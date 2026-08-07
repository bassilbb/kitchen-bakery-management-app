<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::with('supplier');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('stock')) {
            $ingredients = $query->get()->filter(function ($i) use ($request) {
                return $request->stock === 'low' ? $i->isLowStock() : $i->stock_qty > $i->low_stock_threshold;
            });
        } else {
            $ingredients = $query->orderBy('name')->get();
        }

        return view('ingredients.index', compact('ingredients'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('ingredients.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $initialStock = (float) ($data['stock_qty'] ?? 0);
        unset($data['stock_qty']);

        DB::transaction(function () use ($data, $initialStock) {
            $ingredient = Ingredient::create($data);
            $ingredient->stock_qty = $initialStock;
            $ingredient->save();

            if ($initialStock > 0) {
                IngredientMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'type' => IngredientMovement::TYPE_PURCHASE,
                    'quantity' => $initialStock,
                    'unit_cost' => $data['cost_per_unit'],
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'reference' => 'Initial stock',
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('ingredients.index')->with('success', 'Ingredient created.');
    }

    public function show(Ingredient $ingredient)
    {
        $ingredient->load('supplier', 'movements.user');

        return view('ingredients.show', compact('ingredient'));
    }

    public function edit(Ingredient $ingredient)
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('ingredients.edit', compact('ingredient', 'suppliers'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $this->validated($request);
        unset($data['stock_qty']);

        $ingredient->update($data);

        return redirect()->route('ingredients.show', $ingredient)->with('success', 'Ingredient updated.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('ingredients.index')->with('success', 'Ingredient deleted.');
    }

    public function addPurchase(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($ingredient, $data) {
            $ingredient->stock_qty += $data['quantity'];
            $ingredient->cost_per_unit = $data['unit_cost'];
            $ingredient->save();

            IngredientMovement::create([
                'ingredient_id' => $ingredient->id,
                'type' => IngredientMovement::TYPE_PURCHASE,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'reference' => 'Purchase',
                'note' => $data['note'] ?? null,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Stock added to '.$ingredient->name.'.');
    }

    public function adjustStock(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'adjustment' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (float) $data['adjustment'];

        if ($qty == 0) {
            return back()->with('error', 'Adjustment cannot be zero.');
        }

        if ($ingredient->stock_qty + $qty < 0) {
            return back()->with('error', 'Adjustment would make stock negative.');
        }

        DB::transaction(function () use ($ingredient, $qty, $data) {
            $ingredient->stock_qty += $qty;
            $ingredient->save();

            IngredientMovement::create([
                'ingredient_id' => $ingredient->id,
                'type' => IngredientMovement::TYPE_ADJUSTMENT,
                'quantity' => $qty,
                'reference' => 'Manual',
                'note' => $data['note'] ?? null,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Stock adjusted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'stock_qty' => ['nullable', 'numeric', 'min:0'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ]);
    }
}
