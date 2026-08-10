<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Production;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $query = Production::with('product', 'user');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('produced_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('produced_at', '<=', $request->to);
        }

        $productions = $query->latest('produced_at')->paginate(20);
        $products = Product::orderBy('name')->get();

        return view('productions.index', compact('productions', 'products'));
    }

    public function create(Request $request)
    {
        $products = Product::with('recipeItems.ingredient')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selected = null;
        $requirements = [];
        $missing = [];

        if ($request->filled('product_id')) {
            $selected = Product::with('recipeItems.ingredient')->find($request->product_id);
            $qty = (float) $request->quantity ?: 1;

            if ($selected && $selected->recipeItems->isNotEmpty()) {
                foreach ($selected->recipeItems as $item) {
                    $needed = $item->quantity * $qty;
                    $available = $item->ingredient->stock_qty;
                    $requirements[] = [
                        'ingredient' => $item->ingredient,
                        'per_unit' => $item->quantity,
                        'needed' => $needed,
                        'available' => $available,
                        'ok' => $available >= $needed,
                    ];
                    if ($available < $needed) {
                        $missing[] = $item->ingredient->name;
                    }
                }
            }
        }

        return view('productions.create', compact('products', 'selected', 'requirements', 'missing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::with('recipeItems.ingredient')->findOrFail($data['product_id']);
        $qty = (float) $data['quantity'];

        if ($product->recipeItems->isEmpty()) {
            return back()->with('error', $product->name.' has no recipe yet. Add ingredients to its recipe first.');
        }

        $requirements = [];
        foreach ($product->recipeItems as $item) {
            $needed = $item->quantity * $qty;
            if ($item->ingredient->stock_qty < $needed) {
                return back()->with('error', 'Not enough '.$item->ingredient->name.' for '.$qty.' '.$product->name.'(s).');
            }
            $requirements[] = [
                'ingredient' => $item->ingredient,
                'needed' => $needed,
            ];
        }

        $unitCost = $product->cost;
        $totalCost = round($unitCost * $qty, 2);

        $production = DB::transaction(function () use ($product, $qty, $requirements, $totalCost, $data) {
            $production = Production::create([
                'production_number' => $this->nextProductionNumber(),
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_cost' => $product->cost,
                'total_cost' => $totalCost,
                'note' => $data['note'] ?? null,
                'user_id' => auth()->id(),
                'produced_at' => now(),
            ]);

            foreach ($requirements as $req) {
                $ingredient = $req['ingredient'];
                $ingredient->stock_qty -= $req['needed'];
                $ingredient->save();

                IngredientMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'type' => IngredientMovement::TYPE_USAGE,
                    'quantity' => -$req['needed'],
                    'reference' => $production->production_number,
                    'note' => 'Used for '.$qty.'x '.$product->name,
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            $product->stock_qty += $qty;
            $product->save();

            ProductMovement::create([
                'product_id' => $product->id,
                'type' => ProductMovement::TYPE_PRODUCTION,
                'quantity' => $qty,
                'reference' => $production->production_number,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);

            return $production;
        });

        return redirect()->route('productions.show', $production)
            ->with('success', 'Production batch completed.');
    }

    public function show(Production $production)
    {
        $production->load('product', 'user');

        $usage = IngredientMovement::where('reference', $production->production_number)
            ->where('type', IngredientMovement::TYPE_USAGE)
            ->with('ingredient')
            ->get();

        return view('productions.show', compact('production', 'usage'));
    }

    public function destroy(Production $production)
    {
        return back()->with('error', 'Production batches cannot be deleted. Use stock adjustments to correct errors.');
    }

    protected function nextProductionNumber(): string
    {
        return 'PRD-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5));
    }
}
