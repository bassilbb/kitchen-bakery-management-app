@extends('layouts.app')

@section('title', 'Batch '.$production->production_number)

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-4">
                <div>
                    <p class="text-sm text-slate-500">Production batch</p>
                    <p class="text-xl font-bold text-slate-900">{{ $production->production_number }}</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">Completed</span>
            </div>

            <dl class="grid grid-cols-2 gap-y-3 text-sm">
                <div><dt class="text-slate-500">Product</dt><dd class="font-medium">{{ $production->product->name }}</dd></div>
                <div><dt class="text-slate-500">Quantity</dt><dd class="font-medium">{{ $production->quantity }} {{ $production->product->unit }}</dd></div>
                <div><dt class="text-slate-500">Unit cost</dt><dd class="font-medium">{{ config('pos.currency') }}{{ number_format($production->unit_cost, 2) }}</dd></div>
                <div><dt class="text-slate-500">Total cost</dt><dd class="font-medium">{{ config('pos.currency') }}{{ number_format($production->total_cost, 2) }}</dd></div>
                <div><dt class="text-slate-500">Baked by</dt><dd class="font-medium">{{ $production->user?->name ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $production->produced_at->format('M d, Y h:i A') }}</dd></div>
            </dl>

            @if ($production->note)
                <p class="mt-4 text-sm text-slate-600 bg-slate-50 rounded-lg px-4 py-3">Note: {{ $production->note }}</p>
            @endif

            <h2 class="font-semibold text-slate-900 mt-6 mb-3">Ingredients Used</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-2">Ingredient</th>
                        <th class="py-2 text-right">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($usage as $move)
                        <tr>
                            <td class="py-2">{{ $move->ingredient->name }}</td>
                            <td class="py-2 text-right font-medium">{{ abs($move->quantity) }} {{ $move->ingredient->unit }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-3 text-slate-500">No ingredient usage recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('productions.create') }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">New Batch</a>
                <a href="{{ route('productions.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">All Batches</a>
            </div>
        </div>
    </div>
@endsection
