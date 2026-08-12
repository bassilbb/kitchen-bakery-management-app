@extends('layouts.app')

@section('title', 'Baking / Production')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('productions.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="product_id" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        </form>
        <a href="{{ route('production-requests.create') }}"
           class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">+ New Production Request</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Batch</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3 text-right">Quantity</th>
                        <th class="px-5 py-3 text-right">Unit cost</th>
                        <th class="px-5 py-3 text-right">Total cost</th>
                        <th class="px-5 py-3">Baked by</th>
                        <th class="px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productions as $production)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('productions.show', $production) }}" class="font-medium text-amber-600 hover:text-amber-500">{{ $production->production_number }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $production->product->name }}</td>
                            <td class="px-5 py-3 text-right">{{ $production->quantity }} {{ $production->product->unit }}</td>
                            <td class="px-5 py-3 text-right">{{ config('pos.currency') }}{{ number_format($production->unit_cost, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ config('pos.currency') }}{{ number_format($production->total_cost, 2) }}</td>
                            <td class="px-5 py-3">{{ $production->user?->name ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $production->produced_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">No production batches found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($productions->hasPages())
            <div class="px-5 py-3 border-t border-slate-200">{{ $productions->links() }}</div>
        @endif
    </div>
@endsection
