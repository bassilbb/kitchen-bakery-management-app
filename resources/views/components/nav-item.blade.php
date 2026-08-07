@props(['href', 'label', 'icon' => '', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ $active ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
    <x-svg-icon :icon="$icon" />
    <span>{{ $label }}</span>
</a>
