<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen lg:flex">
    @php
        $logoUrl = \App\Models\Setting::logoUrl();
        $companyName = \App\Models\Setting::companyName();
    @endphp

    <aside class="relative hidden lg:flex lg:w-1/2 lg:flex-col lg:justify-between overflow-hidden bg-slate-900 px-12 py-10">
        <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-amber-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 -left-16 h-80 w-80 rounded-full bg-amber-600/10 blur-3xl"></div>

        <a href="{{ route('login') }}" class="relative flex items-center gap-3">
            @if ($logoUrl)
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/95 p-1 overflow-hidden">
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain">
                </span>
            @else
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-slate-900 font-black text-2xl">B</span>
            @endif
            <span>
                <span class="block text-lg font-bold text-white">{{ $companyName }}</span>
                <span class="block text-xs text-slate-400">Management System</span>
            </span>
        </a>

        <div class="relative max-w-md">
            <h1 class="text-3xl font-bold leading-snug text-white">
                Run your kitchen &amp; bakery from one simple dashboard.
            </h1>
            <p class="mt-4 text-sm leading-relaxed text-slate-400">
                Track raw materials, bake to order, take payments and keep an eye on
                stock - all in one place.
            </p>
            <ul class="mt-8 space-y-3 text-sm text-slate-300">
                <li class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-amber-400"><x-svg-icon icon="ingredients" /></span>
                    Ingredient inventory &amp; supplier purchases
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-amber-400"><x-svg-icon icon="production" /></span>
                    Recipes, production batches &amp; finished goods
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-amber-400"><x-svg-icon icon="reports" /></span>
                    Daily sales, reports &amp; full stock history
                </li>
            </ul>
        </div>

        <p class="relative text-xs text-slate-500">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
    </aside>

    <main class="flex flex-1 items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center lg:hidden">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="mx-auto h-14 w-14 rounded-2xl object-contain">
                @else
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500 text-slate-900 font-black text-2xl">B</span>
                @endif
                <p class="mt-3 text-lg font-bold text-slate-900">{{ $companyName }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-8 py-8">
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
