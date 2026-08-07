@extends('layouts.app')

@section('title', 'New Production Batch')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Start a Batch</h2>
            <form method="GET" action="{{ route('productions.create') }}" class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700">Product</label>
                    <select name="product_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                        <option value="">-- choose product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected($selected?->id === $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Quantity</label>
                    <input type="number" name="quantity" value="{{ request('quantity', 1) }}" min="0.001" step="0.001"
                           class="mt-1 block w-32 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Check</button>
            </form>
        </div>

        @if ($selected)
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Requirements for {{ request('quantity', 1) }}x {{ $selected->name }}</h2>

                @if ($selected->recipeItems->isEmpty())
                    <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        This product has no recipe yet. Add ingredients to the recipe on the product page first.
                    </p>
                @else
                    <div class="space-y-2 mb-4">
                        @foreach ($requirements as $req)
                            <div class="flex items-center justify-between text-sm px-4 py-2.5 rounded-lg border {{ $req['ok'] ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }}">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $req['ingredient']->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $req['per_unit'] }} {{ $req['ingredient']->unit }} per unit</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold {{ $req['ok'] ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $req['needed'] }} {{ $req['ingredient']->unit }}
                                    </p>
                                    <p class="text-xs text-slate-500">Available: {{ $req['available'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if (empty($missing))
                        <form method="POST" action="{{ route('productions.store') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $selected->id }}">
                            <input type="hidden" name="quantity" value="{{ request('quantity', 1) }}">
                            <input type="text" name="note" placeholder="Note (optional)"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
                                Produce {{ request('quantity', 1) }}x {{ $selected->name }} ({{ config('pos.currency') }}{{ number_format($selected->cost * request('quantity', 1), 2) }})
                            </button>
                            <p class="text-xs text-slate-500 text-center">This will deduct ingredients and add finished stock.</p>
                        </form>
                    @else
                        <p class="text-sm text-rose-700 bg-rose-50 border border-rose-200 rounded-lg px-4 py-3">
                            Not enough stock for: {{ implode(', ', $missing) }}. Receive stock from your suppliers first.
                        </p>
                    @endif
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center justify-center text-slate-400 text-sm">
                Choose a product and quantity to see the ingredient requirements.
            </div>
        @endif
    </div>
@endsection
