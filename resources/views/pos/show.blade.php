@extends('layouts.app')

@section('title', 'Receipt '.$order->order_number)

@php
    $companyName = \App\Models\Setting::companyName();
    $logoUrl = \App\Models\Setting::logoUrl();
@endphp

@push('styles')
    <style>
        .receipt-sheet {
            background:
                radial-gradient(ellipse at 50% 0%, rgba(245, 158, 11, 0.06), transparent 60%),
                #ffffff;
        }

        .receipt-heading {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
        }

        .receipt-divider {
            background-image: linear-gradient(90deg, #e2e8f0 0%, #e2e8f0 6px, transparent 6px, transparent 12px);
            background-size: 12px 1px;
            background-repeat: repeat-x;
            height: 1px;
        }

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
                padding: 0 !important;
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

            .receipt-sheet {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 8mm;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-2xl mx-auto print-receipt">
        <div class="receipt-sheet rounded-xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="receipt-heading px-6 py-8 text-center">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="mx-auto h-14 w-14 rounded-lg bg-white object-contain p-1 mb-3">
                @else
                    <div class="mx-auto mb-3 inline-flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-black text-slate-900 shadow-lg">
                        {{ mb_substr($companyName, 0, 1) }}
                    </div>
                @endif
                <h2 class="text-2xl font-bold tracking-tight">{{ $companyName }}</h2>
                <p class="mt-1 text-sm font-medium uppercase tracking-widest text-amber-400">Sales Receipt</p>
                <span class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-white/10 text-white border border-white/20 {{ $order->status === 'completed' ? 'text-emerald-300 border-emerald-400/30' : '' }}">
                    <span class="mr-1.5 inline-block h-1.5 w-1.5 rounded-full {{ $order->status === 'completed' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            {{-- Meta --}}
            <div class="px-6 py-5">
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Receipt No.</p>
                        <p class="font-mono font-semibold text-slate-900">{{ $order->order_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Date</p>
                        <p class="font-semibold text-slate-900">{{ $order->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $order->created_at->format('h:i A') }}</p>
                    </div>
                </div>

                <div class="receipt-divider my-5"></div>

                <div class="grid grid-cols-2 gap-y-3 text-sm">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Customer</p>
                        <p class="font-medium text-slate-900">{{ $order->customer_name ?: 'Walk-in customer' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Payment method</p>
                        <p class="font-medium text-slate-900">{{ ucfirst($order->payment_method) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Served by</p>
                        <p class="font-medium text-slate-900">{{ $order->user?->name ?: '-' }}</p>
                    </div>
                    @if ($order->transaction_reference)
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-medium">Payment ref</p>
                            <p class="font-mono text-xs font-medium text-slate-900">{{ $order->transaction_reference }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="px-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-400 border-y border-slate-100">
                            <th class="py-2.5">Item</th>
                            <th class="py-2.5 text-center">Qty</th>
                            <th class="py-2.5 text-right">Price</th>
                            <th class="py-2.5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-3 font-medium text-slate-800">{{ $item->product_name }}</td>
                                <td class="py-3 text-center text-slate-600">{{ $item->quantity }}</td>
                                <td class="py-3 text-right text-slate-600">{{ config('pos.currency') }}{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right font-semibold text-slate-900">{{ config('pos.currency') }}{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="px-6 pb-6">
                <div class="receipt-divider my-5"></div>
                <div class="ml-auto max-w-xs space-y-1.5 text-sm">
                    <div class="flex justify-between text-slate-600"><span>Subtotal</span><span>{{ config('pos.currency') }}{{ number_format($order->subtotal, 2) }}</span></div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between text-rose-600"><span>Discount</span><span>-{{ config('pos.currency') }}{{ number_format($order->discount, 2) }}</span></div>
                    @endif
                    @if ($order->tax > 0)
                        <div class="flex justify-between text-slate-600"><span>Tax</span><span>{{ config('pos.currency') }}{{ number_format($order->tax, 2) }}</span></div>
                    @endif
                    <div class="flex items-center justify-between rounded-lg bg-amber-500 px-4 py-3 mt-3">
                        <span class="text-sm font-bold uppercase tracking-wide text-slate-900">Total</span>
                        <span class="text-xl font-black text-slate-900">{{ config('pos.currency') }}{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                @if ($order->note)
                    <p class="mt-4 text-sm text-slate-500 border-t border-dashed border-slate-200 pt-3">
                        <span class="font-medium text-slate-600">Note:</span> {{ $order->note }}
                    </p>
                @endif
            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-100 bg-slate-50 px-6 py-5 text-center">
                <p class="text-sm font-semibold text-slate-700">Thank you for your order!</p>
                <p class="mt-1 text-xs text-slate-400">We appreciate your business. Please keep this receipt for your records.</p>
            </div>
        </div>

        <div class="flex justify-center gap-3 mt-6 no-print">
            <a href="{{ route('pos.index') }}" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">New Sale</a>
            <button onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Print Receipt</button>
            <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">View in Orders</a>
        </div>
    </div>
@endsection
