@if (! empty($heldCarts))
    <div class="bg-white rounded-xl border border-slate-200 p-5 mt-4" id="pos-held">
        <h2 class="font-semibold text-slate-900 mb-3">Held Sales</h2>
        <div class="space-y-3">
            @foreach ($heldCarts as $held)
                <div class="border border-slate-100 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-slate-900">{{ $held['label'] }}</p>
                        <span class="text-xs text-slate-400">{{ $held['held_at'] }}</span>
                    </div>
                    <p class="text-xs text-slate-500">
                        @foreach ($held['items'] as $item)
                            {{ $item['qty'] }}x {{ $item['name'] }}@if (! $loop->last), @endif
                        @endforeach
                    </p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-sm font-bold">{{ config('pos.currency') }}{{ number_format($held['total'], 2) }}</span>
                        <div class="flex gap-2">
                            <button type="button" data-resume-hold="{{ $held['key'] }}"
                                    class="text-amber-600 hover:text-amber-500 text-sm font-medium">Resume</button>
                            <button type="button" data-discard-hold="{{ $held['key'] }}"
                                    class="text-rose-600 hover:text-rose-500 text-sm font-medium">Discard</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
