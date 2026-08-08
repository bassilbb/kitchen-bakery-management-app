@extends('layouts.app')

@section('title', 'Company Settings')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900">Logo</h2>
            <p class="text-sm text-slate-500 mt-1">Upload your company logo. It will appear in the sidebar and on the login page.</p>

            <div class="mt-5 flex items-center gap-5">
                @php $logoUrl = \App\Models\Setting::logoUrl(); @endphp
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Company logo" class="h-full w-full object-contain">
                    @else
                        <span class="text-slate-300 font-black text-3xl">B</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">PNG, JPG, GIF, SVG or WebP.<br>Max 2 MB.</p>
            </div>

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <label class="block text-sm font-medium text-slate-700">Company logo</label>
                <input type="file" name="logo" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp"
                       class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-900 hover:file:bg-amber-400">
                <button type="submit" class="mt-4 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                    Save logo
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900">Company details</h2>
            <p class="text-sm text-slate-500 mt-1">The name shown next to the logo.</p>

            <form method="POST" action="{{ route('settings.update') }}" class="mt-5">
                @csrf
                <label for="company_name" class="block text-sm font-medium text-slate-700">Company name</label>
                <input id="company_name" type="text" name="company_name" value="{{ \App\Models\Setting::get('company_name') }}"
                       placeholder="Kitchen &amp; Bakery Manager"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <button type="submit" class="mt-4 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                    Save name
                </button>
            </form>
        </div>
    </div>
@endsection
