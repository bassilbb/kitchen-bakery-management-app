@extends('layouts.app')

@section('title', 'Edit '.$ingredient->name)

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('ingredients.update', $ingredient) }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $ingredient->sku) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Unit</label>
                    <select name="unit" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        @foreach (['kg', 'g', 'L', 'ml', 'piece', 'box', 'bag'] as $unit)
                            <option value="{{ $unit }}" @selected(old('unit', $ingredient->unit) === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Supplier</label>
                    <select name="supplier_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        <option value="">None</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $ingredient->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Cost per unit</label>
                    <input type="number" name="cost_per_unit" value="{{ old('cost_per_unit', $ingredient->cost_per_unit) }}" min="0" step="0.01"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Low stock threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $ingredient->low_stock_threshold) }}" min="0" step="0.01"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Save Changes</button>
                <a href="{{ route('ingredients.show', $ingredient) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
