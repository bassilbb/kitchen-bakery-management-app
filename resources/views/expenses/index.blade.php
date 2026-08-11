@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="category" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
                <option value="">All categories</option>
                @foreach (\App\Models\Expense::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" @selected(request('category') == $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>
        <a href="{{ route('expenses.create') }}"
           class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">+ Record Expense</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4 flex items-center justify-between">
        <span class="text-sm text-slate-500">Total for filters</span>
        <span class="text-lg font-bold text-slate-900">{{ config('pos.currency') }}{{ number_format($total, 2) }}</span>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Recorded by</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($expenses as $expense)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">
                                {{ $expense->title }}
                                @if ($expense->note)
                                    <span class="block text-xs text-slate-400 font-normal">{{ $expense->note }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    {{ $expense->categoryLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3">{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td class="px-5 py-3">{{ $expense->user?->name ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-rose-600">-{{ config('pos.currency') }}{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-amber-600 hover:text-amber-500 font-medium mr-3">Edit</a>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-500 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No expenses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-slate-200">
            {{ $expenses->links() }}
        </div>
    </div>
@endsection
