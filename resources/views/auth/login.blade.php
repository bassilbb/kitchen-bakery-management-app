@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <h2 class="text-2xl font-bold text-slate-900 text-center">Sign in</h2>
    <p class="text-sm text-slate-500 text-center mt-1">Welcome back to Kitchen &amp; Bakery Manager</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                <span class="ml-2">Remember me</span>
            </label>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400">
            Sign in
        </button>

        <p class="text-sm text-center text-slate-500">
            No account yet?
            <a href="{{ route('register') }}" class="font-medium text-amber-600 hover:text-amber-500">Create one</a>
        </p>
    </form>
@endsection
