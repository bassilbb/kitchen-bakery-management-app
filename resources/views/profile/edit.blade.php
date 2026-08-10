@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 max-w-4xl">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Profile</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role</label>
                        <input type="text" value="{{ ucfirst(auth()->user()->role) }}" disabled
                               class="mt-1 block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Department</label>
                        <input type="text" value="{{ auth()->user()->departmentLabel() ?: 'All (Admin)' }}" disabled
                               class="mt-1 block w-full rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-500">
                    </div>
                </div>
                <p class="text-xs text-slate-400">Roles and departments are managed by the admin on the Users page.</p>
                <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Update Profile</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Change Password</h2>
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700">Current password</label>
                    <input type="password" name="current_password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">New password</label>
                    <input type="password" name="password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Confirm new password</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <button type="submit" class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Update Password</button>
            </form>
        </div>
    </div>
@endsection
