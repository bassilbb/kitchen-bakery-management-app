@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
            <select name="category_id" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="stock" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">All stock</option>
                <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                <option value="ok" @selected(request('stock') === 'ok')>In stock</option>
            </select>
        </form>
        <a href="{{ route('products.create') }}"
           class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">+ New Product</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3 text-right">Price</th>
                        <th class="px-5 py-3 text-right">Cost</th>
                        <th class="px-5 py-3 text-right">Stock</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('products.show', $product) }}" class="font-medium text-slate-900 hover:text-amber-600">{{ $product->name }}</a>
                                @if ($product->sku)<p class="text-xs text-slate-400">{{ $product->sku }}</p>@endif
                            </td>
                            <td class="px-5 py-3">{{ $product->category?->name ?: '-' }}</td>
                            <td class="px-5 py-3 text-right">{{ config('pos.currency') }}{{ number_format($product->price, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ config('pos.currency') }}{{ number_format($product->cost, 2) }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $product->isLowStock() ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $product->stock_qty }} {{ $product->unit }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $product->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('products.show', $product) }}" class="text-amber-600 hover:text-amber-500 font-medium mr-3">View</a>
                                <a href="{{ route('products.edit', $product) }}" class="text-slate-600 hover:text-slate-900 font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
