<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Kitchen & Bakery Manager'))</title>
    <script>window.currencySymbol = @json(config('pos.currency'));</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="lg:w-64 lg:fixed lg:inset-y-0 bg-slate-900 text-slate-300 flex flex-col">
            <div class="px-5 py-5 border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    @php $logoUrl = \App\Models\Setting::logoUrl(); $companyName = \App\Models\Setting::companyName(); @endphp
                    @if ($logoUrl)
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/95 p-1 overflow-hidden">
                            <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain">
                        </span>
                    @else
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-slate-900 font-black text-xl">B</span>
                    @endif
                    <span>
                        <span class="block font-bold text-white leading-tight">{{ $companyName }}</span>
                        <span class="block text-xs text-slate-400">Management System</span>
                    </span>
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm font-medium">
                <x-nav-item href="{{ route('dashboard') }}" label="Dashboard" icon="dashboard" :active="request()->routeIs('dashboard')" />

                @if (auth()->user()->canAccessSales())
                    <x-nav-item href="{{ route('pos.index') }}" label="Sell (POS)" icon="pos" :active="request()->routeIs('pos.*')" />
                @endif

                @if (auth()->user()->canAccessBakery())
                    <x-nav-item href="{{ route('products.index') }}" label="Products" icon="products" :active="request()->routeIs('products.*')" />
                    <x-nav-item href="{{ route('productions.index') }}" label="Baking / Production" icon="production" :active="request()->routeIs('productions.*')" />
                    <x-nav-item href="{{ route('categories.index') }}" label="Categories" icon="categories" :active="request()->routeIs('categories.*')" />
                @endif

                @if (auth()->user()->canAccessKitchen())
                    <x-nav-item href="{{ route('ingredients.index') }}" label="Ingredients" icon="ingredients" :active="request()->routeIs('ingredients.*')" />
                    <x-nav-item href="{{ route('suppliers.index') }}" label="Suppliers" icon="suppliers" :active="request()->routeIs('suppliers.*')" />
                @endif

                @if (auth()->user()->canAccessSales())
                    <x-nav-item href="{{ route('orders.index') }}" label="Orders" icon="orders" :active="request()->routeIs('orders.*')" />
                    <x-nav-item href="{{ route('customers.index') }}" label="Customers" icon="customers" :active="request()->routeIs('customers.*')" />
                @endif

                @if (auth()->user()->isAdmin())
                    <x-nav-item href="{{ route('reports.index') }}" label="Reports" icon="reports" :active="request()->routeIs('reports.*')" />
                    <x-nav-item href="{{ route('expenses.index') }}" label="Expenses" icon="expenses" :active="request()->routeIs('expenses.*')" />
                    <x-nav-item href="{{ route('users.index') }}" label="Users" icon="user" :active="request()->routeIs('users.*')" />
                    <x-nav-item href="{{ route('settings.index') }}" label="Settings" icon="settings" :active="request()->routeIs('settings.*')" />
                @endif
            </nav>

            <div class="px-3 py-4 border-t border-slate-800 space-y-1 text-sm font-medium">
                <x-nav-item href="{{ route('profile.edit') }}" label="{{ auth()->user()->name }}" icon="user" :active="request()->routeIs('profile.*')" />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                        <x-svg-icon icon="logout" />
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 lg:ml-64">
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                @if (session('success'))
                    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h1 class="text-2xl font-bold text-slate-900 mb-6">@yield('title', 'Dashboard')</h1>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
