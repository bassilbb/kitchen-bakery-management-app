@extends('layouts.app')

@section('title', 'Sell (POS)')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2">
            <form method="GET" action="{{ route('pos.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 flex-1 min-w-40">
                <select name="category_id" onchange="this.form.submit()"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </form>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @forelse ($products as $product)
                    <form method="POST" action="{{ route('pos.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" {{ $product->stock_qty <= 0 ? 'disabled' : '' }}
                            class="w-full text-left bg-white rounded-xl border border-slate-200 p-4 hover:border-amber-400 hover:shadow-sm transition {{ $product->stock_qty <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <div class="flex items-start justify-between">
                                <span class="text-xs uppercase tracking-wide text-slate-400 font-semibold">{{ $product->category?->name ?: 'Uncategorized' }}</span>
                                <span class="text-xs font-semibold {{ $product->stock_qty <= 0 ? 'text-rose-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ $product->stock_qty }} left
                                </span>
                            </div>
                            <p class="mt-1 font-semibold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-1 text-amber-600 font-bold">{{ config('pos.currency') }}{{ number_format($product->price, 2) }}</p>
                        </button>
                    </form>
                @empty
                    <p class="col-span-full text-sm text-slate-500 py-8 text-center">No products found. Add products first.</p>
                @endforelse
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 sticky top-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-slate-900">Current Sale</h2>
                    <form method="POST" action="{{ route('pos.clear') }}">
                        @csrf
                        <button type="submit" class="text-sm text-rose-600 hover:text-rose-500 font-medium">Clear</button>
                    </form>
                </div>

                @php
                    $items = [];
                    $subtotal = 0;
                    foreach ($cart as $id => $qty) {
                        $product = $products->firstWhere('id', $id);
                        if ($product) {
                            $items[] = ['product' => $product, 'qty' => (float)$qty, 'line' => round($product->price * $qty, 2)];
                            $subtotal += $product->price * $qty;
                        }
                    }
                    $subtotal = round($subtotal, 2);
                @endphp

                @if (empty($items))
                    <p class="text-sm text-slate-500 py-8 text-center">Cart is empty.<br>Tap a product to add it.</p>
                @else
                    <div class="space-y-3 mb-4">
                        @foreach ($items as $item)
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $item['product']->name }}</p>
                                    <p class="text-xs text-slate-500">{{ config('pos.currency') }}{{ number_format($item['product']->price, 2) }} each</p>
                                </div>
                                <form method="POST" action="{{ route('pos.update-qty') }}" class="flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                    <input type="number" name="qty" value="{{ $item['qty'] }}" min="0.01" step="0.01"
                                           class="w-16 rounded-lg border border-slate-300 px-2 py-1 text-sm focus:border-amber-500"
                                           onchange="this.form.submit()">
                                </form>
                                <span class="text-sm font-semibold w-20 text-right">{{ config('pos.currency') }}{{ number_format($item['line'], 2) }}</span>
                                <form method="POST" action="{{ route('pos.remove') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                    <button type="submit" class="text-slate-400 hover:text-rose-500 text-sm px-1">&times;</button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-1 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-medium">{{ config('pos.currency') }}{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tax ({{ config('pos.tax_rate', 0) }}%)</span>
                            <span class="font-medium">{{ config('pos.currency') }}{{ number_format($subtotal * config('pos.tax_rate', 0) / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold pt-1">
                            <span>Total</span>
                            <span>{{ config('pos.currency') }}{{ number_format($subtotal * (1 + config('pos.tax_rate', 0) / 100), 2) }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('pos.checkout') }}" class="space-y-3">
                        @csrf
                        <input type="text" name="customer_name" placeholder="Customer name (optional)"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        <input type="number" name="discount" value="0" min="0" step="0.01" placeholder="Discount amount"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        <select name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="online">Online</option>
                        </select>
                        <button type="submit"
                                class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
                            Complete Sale
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
