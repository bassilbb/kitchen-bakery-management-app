<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('sku', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock')) {
            $products = $query->get()->filter(function ($p) use ($request) {
                return $request->stock === 'low' ? $p->isLowStock() : $p->stock_qty > $p->low_stock_threshold;
            });
        } else {
            $products = $query->orderBy('name')->get();
        }

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'recipeItems.ingredient', 'movements.user');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load('recipeItems.ingredient');

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', $product->is_active ? 'Product activated.' : 'Product deactivated.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $data = $request->validate([
            'adjustment' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (float) $data['adjustment'];

        if ($qty == 0) {
            return back()->with('error', 'Adjustment cannot be zero.');
        }

        if ($product->stock_qty + $qty < 0) {
            return back()->with('error', 'Adjustment would make stock negative.');
        }

        DB::transaction(function () use ($product, $qty, $data) {
            $product->stock_qty += $qty;
            $product->save();

            ProductMovement::create([
                'product_id' => $product->id,
                'type' => ProductMovement::TYPE_ADJUSTMENT,
                'quantity' => $qty,
                'reference' => 'Manual',
                'note' => $data['note'] ?? null,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Stock adjusted.');
    }

    public function saveRecipe(Request $request, Product $product)
    {
        $request->validate([
            'ingredients' => ['array'],
            'ingredients.*.ingredient_id' => ['nullable', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($request, $product) {
            $product->recipeItems()->delete();

            foreach ($request->ingredients as $row) {
                if (! empty($row['ingredient_id']) && ! empty($row['quantity'])) {
                    $product->recipeItems()->create([
                        'ingredient_id' => $row['ingredient_id'],
                        'quantity' => $row['quantity'],
                    ]);
                }
            }

            $product->cost = $this->calculateRecipeCost($product);
            $product->save();
        });

        return back()->with('success', 'Recipe saved.');
    }

    protected function calculateRecipeCost(Product $product): float
    {
        $cost = 0;
        foreach ($product->recipeItems as $item) {
            $cost += $item->quantity * $item->ingredient->cost_per_unit;
        }

        return round($cost, 2);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'stock_qty' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
