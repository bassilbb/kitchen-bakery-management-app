@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-2 mb-6">
        <div>
            <label class="block text-xs font-medium text-slate-500">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                   class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                   class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
        </div>
        <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">Generate</button>
        <a href="{{ route('reports.export-orders', request()->query()) }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Export orders (CSV)</a>
    </form>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Sales</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($salesTotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Orders</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $salesCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Discounts given</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($discountGiven, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Units produced</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($unitsProduced, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Expenses</p>
            <p class="mt-1 text-2xl font-bold text-rose-600">-{{ config('pos.currency') }}{{ number_format($expenseTotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Tax collected</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($taxCollected, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Production cost</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($productionTotal, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Net profit</p>
            <p class="mt-1 text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ config('pos.currency') }}{{ number_format($netProfit, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Daily Sales</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2">Date</th>
                            <th class="py-2 text-right">Orders</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($dailySales as $row)
                            <tr>
                                <td class="py-2">{{ $row['day'] }}</td>
                                <td class="py-2 text-right">{{ $row['orders'] }}</td>
                                <td class="py-2 text-right font-medium">{{ config('pos.currency') }}{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-slate-500">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Top Selling Products</h2>
            <div class="space-y-2">
                @forelse ($topProducts as $index => $top)
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs font-bold">{{ $index + 1 }}</span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-900">{{ $top->product_name }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($top->total_qty, 1) }} sold</p>
                        </div>
                        <span class="text-sm font-semibold">{{ config('pos.currency') }}{{ number_format($top->total_revenue, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No sales in this period.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Production in Period</h2>
            <p class="text-sm text-slate-500 mb-3">Total production cost: <span class="font-semibold text-slate-900">{{ config('pos.currency') }}{{ number_format($productionTotal, 2) }}</span></p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2">Batch</th>
                            <th class="py-2">Product</th>
                            <th class="py-2 text-right">Qty</th>
                            <th class="py-2 text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($productions as $production)
                            <tr>
                                <td class="py-2"><a href="{{ route('productions.show', $production) }}" class="text-amber-600 hover:text-amber-500">{{ $production->production_number }}</a></td>
                                <td class="py-2">{{ $production->product->name }}</td>
                                <td class="py-2 text-right">{{ $production->quantity }}</td>
                                <td class="py-2 text-right font-medium">{{ config('pos.currency') }}{{ number_format($production->total_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-slate-500">No production in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Most Used Ingredients</h2>
            <div class="space-y-2">
                @forelse ($usageByIngredient as $row)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-sm">
                        <span class="font-medium text-slate-900">{{ $row['name'] }}</span>
                        <span class="text-slate-600">{{ number_format($row['quantity'], 2) }} {{ $row['unit'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No production in this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Payments by Method</h2>
            <div class="space-y-2 text-sm">
                @forelse ($paymentBreakdown as $method)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="font-medium text-slate-900">{{ ucfirst($method->payment_method) }} ({{ $method->orders }} orders)</span>
                        <span class="font-semibold">{{ config('pos.currency') }}{{ number_format($method->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-slate-500">No sales in this period.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Expenses by Category</h2>
            <div class="space-y-2 text-sm">
                @forelse ($expenseByCategory as $row)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="font-medium text-slate-900">{{\App\Models\Expense::CATEGORIES[$row->category] ?? ucfirst($row->category) }}</span>
                        <span class="font-semibold text-rose-600">-{{ config('pos.currency') }}{{ number_format($row->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-slate-500">No expenses in this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Inventory Overview</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Ingredient stock value</span>
                    <span class="font-semibold">{{ config('pos.currency') }}{{ number_format($inventoryValue['ingredients'], 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Finished product value</span>
                    <span class="font-semibold">{{ config('pos.currency') }}{{ number_format($inventoryValue['products'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total inventory value</span>
                    <span class="font-bold">{{ config('pos.currency') }}{{ number_format($inventoryValue['ingredients'] + $inventoryValue['products'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Low Stock Items</h2>
            <div class="space-y-2 text-sm">
                @foreach ($lowStockIngredients->take(5) as $ingredient)
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <a href="{{ route('ingredients.show', $ingredient) }}" class="text-slate-700 hover:text-amber-600">{{ $ingredient->name }}</a>
                        <span class="text-rose-600 font-medium">{{ $ingredient->stock_qty }} {{ $ingredient->unit }}</span>
                    </div>
                @endforeach
                @foreach ($lowStockProducts->take(5) as $product)
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <a href="{{ route('products.show', $product) }}" class="text-slate-700 hover:text-amber-600">{{ $product->name }}</a>
                        <span class="text-rose-600 font-medium">{{ $product->stock_qty }} {{ $product->unit }}</span>
                    </div>
                @endforeach
                @if ($lowStockIngredients->isEmpty() && $lowStockProducts->isEmpty())
                    <p class="text-slate-500">All stock levels are healthy.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
