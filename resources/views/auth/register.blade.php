@extends('layouts.auth')

@section('title', 'Create account')

@section('content')
    <h2 class="text-2xl font-bold text-slate-900 text-center">Create account</h2>
    <p class="text-sm text-slate-500 text-center mt-1">Set up your Kitchen &amp; Bakery Manager account</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div>
            <label for="department" class="block text-sm font-medium text-slate-700">Department</label>
            <select id="department" name="department"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="kitchen" @selected(old('department', 'kitchen') === 'kitchen')>Kitchen</option>
                <option value="bakery" @selected(old('department') === 'bakery')>Bakery</option>
            </select>
            <p class="mt-1 text-xs text-slate-400">Staff are limited to their department's features. The first account created becomes the admin.</p>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400">
            Create account
        </button>

        <p class="text-sm text-center text-slate-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-amber-600 hover:text-amber-500">Sign in</a>
        </p>
    </form>
@endsection
