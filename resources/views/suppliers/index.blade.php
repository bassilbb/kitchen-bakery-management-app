@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
        </form>
        <a href="{{ route('suppliers.create') }}"
           class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">+ New Supplier</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Supplier</th>
                        <th class="px-5 py-3">Contact person</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3 text-center">Ingredients</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $supplier->name }}</td>
                            <td class="px-5 py-3">{{ $supplier->contact_person ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $supplier->phone ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $supplier->email ?: '-' }}</td>
                            <td class="px-5 py-3 text-center">{{ $supplier->ingredients_count }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-amber-600 hover:text-amber-500 font-medium mr-3">Edit</a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline"
                                      onsubmit="return confirm('Delete this supplier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
