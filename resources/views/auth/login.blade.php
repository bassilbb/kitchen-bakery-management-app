@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <h2 class="text-2xl font-bold text-slate-900 text-center">Welcome back</h2>
    <p class="text-sm text-slate-500 text-center mt-1">Sign in to your account to continue</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com"
                       class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                       class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            </div>
        </div>

        <div class="flex items-center">
            <label class="flex items-center text-sm text-slate-600">
                <input type="checkbox" name="remember"
                       class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500 focus:ring-offset-0">
                <span class="ml-2">Remember me</span>
            </label>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
            Sign in
        </button>

        <p class="text-sm text-center text-slate-500">
            No account yet?
            <a href="{{ route('register') }}" class="font-medium text-amber-600 hover:text-amber-500">Create one</a>
        </p>
    </form>
@endsection
