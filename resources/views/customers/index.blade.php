@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..."
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
        </form>
        <div class="flex gap-2">
            <a href="{{ route('customers.export', request()->query()) }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Export CSV</a>
            <a href="{{ route('customers.create') }}"
               class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">+ New Customer</a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3 text-center">Orders</th>
                        <th class="px-5 py-3 text-right">Total spent</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">
                                <a href="{{ route('customers.show', $customer) }}" class="hover:text-amber-600">{{ $customer->name }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $customer->phone ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $customer->email ?: '-' }}</td>
                            <td class="px-5 py-3 text-center">{{ $customer->orders_count }}</td>
                            <td class="px-5 py-3 text-right font-medium">{{ config('pos.currency') }}{{ number_format($customer->total_spent, 2) }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('customers.show', $customer) }}" class="text-amber-600 hover:text-amber-500 font-medium mr-3">View</a>
                                <a href="{{ route('customers.edit', $customer) }}" class="text-slate-500 hover:text-slate-700 font-medium mr-3">Edit</a>
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline"
                                      onsubmit="return confirm('Delete this customer? Their order history is kept.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-slate-200">
            {{ $customers->links() }}
        </div>
    </div>
@endsection
