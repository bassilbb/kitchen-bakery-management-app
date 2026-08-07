@extends('layouts.app')

@section('title', 'Edit '.$product->name)

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('products.update', $product) }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        <option value="">None</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Unit</label>
                    <select name="unit" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        @foreach (['piece', 'loaf', 'box', 'kg', 'bag'] as $unit)
                            <option value="{{ $unit }}" @selected(old('unit', $product->unit) === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Selling price</label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Low stock threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0" step="0.01"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="3"
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">{{ old('description', $product->description) }}</textarea>
            </div>

            <div>
                <label class="flex items-center text-sm font-medium text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($product->is_active) class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <span class="ml-2">Active (visible in POS)</span>
                </label>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Save Changes</button>
                <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
