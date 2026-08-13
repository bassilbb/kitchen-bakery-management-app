@extends('layouts.app')

@section('title', 'Receipt '.$order->order_number)

@push('styles')
    <style>
        @media print {
            body {
                background: #fff;
            }

            aside,
            .no-print,
            .print-hidden {
                display: none !important;
            }

            main {
                margin-left: 0 !important;
            }

            main h1,
            main .bg-emerald-50,
            main .bg-rose-50 {
                display: none !important;
            }

            .print-receipt {
                max-width: none;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-2xl mx-auto print-receipt">
        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-10">
            <div class="text-center border-b border-dashed border-slate-300 pb-6">
                <p class="text-xl font-bold text-slate-900">Kitchen &amp; Bakery</p>
                <p class="text-sm text-slate-500 mt-1">Receipt / Invoice</p>
                @php
                    $badge = [
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'pending' => 'bg-amber-100 text-amber-700',
                        'failed' => 'bg-rose-100 text-rose-700',
                        'refunded' => 'bg-slate-100 text-slate-600',
                    ][$order->status] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <p class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                    {{ ucfirst($order->status) }}
                </p>
            </div>

            <div class="py-5 grid grid-cols-2 gap-y-2 text-sm">
                <div>
                    <p class="text-slate-500">Order number</p>
                    <p class="font-medium text-slate-900">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Date</p>
                    <p class="font-medium text-slate-900">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Customer</p>
                    <p class="font-medium text-slate-900">{{ $order->customer_name ?: 'Walk-in customer' }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Payment method</p>
                    <p class="font-medium text-slate-900">{{ ucfirst($order->payment_method) }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Served by</p>
                    <p class="font-medium text-slate-900">{{ $order->user?->name ?: '-' }}</p>
                </div>
                @if ($order->transaction_reference)
                    <div class="col-span-2">
                        <p class="text-slate-500">Payment reference</p>
                        <p class="font-mono font-medium text-slate-900">{{ $order->transaction_reference }}</p>
                    </div>
                @endif
            </div>

            <table class="w-full text-sm border-t border-dashed border-slate-300">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="py-2">Item</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Price</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="py-2">{{ $item->product_name }}</td>
                            <td class="py-2 text-center">{{ $item->quantity }}</td>
                            <td class="py-2 text-right">{{ config('pos.currency') }}{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 text-right font-medium">{{ config('pos.currency') }}{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pt-4 space-y-1 text-sm border-t border-dashed border-slate-300 mt-4">
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
                <p class="mt-4 text-sm text-slate-500 border-t border-dashed border-slate-300 pt-3">Note: {{ $order->note }}</p>
            @endif

            <p class="text-center text-xs text-slate-400 mt-6">Thank you for your order!</p>
        </div>

        <div class="flex justify-center gap-3 mt-6 no-print">
            <a href="{{ route('pos.index') }}" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">New Sale</a>
            <button onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Print Receipt</button>
            <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">View in Orders</a>
        </div>
    </div>
@endsection
