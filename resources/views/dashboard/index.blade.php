@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .stat-gradient-sales {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
        }

        .stat-gradient-orders {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
        }

        .stat-gradient-week {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .stat-gradient-baked {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        }

        .stat-gradient-expenses {
            background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%);
        }

        .stat-gradient-net {
            background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        }

        .stat-gradient-pending {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        }

        .stat-gradient-lowstock {
            background: linear-gradient(135deg, #be123c 0%, #fb7185 100%);
        }
    </style>
@endpush

@section('content')
    @php
        $user = auth()->user();
        $roleView = $user->isAdmin() ? 'admin' : ($user->isCashier() ? 'cashier' : ($user->isKitchen() ? 'kitchen' : 'bakery'));
    @endphp

    @if ($roleView === 'admin')
        {{-- ============ ADMIN FULL VIEW ============ --}}

        {{-- 6 stat widgets in 3 segments --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-sales card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-300">Today's Sales</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ config('pos.currency') }}{{ number_format($todaySales, 2) }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/10 text-amber-400"><x-svg-icon icon="pos" /></span>
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-orders card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-amber-100/90">Today's Orders</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $todayOrders }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="orders" /></span>
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-week card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-emerald-100/90">This Week's Sales</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ config('pos.currency') }}{{ number_format($weekSales, 2) }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="reports" /></span>
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-baked card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-purple-100/90">Today's Batches Baked</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $todayProduction }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="production" /></span>
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-expenses card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-rose-100/90">Today's Expenses</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">-{{ config('pos.currency') }}{{ number_format($todayExpenses, 2) }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="expenses" /></span>
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-net card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-sky-100/90">Today's Net</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ config('pos.currency') }}{{ number_format($todaySales - $todayExpenses, 2) }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="dashboard" /></span>
                </div>
            </div>
        </div>

        {{-- Payments + charts --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
            <div class="card-3d glow-amber rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-5">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><x-svg-icon icon="pos" /></span>
                    Today's Payments
                </h2>
                @php
                    $paymentColors = ['cash' => 'bg-emerald-500', 'card' => 'bg-sky-500', 'online' => 'bg-violet-500'];
                    $payMax = max(1, $paymentBreakdown->max('total'));
                @endphp
                <div class="space-y-3">
                    @forelse ($paymentBreakdown as $method)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-16 font-medium text-slate-700">{{ ucfirst($method->payment_method) }}</span>
                            <div class="flex-1 h-3.5 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                <div class="h-full rounded-full {{ $paymentColors[$method->payment_method] ?? 'bg-slate-400' }} transition-all duration-500"
                                     style="width: {{ round($method->total / $payMax * 100) }}%"></div>
                            </div>
                            <span class="w-24 text-right font-semibold text-slate-700">{{ config('pos.currency') }}{{ number_format($method->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No sales today yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6 flex flex-col">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><x-svg-icon icon="reports" /></span>
                    Sales - Last 7 Days
                </h2>
                @php
                    $salesConfig = [
                        'type' => 'line',
                        'data' => [
                            'labels' => $salesChart->pluck('label'),
                            'datasets' => [[
                                'label' => 'Sales',
                                'data' => $salesChart->pluck('total'),
                                'borderColor' => '#f59e0b',
                                'backgroundColor' => 'rgba(245, 158, 11, 0.18)',
                                'fill' => true,
                                'tension' => 0.4,
                                'pointRadius' => 4,
                                'pointBackgroundColor' => '#f59e0b',
                                'borderWidth' => 3,
                            ]],
                        ],
                        'options' => [
                            'responsive' => true,
                            'maintainAspectRatio' => false,
                            'plugins' => ['legend' => ['display' => false]],
                            'scales' => [
                                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(226, 232, 240, 0.6)'], 'ticks' => ['callback' => 'formatCurrency']],
                                'x' => ['grid' => ['display' => false]],
                            ],
                        ],
                    ];
                @endphp
                <div class="relative flex-1 min-h-56">
                    <canvas data-chart="{{ json_encode($salesConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            <div class="card-3d glow-amber rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6 flex flex-col">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-violet-700"><x-svg-icon icon="pos" /></span>
                    Payment Methods - Last 7 Days
                </h2>
                @php
                    $paymentConfig = [
                        'type' => 'doughnut',
                        'data' => [
                            'labels' => $paymentChart->pluck('method')->map(fn ($m) => ucfirst($m)),
                            'datasets' => [[
                                'data' => $paymentChart->pluck('total'),
                                'backgroundColor' => ['#10b981', '#0ea5e9', '#8b5cf6'],
                                'borderColor' => '#ffffff',
                                'borderWidth' => 3,
                                'hoverOffset' => 10,
                            ]],
                        ],
                        'options' => [
                            'responsive' => true,
                            'maintainAspectRatio' => false,
                            'cutout' => '62%',
                            'plugins' => [
                                'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'padding' => 16]],
                                'tooltip' => ['callbacks' => ['label' => 'formatCurrencyTooltip']],
                            ],
                        ],
                    ];
                @endphp
                <div class="relative flex-1 min-h-56">
                    <canvas data-chart="{{ json_encode($paymentConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mt-5">
            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6 flex flex-col">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700"><x-svg-icon icon="reports" /></span>
                    Sales vs Expenses - Last 7 Days
                </h2>
                @php
                    $netConfig = [
                        'type' => 'bar',
                        'data' => [
                            'labels' => $netChart->pluck('label'),
                            'datasets' => [
                                ['label' => 'Sales', 'data' => $netChart->pluck('sales'), 'backgroundColor' => 'rgba(16, 185, 129, 0.85)', 'borderRadius' => 6, 'borderSkipped' => false],
                                ['label' => 'Expenses', 'data' => $netChart->pluck('expenses'), 'backgroundColor' => 'rgba(244, 63, 94, 0.85)', 'borderRadius' => 6, 'borderSkipped' => false],
                            ],
                        ],
                        'options' => [
                            'responsive' => true,
                            'maintainAspectRatio' => false,
                            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8]]],
                            'scales' => [
                                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(226, 232, 240, 0.6)'], 'ticks' => ['callback' => 'formatCurrency']],
                                'x' => ['grid' => ['display' => false]],
                            ],
                        ],
                    ];
                @endphp
                <div class="relative flex-1 min-h-56">
                    <canvas data-chart="{{ json_encode($netConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><x-svg-icon icon="products" /></span>
                    Top Products
                </h2>
                @forelse ($topProducts as $index => $top)
                    <div class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ ['from-amber-400 to-orange-500', 'from-slate-400 to-slate-500', 'from-orange-300 to-amber-500'][$index] ?? 'from-amber-400 to-orange-500' }} text-white text-sm font-bold shadow-md">{{ $index + 1 }}</span>
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

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
            <div class="xl:col-span-2 card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-700"><x-svg-icon icon="orders" /></span>
                        Recent Orders
                    </h2>
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
                                <tr class="hover:bg-slate-50 transition-colors">
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

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700"><x-svg-icon icon="ingredients" /></span>
                    Low Stock Alerts
                </h2>
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold mb-2">Ingredients</p>
                @forelse ($lowStockIngredients as $ingredient)
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <a href="{{ route('ingredients.show', $ingredient) }}" class="text-slate-700 hover:text-amber-600">{{ $ingredient->name }}</a>
                        <span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs font-semibold">{{ $ingredient->stock_qty }} {{ $ingredient->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-1.5">All ingredient levels OK.</p>
                @endforelse
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold mb-2 mt-4">Finished Products</p>
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <a href="{{ route('products.show', $product) }}" class="text-slate-700 hover:text-amber-600">{{ $product->name }}</a>
                        <span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs font-semibold">{{ $product->stock_qty }} {{ $product->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-1.5">All product levels OK.</p>
                @endforelse
            </div>
        </div>

    @elseif ($roleView === 'cashier')
        {{-- ============ CASHIER VIEW - sales only ============ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-sales card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-300">Today's Sales</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ config('pos.currency') }}{{ number_format($todaySales, 2) }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/10 text-amber-400"><x-svg-icon icon="pos" /></span>
                </div>
            </div>
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-orders card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-amber-100/90">Today's Orders</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $todayOrders }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="orders" /></span>
                </div>
            </div>
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-week card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-emerald-100/90">This Week's Sales</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ config('pos.currency') }}{{ number_format($weekSales, 2) }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="reports" /></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mt-5">
            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6 flex flex-col">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><x-svg-icon icon="reports" /></span>
                    Sales - Last 7 Days
                </h2>
                @php
                    $salesConfig = [
                        'type' => 'line',
                        'data' => [
                            'labels' => $salesChart->pluck('label'),
                            'datasets' => [[
                                'label' => 'Sales',
                                'data' => $salesChart->pluck('total'),
                                'borderColor' => '#f59e0b',
                                'backgroundColor' => 'rgba(245, 158, 11, 0.18)',
                                'fill' => true,
                                'tension' => 0.4,
                                'pointRadius' => 4,
                                'pointBackgroundColor' => '#f59e0b',
                                'borderWidth' => 3,
                            ]],
                        ],
                        'options' => [
                            'responsive' => true,
                            'maintainAspectRatio' => false,
                            'plugins' => ['legend' => ['display' => false]],
                            'scales' => [
                                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(226, 232, 240, 0.6)'], 'ticks' => ['callback' => 'formatCurrency']],
                                'x' => ['grid' => ['display' => false]],
                            ],
                        ],
                    ];
                @endphp
                <div class="relative flex-1 min-h-56">
                    <canvas data-chart="{{ json_encode($salesConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6 flex flex-col">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-violet-700"><x-svg-icon icon="pos" /></span>
                    Payment Methods - Last 7 Days
                </h2>
                @php
                    $paymentConfig = [
                        'type' => 'doughnut',
                        'data' => [
                            'labels' => $paymentChart->pluck('method')->map(fn ($m) => ucfirst($m)),
                            'datasets' => [[
                                'data' => $paymentChart->pluck('total'),
                                'backgroundColor' => ['#10b981', '#0ea5e9', '#8b5cf6'],
                                'borderColor' => '#ffffff',
                                'borderWidth' => 3,
                                'hoverOffset' => 10,
                            ]],
                        ],
                        'options' => [
                            'responsive' => true,
                            'maintainAspectRatio' => false,
                            'cutout' => '62%',
                            'plugins' => [
                                'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'padding' => 16]],
                                'tooltip' => ['callbacks' => ['label' => 'formatCurrencyTooltip']],
                            ],
                        ],
                    ];
                @endphp
                <div class="relative flex-1 min-h-56">
                    <canvas data-chart="{{ json_encode($paymentConfig) }}" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
            <div class="xl:col-span-2 card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-700"><x-svg-icon icon="orders" /></span>
                        Recent Orders
                    </h2>
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
                                <tr class="hover:bg-slate-50 transition-colors">
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

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><x-svg-icon icon="products" /></span>
                    Top Products
                </h2>
                @forelse ($topProducts as $index => $top)
                    <div class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ ['from-amber-400 to-orange-500', 'from-slate-400 to-slate-500', 'from-orange-300 to-amber-500'][$index] ?? 'from-amber-400 to-orange-500' }} text-white text-sm font-bold shadow-md">{{ $index + 1 }}</span>
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

    @elseif ($roleView === 'kitchen')
        {{-- ============ KITCHEN VIEW - ingredients & requests ============ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-lowstock card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-rose-100/90">Low Stock Ingredients</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $lowStockIngredients->count() }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="ingredients" /></span>
                </div>
            </div>
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-pending card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-teal-100/90">Awaiting Review</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $pendingReviews->count() }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="requests" /></span>
                </div>
            </div>
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-orders card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-amber-100/90">Ready to Issue</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $readyToIssue->count() }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="pos" /></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700"><x-svg-icon icon="ingredients" /></span>
                    Low Stock Ingredients
                </h2>
                @forelse ($lowStockIngredients as $ingredient)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <a href="{{ route('ingredients.show', $ingredient) }}" class="text-sm text-slate-700 hover:text-amber-600">{{ $ingredient->name }}</a>
                        <span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs font-semibold">{{ $ingredient->stock_qty }} {{ $ingredient->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">All ingredient levels OK.</p>
                @endforelse
                <a href="{{ route('ingredients.index') }}" class="inline-block mt-4 text-sm font-medium text-amber-600 hover:text-amber-500">Manage ingredients &rarr;</a>
            </div>

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-700"><x-svg-icon icon="requests" /></span>
                    Awaiting Your Review
                </h2>
                @forelse ($pendingReviews as $request)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <div>
                            <a href="{{ route('production-requests.show', $request) }}" class="text-sm font-medium text-slate-800 hover:text-amber-600">{{ $request->request_number }}</a>
                            <p class="text-xs text-slate-500">{{ $request->product->name }} &middot; {{ $request->quantity }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-sky-100 text-sky-700 px-2 py-0.5 text-xs font-semibold">Approve</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No requests waiting for review.</p>
                @endforelse
                <a href="{{ route('production-requests.index') }}" class="inline-block mt-4 text-sm font-medium text-amber-600 hover:text-amber-500">All requests &rarr;</a>
            </div>

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><x-svg-icon icon="pos" /></span>
                    Ready to Issue
                </h2>
                @forelse ($readyToIssue as $request)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <div>
                            <a href="{{ route('production-requests.show', $request) }}" class="text-sm font-medium text-slate-800 hover:text-amber-600">{{ $request->request_number }}</a>
                            <p class="text-xs text-slate-500">{{ $request->product->name }} &middot; {{ $request->quantity }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 text-xs font-semibold">Issue</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nothing ready to issue right now.</p>
                @endforelse
                <a href="{{ route('production-requests.index') }}" class="inline-block mt-4 text-sm font-medium text-amber-600 hover:text-amber-500">All requests &rarr;</a>
            </div>
        </div>

    @else
        {{-- ============ BAKERY VIEW - products & requests ============ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-baked card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-purple-100/90">Batches Baked Today</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $todayProduction }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="production" /></span>
                </div>
            </div>
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-pending card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-teal-100/90">Draft Requests</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $draftRequests->count() }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="requests" /></span>
                </div>
            </div>
            <div class="card-3d glow-amber rounded-2xl shadow-lg shadow-slate-200/60">
                <div class="stat-gradient-orders card-inner rounded-2xl p-5 text-white flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-amber-100/90">Ready to Produce</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-white">{{ $readyToProduce->count() }}</p>
                    </div>
                    <span class="stat-icon h-12 w-12 bg-white/15 text-white"><x-svg-icon icon="pos" /></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700"><x-svg-icon icon="products" /></span>
                    Low Stock Products
                </h2>
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <a href="{{ route('products.show', $product) }}" class="text-sm text-slate-700 hover:text-amber-600">{{ $product->name }}</a>
                        <span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs font-semibold">{{ $product->stock_qty }} {{ $product->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">All product levels OK.</p>
                @endforelse
                <a href="{{ route('products.index') }}" class="inline-block mt-4 text-sm font-medium text-amber-600 hover:text-amber-500">Manage products &rarr;</a>
            </div>

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-700"><x-svg-icon icon="requests" /></span>
                    Draft Requests
                </h2>
                @forelse ($draftRequests as $request)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <div>
                            <a href="{{ route('production-requests.show', $request) }}" class="text-sm font-medium text-slate-800 hover:text-amber-600">{{ $request->request_number }}</a>
                            <p class="text-xs text-slate-500">{{ $request->product->name }} &middot; {{ $request->quantity }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-xs font-semibold">Draft</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No draft requests.</p>
                @endforelse
                <a href="{{ route('production-requests.create') }}" class="inline-block mt-4 text-sm font-medium text-amber-600 hover:text-amber-500">New request &rarr;</a>
            </div>

            <div class="card-3d rounded-2xl bg-white border border-slate-200 shadow-lg shadow-slate-200/60 p-6">
                <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><x-svg-icon icon="production" /></span>
                    Ready to Produce
                </h2>
                @forelse ($readyToProduce as $request)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <div>
                            <a href="{{ route('production-requests.show', $request) }}" class="text-sm font-medium text-slate-800 hover:text-amber-600">{{ $request->request_number }}</a>
                            <p class="text-xs text-slate-500">{{ $request->product->name }} &middot; {{ $request->quantity }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs font-semibold">Produce</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nothing issued and waiting to be produced.</p>
                @endforelse
                <a href="{{ route('production-requests.index') }}" class="inline-block mt-4 text-sm font-medium text-amber-600 hover:text-amber-500">All requests &rarr;</a>
            </div>
        </div>
    @endif
@endsection
