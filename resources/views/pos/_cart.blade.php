@php
    $items = [];
    $subtotal = 0;
    foreach ($cart as $id => $qty) {
        $product = $products->firstWhere('id', $id);
        if ($product) {
            $items[] = ['product' => $product, 'qty' => (float)$qty, 'line' => round($product->price * $qty, 2)];
            $subtotal += $product->price * $qty;
        }
    }
    $subtotal = round($subtotal, 2);
    $taxRate = (float) config('pos.tax_rate', 0);
@endphp

<div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-slate-900">Current Sale</h2>
    <button type="button" data-clear-cart class="text-sm text-rose-600 hover:text-rose-500 font-medium">Clear</button>
</div>

@if (empty($items))
    <p class="text-sm text-slate-500 py-8 text-center">Cart is empty.<br>Tap a product to add it.</p>
@else
    <div class="space-y-3 mb-4" id="pos-cart-items">
        @foreach ($items as $item)
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate">{{ $item['product']->name }}</p>
                    <p class="text-xs text-slate-500">{{ config('pos.currency') }}{{ number_format($item['product']->price, 2) }} each</p>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" data-qty-decrement="{{ $item['product']->id }}"
                            class="w-7 h-7 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">-</button>
                    <input type="number" name="qty" value="{{ $item['qty'] }}" min="0.01" step="0.01"
                           data-qty-input="{{ $item['product']->id }}"
                           class="w-14 rounded-lg border border-slate-300 px-2 py-1 text-sm text-center focus:border-amber-500">
                    <button type="button" data-qty-increment="{{ $item['product']->id }}"
                            class="w-7 h-7 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">+</button>
                </div>
                <span class="text-sm font-semibold w-20 text-right" data-line-total>{{ config('pos.currency') }}{{ number_format($item['line'], 2) }}</span>
                <button type="button" data-remove-item="{{ $item['product']->id }}" class="text-slate-400 hover:text-rose-500 text-sm px-1">&times;</button>
            </div>
        @endforeach
    </div>

    <div class="space-y-1 text-sm mb-4" id="pos-totals"
         data-subtotal="{{ $subtotal }}"
         data-tax-rate="{{ $taxRate }}">
        <div class="flex justify-between">
            <span class="text-slate-500">Subtotal</span>
            <span class="font-medium" data-total-subtotal>{{ config('pos.currency') }}{{ number_format($subtotal, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Discount</span>
            <span class="font-medium" data-total-discount>{{ config('pos.currency') }}0.00</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Tax ({{ $taxRate }}%)</span>
            <span class="font-medium" data-total-tax>{{ config('pos.currency') }}{{ number_format($subtotal * $taxRate / 100, 2) }}</span>
        </div>
        <div class="flex justify-between text-base font-bold pt-1">
            <span>Total</span>
            <span data-total-grand>{{ config('pos.currency') }}{{ number_format($subtotal * (1 + $taxRate / 100), 2) }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('pos.checkout') }}" class="space-y-3" id="pos-checkout-form">
        @csrf
        <select name="customer_id" id="pos-customer-select" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
            <option value="">Walk-in customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
        <input type="text" name="customer_name" id="pos-customer-name" placeholder="Or new customer name"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
        <input type="number" name="discount" id="pos-discount" value="0" min="0" step="0.01" placeholder="Discount amount"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
        <select name="payment_method" id="pos-payment-method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="online" @disabled(! $paystackConfigured)>Online</option>
        </select>
        @if (! $paystackConfigured)
            <p class="text-xs text-amber-600">Online payments need Paystack keys. An admin can add them in
                <a href="{{ route('settings.index') }}" class="underline">Settings</a>.</p>
        @endif
        <div class="grid grid-cols-2 gap-2">
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
                Complete Sale
            </button>
            <button type="button" data-hold-sale
                    class="rounded-lg bg-slate-600 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-500">
                Hold Sale
            </button>
        </div>
    </form>
@endif
