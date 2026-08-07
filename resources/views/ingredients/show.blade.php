@extends('layouts.app')

@section('title', $ingredient->name)

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ingredient->isLowStock() ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                {{ $ingredient->stock_qty }} {{ $ingredient->unit }} in stock
            </span>
            <span class="text-sm text-slate-500">Value: {{ config('pos.currency') }}{{ number_format($ingredient->stockValue(), 2) }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('suppliers.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Manage Suppliers</a>
            <a href="{{ route('ingredients.edit', $ingredient) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Details</h2>
                <dl class="grid grid-cols-2 gap-y-3 text-sm">
                    <div><dt class="text-slate-500">SKU</dt><dd class="font-medium">{{ $ingredient->sku ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">Unit</dt><dd class="font-medium">{{ $ingredient->unit }}</dd></div>
                    <div><dt class="text-slate-500">Cost per unit</dt><dd class="font-medium">{{ config('pos.currency') }}{{ number_format($ingredient->cost_per_unit, 2) }}</dd></div>
                    <div><dt class="text-slate-500">Supplier</dt><dd class="font-medium">{{ $ingredient->supplier?->name ?: '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Movement History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4 text-right">Qty</th>
                                <th class="py-2 pr-4 text-right">Unit cost</th>
                                <th class="py-2">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ingredient->movements as $movement)
                                <tr>
                                    <td class="py-2 pr-4 text-slate-500">{{ $movement->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $movement->type === 'purchase' ? 'bg-emerald-100 text-emerald-700' : ($movement->type === 'usage' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ ucfirst($movement->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 text-right font-medium {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} {{ $ingredient->unit }}
                                    </td>
                                    <td class="py-2 pr-4 text-right">{{ $movement->unit_cost ? config('pos.currency').number_format($movement->unit_cost, 2) : '-' }}</td>
                                    <td class="py-2 text-slate-500">{{ $movement->reference }} {{ $movement->note }} {{ $movement->supplier?->name ? '('. $movement->supplier->name .')' : '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-slate-500">No movements recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Receive Stock (Purchase)</h2>
                <form method="POST" action="{{ route('ingredients.purchase', $ingredient) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Quantity</label>
                        <input type="number" name="quantity" step="0.001" min="0.001" required placeholder="e.g. 10"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Unit cost</label>
                        <input type="number" name="unit_cost" step="0.01" min="0" required
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Supplier</label>
                        <select name="supplier_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                            <option value="">None</option>
                            @foreach (\App\Models\Supplier::orderBy('name')->get() as $supplier)
                                <option value="{{ $supplier->id }}" @selected($ingredient->supplier_id === $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Note</label>
                        <input type="text" name="note" placeholder="e.g. weekly delivery"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Add to Stock</button>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Adjust Stock</h2>
                <form method="POST" action="{{ route('ingredients.adjust-stock', $ingredient) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Change amount (+/-)</label>
                        <input type="number" name="adjustment" step="0.001" required placeholder="e.g. 2 or -1.5"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500">Reason</label>
                        <input type="text" name="note" placeholder="e.g. spillage, spoilage, count"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Apply Adjustment</button>
                </form>
            </div>
        </div>
    </div>
@endsection
