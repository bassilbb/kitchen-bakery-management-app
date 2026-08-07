@extends('layouts.app')

@section('title', 'Edit '.$supplier->name)

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Company name</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Contact person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Address</label>
                    <input type="text" name="address" value="{{ old('address', $supplier->address) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Save Changes</button>
                <a href="{{ route('suppliers.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
