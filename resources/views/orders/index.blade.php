@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Order # or customer..."
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">All statuses</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                <option value="refunded" @selected(request('status') === 'refunded')>Refunded</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        </form>
        <a href="{{ route('pos.index') }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">New Sale</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3 text-right">Items</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('orders.show', $order) }}" class="font-medium text-amber-600 hover:text-amber-500">{{ $order->order_number }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $order->customer_name ?: 'Walk-in' }}</td>
                            <td class="px-5 py-3 text-right">{{ $order->items->sum('quantity') }}</td>
                            <td class="px-5 py-3 text-right font-medium">{{ config('pos.currency') }}{{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-3">{{ ucfirst($order->payment_method) }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = [
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'failed' => 'bg-rose-100 text-rose-700',
                                        'refunded' => 'bg-slate-100 text-slate-500',
                                    ][$order->status] ?? 'bg-slate-100 text-slate-500';
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="px-5 py-3 border-t border-slate-200">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
