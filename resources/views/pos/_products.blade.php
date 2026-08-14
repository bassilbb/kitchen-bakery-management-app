@forelse ($products as $product)
    <button type="button" data-add-product="{{ $product->id }}"
            {{ $product->stock_qty <= 0 ? 'disabled' : '' }}
            class="w-full text-left bg-white rounded-xl border border-slate-200 p-4 hover:border-amber-400 hover:shadow-sm transition {{ $product->stock_qty <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
        <div class="flex items-start justify-between">
            <span class="text-xs uppercase tracking-wide text-slate-400 font-semibold">{{ $product->category?->name ?: 'Uncategorized' }}</span>
            <span class="text-xs font-semibold {{ $product->stock_qty <= 0 ? 'text-rose-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-emerald-600') }}">
                {{ $product->stock_qty }} left
            </span>
        </div>
        <p class="mt-1 font-semibold text-slate-900">{{ $product->name }}</p>
        <p class="mt-1 text-amber-600 font-bold">{{ config('pos.currency') }}{{ number_format($product->price, 2) }}</p>
    </button>
@empty
    <p class="col-span-full text-sm text-slate-500 py-8 text-center">No products found. Add products first.</p>
@endforelse
