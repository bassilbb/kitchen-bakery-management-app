@extends('layouts.app')

@section('title', $customer->name)

@section('content')
    <div class="max-w-4xl">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    @if ($customer->phone){{ $customer->phone }}@endif
                    @if ($customer->email) &middot; {{ $customer->email }}@endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                      onsubmit="return confirm('Delete this customer? Their order history is kept.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Delete</button>
                </form>
            </div>
        </div>

        @if ($customer->address)
            <p class="text-sm text-slate-600 mb-4">{{ $customer->address }}</p>
        @endif

        @if ($customer->notes)
            <p class="text-sm text-slate-600 bg-slate-50 rounded-lg px-4 py-3 mb-4">Notes: {{ $customer->notes }}</p>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Orders</p>
                <p class="text-xl font-bold text-slate-900 mt-1">{{ $completedOrders }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Total spent</p>
                <p class="text-xl font-bold text-slate-900 mt-1">{{ config('pos.currency') }}{{ number_format($totalSpent, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Member since</p>
                <p class="text-xl font-bold text-slate-900 mt-1">{{ $customer->created_at->format('M Y') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Average order</p>
                <p class="text-xl font-bold text-slate-900 mt-1">
                    {{ config('pos.currency') }}{{ number_format($completedOrders > 0 ? $totalSpent / $completedOrders : 0, 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="font-semibold text-slate-900">Order history</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Payment</th>
                            <th class="px-5 py-3 text-center">Items</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">
                                    <a href="{{ route('orders.show', $order) }}" class="hover:text-amber-600">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-5 py-3">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-5 py-3">{{ ucfirst($order->payment_method) }}</td>
                                <td class="px-5 py-3 text-center">{{ $order->items->sum('quantity') }}</td>
                                <td class="px-5 py-3 text-right font-medium">{{ config('pos.currency') }}{{ number_format($order->total, 2) }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $order->isRefunded() ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-200">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
