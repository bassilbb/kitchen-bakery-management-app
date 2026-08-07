@extends('layouts.app')

@section('title', 'New Product')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('products.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        <option value="">None</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Unit</label>
                    <select name="unit" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        @foreach (['piece', 'loaf', 'box', 'kg', 'bag'] as $unit)
                            <option value="{{ $unit }}" @selected(old('unit', 'piece') === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Selling price</label>
                    <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Low stock threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 0) }}" min="0" step="0.01"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Opening stock</label>
                    <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0" step="0.01"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="3"
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Create Product</button>
                <a href="{{ route('products.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
