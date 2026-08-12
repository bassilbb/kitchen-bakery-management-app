<?php

namespace App\Http\Controllers;

use App\Models\IngredientMovement;
use App\Models\Product;
use App\Models\Production;
use Illuminate\Http\Request;

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

    public function show(Production $production)
    {
        $production->load('product', 'user', 'request.items.ingredient');

        $usage = IngredientMovement::where('reference', $production->production_number)
            ->where('type', IngredientMovement::TYPE_USAGE)
            ->with('ingredient')
            ->get();

        // Request-driven batches reference the request number for the
        // issued ingredients, not the production number.
        if ($production->request) {
            $usage = $usage->concat(
                $production->request->items->map(fn ($item) => (object) [
                    'ingredient' => $item->ingredient,
                    'quantity' => -($item->issued_qty ?? 0),
                ])
            );
        }

        return view('productions.show', compact('production', 'usage'));
    }

    public function destroy(Production $production)
    {
        return back()->with('error', 'Production batches cannot be deleted. Use stock adjustments to correct errors.');
    }
}
