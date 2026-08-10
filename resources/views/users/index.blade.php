@extends('layouts.app')

@section('title', 'Users & Permissions')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3 text-right">Orders</th>
                        <th class="px-5 py-3 text-right">Permissions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->isAdmin() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">{{ $user->departmentLabel() ?: 'All areas' }}</td>
                            <td class="px-5 py-3 text-right">{{ $user->orders_count }}</td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('users.update', $user) }}" class="flex items-center gap-2 justify-end">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm focus:border-amber-500">
                                        <option value="staff" @selected(! $user->isAdmin())>Staff</option>
                                        <option value="admin" @selected($user->isAdmin())>Admin</option>
                                    </select>
                                    <select name="department" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm focus:border-amber-500" {{ $user->isAdmin() ? 'disabled' : '' }}>
                                        @if ($user->isAdmin())
                                            <option value="" selected>All areas</option>
                                        @else
                                            @foreach (\App\Models\User::DEPARTMENTS as $key => $label)
                                                <option value="{{ $key }}" @selected($user->department === $key)>{{ $label }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-900 mb-2">How permissions work</h2>
        <ul class="text-sm text-slate-600 space-y-1 list-disc list-inside">
            <li><strong>Admin</strong> - access to every module including Reports and this Users page.</li>
            <li><strong>Kitchen staff</strong> - Ingredients, Suppliers and stock receiving only.</li>
            <li><strong>Bakery staff</strong> - Products, Recipes and Baking/Production only.</li>
            <li><strong>Everyone</strong> - Dashboard, Sell (POS), Orders, Categories and Profile.</li>
        </ul>
    </div>
@endsection
