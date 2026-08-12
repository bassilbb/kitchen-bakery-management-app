@extends('layouts.app')

@section('title', 'Production Requests')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('production-requests.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">All statuses</option>
                @foreach (\App\Models\ProductionRequest::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="product_id" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </form>
        @if (auth()->user()->isAdmin() || auth()->user()->isBakery())
            <a href="{{ route('production-requests.create') }}"
               class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">+ New Production Request</a>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Request</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3">Requested by</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($requests as $productionRequest)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('production-requests.show', $productionRequest) }}" class="font-medium text-amber-600 hover:text-amber-500">{{ $productionRequest->request_number }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $productionRequest->product->name }}</td>
                            <td class="px-5 py-3 text-right">{{ $productionRequest->quantity }} {{ $productionRequest->product->unit }}</td>
                            <td class="px-5 py-3">{{ $productionRequest->requester?->name ?: '-' }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = [
                                        'draft' => 'bg-slate-100 text-slate-600',
                                        'submitted' => 'bg-sky-100 text-sky-700',
                                        'approved' => 'bg-indigo-100 text-indigo-700',
                                        'partially_issued' => 'bg-amber-100 text-amber-700',
                                        'issued' => 'bg-emerald-100 text-emerald-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'rejected' => 'bg-rose-100 text-rose-700',
                                        'cancelled' => 'bg-slate-100 text-slate-500',
                                    ][$productionRequest->status] ?? 'bg-slate-100 text-slate-500';
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $productionRequest->statusLabel() }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $productionRequest->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No production requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="px-5 py-3 border-t border-slate-200">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
