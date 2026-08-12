@extends('layouts.app')

@section('title', 'New Production Request')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Plan a Batch</h2>
            <form method="GET" action="{{ route('production-requests.create') }}" class="flex gap-2 items-end">
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
                <h2 class="font-semibold text-slate-900 mb-3">Ingredients for {{ request('quantity', 1) }}x {{ $selected->name }}</h2>

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
                                    <p class="text-xs {{ $req['ok'] ? 'text-slate-500' : 'text-rose-600' }}">
                                        Available: {{ $req['available'] }} {{ $req['ok'] ? '' : '- insufficient' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @php $hasShortage = collect($requirements)->contains(fn ($r) => ! $r['ok']); @endphp

                    <form method="POST" action="{{ route('production-requests.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $selected->id }}">
                        <input type="hidden" name="quantity" value="{{ request('quantity', 1) }}">
                        <input type="text" name="note" placeholder="Note (optional)"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">

                        @if ($hasShortage && auth()->user()->isAdmin())
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="force" value="1" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                                Approve exception - allow requesting more than available stock
                            </label>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="action" value="save"
                                    class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Save as draft
                            </button>
                            <button type="submit" name="action" value="submit"
                                    class="rounded-lg bg-amber-500 px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                                Submit for review
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 text-center">Stock is only deducted when the kitchen issues the ingredients.</p>
                    </form>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center justify-center text-slate-400 text-sm">
                Choose a product and quantity to calculate the ingredients the kitchen must issue.
            </div>
        @endif
    </div>
@endsection
