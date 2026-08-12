@extends('layouts.app')

@section('title', 'Request '.$productionRequest->request_number)

@section('content')
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
        $user = auth()->user();
    @endphp

    <div class="max-w-3xl mx-auto space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 pb-4 mb-4">
                <div>
                    <p class="text-sm text-slate-500">Production request</p>
                    <p class="text-xl font-bold text-slate-900">{{ $productionRequest->request_number }}</p>
                </div>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $productionRequest->statusLabel() }}</span>
            </div>

            <dl class="grid grid-cols-2 gap-y-3 text-sm">
                <div><dt class="text-slate-500">Product</dt><dd class="font-medium">{{ $productionRequest->product->name }}</dd></div>
                <div><dt class="text-slate-500">Quantity planned</dt><dd class="font-medium">{{ $productionRequest->quantity }} {{ $productionRequest->product->unit }}</dd></div>
                <div><dt class="text-slate-500">Requested by</dt><dd class="font-medium">{{ $productionRequest->requester?->name ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $productionRequest->created_at->format('M d, Y h:i A') }}</dd></div>
                @if ($productionRequest->approved_at)
                    <div><dt class="text-slate-500">Approved by</dt><dd class="font-medium">{{ $productionRequest->approver?->name ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">Approved at</dt><dd class="font-medium">{{ $productionRequest->approved_at->format('M d, Y h:i A') }}</dd></div>
                @endif
                @if ($productionRequest->issued_at)
                    <div><dt class="text-slate-500">Issued by</dt><dd class="font-medium">{{ $productionRequest->issuer?->name ?: '-' }}</dd></div>
                    <div><dt class="text-slate-500">Issued at</dt><dd class="font-medium">{{ $productionRequest->issued_at->format('M d, Y h:i A') }}</dd></div>
                @endif
            </dl>

            @if ($productionRequest->note)
                <p class="mt-4 text-sm text-slate-600 bg-slate-50 rounded-lg px-4 py-3 whitespace-pre-line">Note: {{ $productionRequest->note }}</p>
            @endif

            <h2 class="font-semibold text-slate-900 mt-6 mb-3">Ingredients</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-2">Ingredient</th>
                        <th class="py-2 text-right">Required</th>
                        <th class="py-2 text-right">Available</th>
                        <th class="py-2 text-right">Issued</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($productionRequest->items as $item)
                        <tr>
                            <td class="py-2 font-medium">{{ $item->ingredient->name }}</td>
                            <td class="py-2 text-right">{{ $item->required_qty }} {{ $item->ingredient->unit }}</td>
                            <td class="py-2 text-right">{{ $item->ingredient->stock_qty }} {{ $item->ingredient->unit }}</td>
                            <td class="py-2 text-right font-medium {{ $item->issued_qty !== null && $item->issued_qty >= $item->required_qty ? 'text-emerald-600' : 'text-slate-700' }}">
                                {{ $item->issued_qty !== null ? $item->issued_qty.' '.$item->ingredient->unit : 'Not yet' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bakery: submit / cancel a draft --}}
        @if ($productionRequest->canBeSubmittedBy($user))
            <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-wrap items-center gap-3">
                <p class="text-sm text-slate-600 flex-1">Ready to ask the kitchen for these ingredients?</p>
                <form method="POST" action="{{ route('production-requests.submit', $productionRequest) }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">Submit for review</button>
                </form>
            </div>
        @endif

        @if ($productionRequest->canBeCancelledBy($user) && $productionRequest->status !== \App\Models\ProductionRequest::STATUS_CANCELLED)
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <form method="POST" action="{{ route('production-requests.cancel', $productionRequest) }}" onsubmit="return confirm('Cancel this production request?');">
                    @csrf
                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Cancel request</button>
                </form>
            </div>
        @endif

        {{-- Kitchen: approve or reject a submitted request --}}
        @if ($productionRequest->canBeApprovedBy($user) || $productionRequest->canBeRejectedBy($user))
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Review</h3>
                <form method="POST" action="{{ route('production-requests.approve', $productionRequest) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                        Approve &amp; prepare for issuance
                    </button>
                </form>
                <form method="POST" action="{{ route('production-requests.reject', $productionRequest) }}">
                    @csrf
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection (optional)"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500 mb-2">
                    <button type="submit" class="w-full rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                        Reject request
                    </button>
                </form>
            </div>
        @endif

        {{-- Kitchen: issue the ingredients --}}
        @if ($productionRequest->canBeIssuedBy($user))
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-1">Issue Ingredients</h3>
                <p class="text-xs text-slate-500 mb-3">Issuing deducts stock immediately. You may issue less than requested, but never more than is available.</p>
                <form method="POST" action="{{ route('production-requests.issue', $productionRequest) }}" class="space-y-3">
                    @csrf
                    <div class="space-y-2">
                        @foreach ($productionRequest->items as $item)
                            <div class="flex items-center gap-3 text-sm">
                                <span class="w-40 font-medium text-slate-700">{{ $item->ingredient->name }}</span>
                                <span class="text-slate-500 flex-1">
                                    {{ $item->ingredient->stock_qty }} {{ $item->ingredient->unit }} available,
                                    {{ $item->required_qty }} requested
                                    @if ($item->issued_qty)
                                        ({{ $item->issued_qty }} issued)
                                    @endif
                                </span>
                                <div class="flex items-center gap-1">
                                    <input type="number" name="issued[{{ $item->id }}]"
                                           value="{{ $item->required_qty - (float) $item->issued_qty }}"
                                           min="0" max="{{ $item->required_qty - (float) $item->issued_qty }}" step="0.001"
                                           class="w-28 rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-amber-500">
                                    <span class="text-xs text-slate-400">{{ $item->ingredient->unit }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                        Confirm issuance - deduct stock
                    </button>
                </form>
            </div>
        @endif

        {{-- Bakery: record the finished batch once issued --}}
        @if ($productionRequest->canBeProducedBy($user))
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-1">Record Production</h3>
                <p class="text-xs text-slate-500 mb-3">Ingredients are already issued. Record how many units you actually produced.</p>
                <form method="POST" action="{{ route('production-requests.produce', $productionRequest) }}" class="grid grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Units produced</label>
                        <input type="number" name="quantity" value="{{ $productionRequest->quantity }}" min="0.001" step="0.001"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Wastage / rejected</label>
                        <input type="number" name="wastage" value="0" min="0" step="0.001"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="note" placeholder="Note (optional)"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                    </div>
                    <button type="submit" class="col-span-2 rounded-lg bg-amber-500 px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                        Complete production
                    </button>
                </form>
            </div>
        @endif

        @if ($productionRequest->production)
            <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center justify-between">
                <p class="text-sm text-slate-600">Production completed.</p>
                <a href="{{ route('productions.show', $productionRequest->production) }}"
                   class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">View batch</a>
            </div>
        @endif

        <div class="flex gap-3">
            <a href="{{ route('production-requests.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">All Requests</a>
            @if (auth()->user()->isAdmin() || auth()->user()->isBakery())
                <a href="{{ route('production-requests.create') }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">New Request</a>
            @endif
        </div>
    </div>
@endsection
