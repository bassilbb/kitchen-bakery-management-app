@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Title</label>
                    <input type="text" name="title" value="{{ old('title', $expense->title) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category</label>
                    <select name="category" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        @foreach (\App\Models\Expense::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $expense->category) == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Amount</label>
                    <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" required min="0.01" step="0.01"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Date</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Note</label>
                    <textarea name="note" rows="3"
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">{{ old('note', $expense->note) }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Save Changes</button>
                <a href="{{ route('expenses.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
