@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Today's Sales</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($todaySales, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Today's Orders</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $todayOrders }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">This Week's Sales</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($weekSales, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Today's Batches Baked</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $todayProduction }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Today's Expenses</p>
            <p class="mt-1 text-2xl font-bold text-rose-600">-{{ config('pos.currency') }}{{ number_format($todayExpenses, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Today's Net</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ config('pos.currency') }}{{ number_format($todaySales - $todayExpenses, 2) }}</p>
        </div>
        <div class="sm:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Today's Payments</h2>
            @php
                $paymentColors = ['cash' => 'bg-emerald-500', 'card' => 'bg-sky-500', 'online' => 'bg-violet-500'];
                $payMax = max(1, $paymentBreakdown->max('total'));
            @endphp
            <div class="space-y-2">
                @forelse ($paymentBreakdown as $method)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-16 font-medium text-slate-700">{{ ucfirst($method->payment_method) }}</span>
                        <div class="flex-1 h-3 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $paymentColors[$method->payment_method] ?? 'bg-slate-400' }}"
                                 style="width: {{ round($method->total / $payMax * 100) }}%"></div>
                        </div>
                        <span class="w-24 text-right font-semibold text-slate-700">{{ config('pos.currency') }}{{ number_format($method->total, 2) }}</span>
                        <span class="w-10 text-right text-xs text-slate-400">{{ $method->orders }} ord</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No sales today yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
            <h2 class="font-semibold text-slate-900 mb-4">Sales - Last 7 Days</h2>
            @php
                $salesConfig = [
                    'type' => 'line',
                    'data' => [
                        'labels' => $salesChart->pluck('label'),
                        'datasets' => [[
                            'label' => 'Sales',
                            'data' => $salesChart->pluck('total'),
                            'borderColor' => '#f59e0b',
                            'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                            'fill' => true,
                            'tension' => 0.35,
                        ]],
                    ],
                    'options' => [
                        'responsive' => true,
                        'maintainAspectRatio' => false,
                        'plugins' => ['legend' => ['display' => false]],
                        'scales' => [
                            'y' => ['beginAtZero' => true, 'ticks' => ['callback' => 'formatCurrency']],
                        ],
                    ],
                ];
            @endphp
            <div class="relative flex-1 min-h-64">
                <canvas data-chart="{{ json_encode($salesConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
            <h2 class="font-semibold text-slate-900 mb-4">Sales vs Expenses - Last 7 Days</h2>
            @php
                $netConfig = [
                    'type' => 'bar',
                    'data' => [
                        'labels' => $netChart->pluck('label'),
                        'datasets' => [
                            ['label' => 'Sales', 'data' => $netChart->pluck('sales'), 'backgroundColor' => '#10b981'],
                            ['label' => 'Expenses', 'data' => $netChart->pluck('expenses'), 'backgroundColor' => '#f43f5e'],
                        ],
                    ],
                    'options' => [
                        'responsive' => true,
                        'maintainAspectRatio' => false,
                        'scales' => [
                            'y' => ['beginAtZero' => true, 'ticks' => ['callback' => 'formatCurrency']],
                        ],
                    ],
                ];
            @endphp
            <div class="relative flex-1 min-h-64">
                <canvas data-chart="{{ json_encode($netConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
            <h2 class="font-semibold text-slate-900 mb-4">Payment Methods - Last 7 Days</h2>
            @php
                $paymentConfig = [
                    'type' => 'doughnut',
                    'data' => [
                        'labels' => $paymentChart->pluck('method')->map(fn ($m) => ucfirst($m)),
                        'datasets' => [[
                            'data' => $paymentChart->pluck('total'),
                            'backgroundColor' => ['#10b981', '#0ea5e9', '#8b5cf6'],
                        ]],
                    ],
                    'options' => [
                        'responsive' => true,
                        'maintainAspectRatio' => false,
                        'plugins' => [
                            'legend' => ['position' => 'bottom'],
                            'tooltip' => ['callbacks' => ['label' => 'formatCurrencyTooltip']],
                        ],
                    ],
                ];
            @endphp
            <div class="relative flex-1 min-h-64">
                <canvas data-chart="{{ json_encode($paymentConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Top Products (by quantity sold)</h2>
            @forelse ($topProducts as $index => $top)
                <div class="flex items-center gap-3 py-2 border-b border-slate-100 last:border-0">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-sm font-bold">{{ $index + 1 }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-900">{{ $top->product_name }}</p>
                        <p class="text-xs text-slate-500">{{ number_format($top->total_qty, 1) }} sold</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ config('pos.currency') }}{{ number_format($top->total_revenue, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No sales yet.</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mt-4">
        <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-900">Recent Orders</h2>
                <a href="{{ route('orders.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-500">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="py-2 pr-4">Order</th>
                            <th class="py-2 pr-4">Customer</th>
                            <th class="py-2 pr-4">Items</th>
                            <th class="py-2 pr-4">Total</th>
                            <th class="py-2">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="py-2.5 pr-4">
                                    <a href="{{ route('orders.show', $order) }}" class="font-medium text-amber-600 hover:text-amber-500">{{ $order->order_number }}</a>
                                </td>
                                <td class="py-2.5 pr-4">{{ $order->customer_name ?: 'Walk-in' }}</td>
                                <td class="py-2.5 pr-4">{{ $order->items->sum('quantity') }}</td>
                                <td class="py-2.5 pr-4 font-medium">{{ config('pos.currency') }}{{ number_format($order->total, 2) }}</td>
                                <td class="py-2.5 text-slate-500">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-slate-500">No orders yet. Make your first sale from the POS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Low Stock Alerts</h2>

            @if (auth()->user()->canAccessKitchen())
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold mb-2">Ingredients</p>
                @forelse ($lowStockIngredients as $ingredient)
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <a href="{{ route('ingredients.show', $ingredient) }}" class="text-slate-700 hover:text-amber-600">{{ $ingredient->name }}</a>
                        <span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs font-semibold">{{ $ingredient->stock_qty }} {{ $ingredient->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-1.5">All ingredient levels OK.</p>
                @endforelse
            @endif

            @if (auth()->user()->canAccessBakery())
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold mb-2 mt-4">Finished Products</p>
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <a href="{{ route('products.show', $product) }}" class="text-slate-700 hover:text-amber-600">{{ $product->name }}</a>
                        <span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs font-semibold">{{ $product->stock_qty }} {{ $product->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-1.5">All product levels OK.</p>
                @endforelse
            @endif

            @if (! auth()->user()->canAccessKitchen() && ! auth()->user()->canAccessBakery())
                <p class="text-sm text-slate-500 py-1.5">Ask an admin to assign you a department.</p>
            @endif
        </div>
    </div>
@endsection
