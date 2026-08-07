@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $product->isLowStock() ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                {{ $product->stock_qty }} {{ $product->unit }} in stock
            </span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $product->is_active ? 'Active' : 'Hidden' }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('products.toggle', $product) }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ $product->is_active ? 'Hide from POS' : 'Show in POS' }}
                </button>
            </form>
            <a href="{{ route('products.edit', $product) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Details</h2>
                <dl class="grid grid-cols-2 gap-y-3 text-sm">
                    <div><dt class="text-slate-500">Category</dt><dd class="font-medium">{{ $product->category?->name ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">SKU</dt><dd class="font-medium">{{ $product->sku ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">Selling price</dt><dd class="font-medium">{{ config('pos.currency') }}{{ number_format($product->price, 2) }}</dd></div>
                    <div><dt class="text-slate-500">Estimated cost</dt><dd class="font-medium">{{ config('pos.currency') }}{{ number_format($product->cost, 2) }}</dd></div>
                    <div><dt class="text-slate-500">Profit margin</dt><dd class="font-medium">{{ $product->price > 0 ? round(($product->price - $product->cost) / $product->price * 100, 1).'%' : '-' }}</dd></div>
                </dl>
                @if ($product->description)
                    <p class="mt-3 text-sm text-slate-600">{{ $product->description }}</p>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Recipe (ingredients per {{ $product->unit }})</h2>
                <form method="POST" action="{{ route('products.recipe', $product) }}">
                    @csrf
                    <div id="recipe-rows" class="space-y-2">
                        @forelse ($product->recipeItems as $i => $item)
                            <div class="flex gap-2 items-center recipe-row">
                                <select name="ingredients[{{ $i }}][ingredient_id]" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                                    <option value="">-- select ingredient --</option>
                                    @foreach (\App\Models\Ingredient::orderBy('name')->get() as $ingredient)
                                        <option value="{{ $ingredient->id }}" @selected($item->ingredient_id === $ingredient->id)>{{ $ingredient->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.001" min="0.001" name="ingredients[{{ $i }}][quantity]" value="{{ $item->quantity }}"
                                       placeholder="Qty" class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                                <button type="button" onclick="this.closest('.recipe-row').remove()" class="text-slate-400 hover:text-rose-500 text-lg px-1">&times;</button>
                            </div>
                        @empty
                            <div class="flex gap-2 items-center recipe-row">
                                <select name="ingredients[0][ingredient_id]" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                                    <option value="">-- select ingredient --</option>
                                    @foreach (\App\Models\Ingredient::orderBy('name')->get() as $ingredient)
                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="0.001" min="0.001" name="ingredients[0][quantity]" placeholder="Qty"
                                       class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                                <button type="button" onclick="this.closest('.recipe-row').remove()" class="text-slate-400 hover:text-rose-500 text-lg px-1">&times;</button>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <button type="button" onclick="addRecipeRow()" class="text-sm font-medium text-amber-600 hover:text-amber-500">+ Add ingredient</button>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Save Recipe</button>
                        <span class="text-xs text-slate-400">Saving recalculates the estimated cost automatically.</span>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Stock Movements</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4 text-right">Qty</th>
                                <th class="py-2">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($product->movements as $movement)
                                <tr>
                                    <td class="py-2 pr-4 text-slate-500">{{ $movement->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $movement->type === 'sale' ? 'bg-sky-100 text-sky-700' : ($movement->type === 'production' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ ucfirst($movement->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 text-right font-medium {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td class="py-2 text-slate-500">{{ $movement->reference }} {{ $movement->note }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-slate-500">No movements recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Adjust Stock</h2>
                <form method="POST" action="{{ route('products.adjust-stock', $product) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Change amount (+/-)</label>
                        <input type="number" name="adjustment" step="0.01" required placeholder="e.g. 5 or -2"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Reason</label>
                        <input type="text" name="note" placeholder="e.g. waste, damaged, counted extra"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Apply Adjustment</button>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Recipe Cost Breakdown</h2>
                @forelse ($product->recipeItems as $item)
                    <div class="flex justify-between py-1.5 text-sm border-b border-slate-100 last:border-0">
                        <span>{{ $item->ingredient->name }} <span class="text-slate-400">({{ $item->quantity }} {{ $item->ingredient->unit }})</span></span>
                        <span class="font-medium">{{ config('pos.currency') }}{{ number_format($item->quantity * $item->ingredient->cost_per_unit, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No recipe yet.</p>
                @endforelse
                <div class="flex justify-between pt-2 font-bold text-sm">
                    <span>Total per {{ $product->unit }}</span>
                    <span>{{ config('pos.currency') }}{{ number_format($product->cost, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let recipeIndex = {{ $product->recipeItems->count() }};
        function addRecipeRow() {
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center recipe-row';
            row.innerHTML = `
                <select name="ingredients[${recipeIndex}][ingredient_id]" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    <option value="">-- select ingredient --</option>
                    @foreach (\App\Models\Ingredient::orderBy('name')->get() as $ingredient)
                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.001" min="0.001" name="ingredients[${recipeIndex}][quantity]" placeholder="Qty"
                       class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                <button type="button" onclick="this.closest('.recipe-row').remove()" class="text-slate-400 hover:text-rose-500 text-lg px-1">&times;</button>`;
            document.getElementById('recipe-rows').appendChild(row);
            recipeIndex++;
        }
    </script>
@endsection
