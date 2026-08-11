@extends('layouts.app')

@section('title', 'Order '.$order->order_number)

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4 mb-4">
                <div>
                    <p class="text-sm text-slate-500">Order</p>
                    <p class="text-xl font-bold text-slate-900">{{ $order->order_number }}</p>
                </div>
                @php
                    $badge = [
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'pending' => 'bg-amber-100 text-amber-700',
                        'failed' => 'bg-rose-100 text-rose-700',
                        'refunded' => 'bg-slate-100 text-slate-500',
                    ][$order->status] ?? 'bg-slate-100 text-slate-500';
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Customer</dt>
                    <dd class="font-medium">
                        @if ($order->customer)
                            <a href="{{ route('customers.show', $order->customer) }}" class="text-amber-600 hover:text-amber-500">{{ $order->customer->name }}</a>
                        @else
                            {{ $order->customer_name ?: 'Walk-in' }}
                        @endif
                    </dd>
                </div>
                <div><dt class="text-slate-500">Payment</dt><dd class="font-medium">{{ ucfirst($order->payment_method) }}</dd></div>
                <div><dt class="text-slate-500">Sold by</dt><dd class="font-medium">{{ $order->user?->name ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $order->created_at->format('M d, Y h:i A') }}</dd></div>
            </dl>

            @if ($order->transaction_reference)
                <p class="mt-4 text-sm text-slate-500 bg-slate-50 rounded-lg px-4 py-3">
                    Payment reference: <span class="font-mono font-medium text-slate-700">{{ $order->transaction_reference }}</span>
                </p>
            @endif

            <table class="w-full text-sm mt-5">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-2">Item</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Price</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="py-2.5">{{ $item->product_name }}</td>
                            <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                            <td class="py-2.5 text-right">{{ config('pos.currency') }}{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 text-right font-medium">{{ config('pos.currency') }}{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pt-4 space-y-1 text-sm border-t border-slate-200 mt-4 ml-auto max-w-xs">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>{{ config('pos.currency') }}{{ number_format($order->subtotal, 2) }}</span></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between"><span class="text-slate-500">Discount</span><span>-{{ config('pos.currency') }}{{ number_format($order->discount, 2) }}</span></div>
                @endif
                @if ($order->tax > 0)
                    <div class="flex justify-between"><span class="text-slate-500">Tax</span><span>{{ config('pos.currency') }}{{ number_format($order->tax, 2) }}</span></div>
                @endif
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                    <span>Total</span><span>{{ config('pos.currency') }}{{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            @if ($order->note)
                <p class="mt-4 text-sm text-slate-600 bg-slate-50 rounded-lg px-4 py-3">Note: {{ $order->note }}</p>
            @endif

            @if ($order->isPending())
                <div class="mt-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    This order is awaiting online payment confirmation.
                </div>
            @endif

            @if (! $order->isRefunded() && $order->isPaid())
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('pos.show', $order) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">View Receipt</a>
                    <form method="POST" action="{{ route('orders.refund', $order) }}" onsubmit="return confirm('Refund this order and return items to stock?')">
                        @csrf
                        <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Refund Order</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
