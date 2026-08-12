<?php

namespace App\Http\Controllers;

use App\Models\IngredientMovement;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionRequest;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionRequest::with('product', 'requester', 'items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $requests = $query->latest()->paginate(20);
        $products = Product::orderBy('name')->get();

        return view('production-requests.index', compact('requests', 'products'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isBakery(), 403);

        $products = Product::with('recipeItems.ingredient')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selected = null;
        $requirements = [];

        if ($request->filled('product_id')) {
            $selected = Product::with('recipeItems.ingredient')->find($request->product_id);
            $qty = (float) $request->quantity ?: 1;

            if ($selected && $selected->recipeItems->isNotEmpty()) {
                $requirements = $this->buildRequirements($selected, $qty);
            }
        }

        return view('production-requests.create', compact('products', 'selected', 'requirements'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isBakery(), 403);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'note' => ['nullable', 'string', 'max:255'],
            'action' => ['required', 'in:save,submit'],
            'force' => ['sometimes', 'boolean'],
        ]);

        $product = Product::with('recipeItems.ingredient')->findOrFail($data['product_id']);
        $qty = (float) $data['quantity'];

        if ($product->recipeItems->isEmpty()) {
            return back()->with('error', $product->name.' has no recipe yet. Add ingredients to its recipe first.');
        }

        $requirements = $this->buildRequirements($product, $qty);

        // Unless an admin overrides, a request cannot exceed available stock.
        $missing = collect($requirements)->filter(fn ($r) => ! $r['ok'])->pluck('ingredient.name');
        if ($missing->isNotEmpty() && ! $request->boolean('force')) {
            return back()->with(
                'error',
                'Not enough stock for: '.$missing->implode(', ').'. Receive stock first, or ask an admin to approve an exception.'
            );
        }

        $status = $data['action'] === 'submit'
            ? ProductionRequest::STATUS_SUBMITTED
            : ProductionRequest::STATUS_DRAFT;

        $productionRequest = DB::transaction(function () use ($product, $qty, $requirements, $data, $status) {
            $requestRecord = ProductionRequest::create([
                'request_number' => $this->nextRequestNumber(),
                'product_id' => $product->id,
                'quantity' => $qty,
                'status' => $status,
                'note' => $data['note'] ?? null,
                'requested_by' => auth()->id(),
            ]);

            foreach ($requirements as $req) {
                $requestRecord->items()->create([
                    'ingredient_id' => $req['ingredient']->id,
                    'required_qty' => $req['needed'],
                ]);
            }

            return $requestRecord;
        });

        $message = $status === ProductionRequest::STATUS_SUBMITTED
            ? 'Production request submitted for review.'
            : 'Production request saved as a draft.';

        return redirect()->route('production-requests.show', $productionRequest)->with('success', $message);
    }

    public function show(ProductionRequest $productionRequest)
    {
        $productionRequest->load('product', 'requester', 'approver', 'issuer', 'items.ingredient', 'production');

        return view('production-requests.show', compact('productionRequest'));
    }

    public function submit(ProductionRequest $productionRequest)
    {
        abort_unless($productionRequest->canBeSubmittedBy(auth()->user()), 403);

        $productionRequest->update(['status' => ProductionRequest::STATUS_SUBMITTED]);

        return back()->with('success', 'Production request submitted for review.');
    }

    public function cancel(ProductionRequest $productionRequest)
    {
        abort_unless($productionRequest->canBeCancelledBy(auth()->user()), 403);

        $productionRequest->update(['status' => ProductionRequest::STATUS_CANCELLED]);

        return back()->with('success', 'Production request cancelled.');
    }

    public function approve(ProductionRequest $productionRequest)
    {
        abort_unless($productionRequest->canBeApprovedBy(auth()->user()), 403);

        $productionRequest->update([
            'status' => ProductionRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Production request approved. Ready to issue ingredients.');
    }

    public function reject(ProductionRequest $productionRequest)
    {
        abort_unless($productionRequest->canBeRejectedBy(auth()->user()), 403);

        $data = request()->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $productionRequest->update([
            'status' => ProductionRequest::STATUS_REJECTED,
            'note' => trim(($productionRequest->note ? $productionRequest->note."\n" : '').'Rejected: '.($data['rejection_reason'] ?? 'No reason given')),
        ]);

        return back()->with('success', 'Production request rejected.');
    }

    public function issue(Request $request, ProductionRequest $productionRequest)
    {
        abort_unless($productionRequest->canBeIssuedBy(auth()->user()), 403);

        $data = $request->validate([
            'issued' => ['required', 'array'],
            'issued.*' => ['required', 'numeric', 'min:0'],
        ]);

        $items = $productionRequest->items()->with('ingredient')->get();

        // Validate each issued quantity: not above what was requested, and
        // never more than is physically available. Partially issued items
        // can be topped up, so check against the remaining amount.
        foreach ($items as $item) {
            $remaining = $item->required_qty - (float) $item->issued_qty;
            $requested = (float) ($data['issued'][$item->id] ?? 0);
            if ($requested > $remaining + 1e-9) {
                return back()->withErrors([
                    'issued.'.$item->id => 'Cannot issue more than the remaining '.$remaining.' '.$item->ingredient->unit.' of '.$item->ingredient->name.'.',
                ]);
            }
            if ($requested > $item->ingredient->stock_qty + 1e-9) {
                return back()->withErrors([
                    'issued.'.$item->id => 'Only '.$item->ingredient->stock_qty.' '.$item->ingredient->unit.' of '.$item->ingredient->name.' is available.',
                ]);
            }
        }

        $issuedAny = false;

        DB::transaction(function () use ($items, $data, $productionRequest, &$issuedAny) {
            foreach ($items as $item) {
                $issuedQty = (float) ($data['issued'][$item->id] ?? 0);

                if ($issuedQty <= 0) {
                    continue;
                }

                $issuedAny = true;

                $item->ingredient->stock_qty -= $issuedQty;
                $item->ingredient->save();

                $item->update(['issued_qty' => (float) $item->issued_qty + $issuedQty]);

                IngredientMovement::create([
                    'ingredient_id' => $item->ingredient_id,
                    'type' => IngredientMovement::TYPE_ISSUE,
                    'quantity' => -$issuedQty,
                    'reference' => $productionRequest->request_number,
                    'note' => 'Issued for '.$productionRequest->quantity.'x '.$productionRequest->product->name,
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            $productionRequest->refresh();
            $productionRequest->load('items');

            $productionRequest->update([
                'status' => $productionRequest->isPartiallyIssued()
                    ? ProductionRequest::STATUS_PARTIALLY_ISSUED
                    : ProductionRequest::STATUS_ISSUED,
                'issued_by' => auth()->id(),
                'issued_at' => now(),
            ]);
        });

        if (! $issuedAny) {
            return back()->with('error', 'Enter at least one issued quantity.');
        }

        return back()->with('success', 'Ingredients issued. Stock has been deducted.');
    }

    public function produce(Request $request, ProductionRequest $productionRequest)
    {
        abort_unless($productionRequest->canBeProducedBy(auth()->user()), 403);

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'wastage' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $product = $productionRequest->product;
        $quantity = (float) $data['quantity'];
        $wastage = (float) ($data['wastage'] ?? 0);
        $unitCost = $product->cost;

        $production = DB::transaction(function () use ($productionRequest, $product, $quantity, $wastage, $unitCost, $data) {
            $production = Production::create([
                'production_number' => $this->nextProductionNumber(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'wastage' => $wastage,
                'unit_cost' => $unitCost,
                'total_cost' => round($unitCost * $quantity, 2),
                'note' => $data['note'] ?? null,
                'production_request_id' => $productionRequest->id,
                'user_id' => auth()->id(),
                'produced_at' => now(),
            ]);

            $product->stock_qty += $quantity;
            $product->save();

            ProductMovement::create([
                'product_id' => $product->id,
                'type' => ProductMovement::TYPE_PRODUCTION,
                'quantity' => $quantity,
                'reference' => $production->production_number,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);

            $productionRequest->update(['status' => ProductionRequest::STATUS_COMPLETED]);

            return $production;
        });

        return redirect()->route('productions.show', $production)->with('success', 'Production batch completed.');
    }

    protected function buildRequirements(Product $product, float $qty): array
    {
        $requirements = [];

        foreach ($product->recipeItems as $item) {
            $needed = $item->quantity * $qty;
            $available = $item->ingredient->stock_qty;

            $requirements[] = [
                'ingredient' => $item->ingredient,
                'per_unit' => $item->quantity,
                'needed' => $needed,
                'available' => $available,
                'ok' => $available >= $needed,
            ];
        }

        return $requirements;
    }

    protected function nextRequestNumber(): string
    {
        return 'PR-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5));
    }

    protected function nextProductionNumber(): string
    {
        return 'PRD-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5));
    }
}
