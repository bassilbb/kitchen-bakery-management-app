@extends('layouts.app')

@section('title', 'Sell (POS)')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6" id="pos-app">
        <div class="xl:col-span-2">
            <form method="GET" action="{{ route('pos.index') }}" id="pos-search-form" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="pos-search" value="{{ request('search') }}" placeholder="Search products..."
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 flex-1 min-w-40">
                <select name="category_id" id="pos-category"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </form>

            <div id="pos-products" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @include('pos._products', ['products' => $products])
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 sticky top-6" id="pos-cart">
                @include('pos._cart', ['cart' => $cart, 'products' => $products, 'customers' => $customers, 'paystackConfigured' => $paystackConfigured])
            </div>

            <div id="pos-held-container">
                @include('pos._held', ['heldCarts' => $heldCarts])
            </div>
        </div>
    </div>

    <div id="pos-flash"></div>
@endsection
