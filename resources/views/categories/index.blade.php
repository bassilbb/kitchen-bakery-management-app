@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Add Category</h2>
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Category name" required
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                <input type="text" name="description" placeholder="Description (optional)"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">Add Category</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-3">Categories</h2>
            <div class="space-y-2">
                @forelse ($categories as $category)
                    <div class="flex items-center justify-between border border-slate-100 rounded-lg px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $category->name }}</p>
                            <p class="text-xs text-slate-500">{{ $category->products_count }} products</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}')"
                                    class="text-amber-600 hover:text-amber-500 text-sm font-medium">Edit</button>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                  onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-500 text-sm font-medium">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No categories yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        function editCategory(id, name, description) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/categories/' + id;
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="_method" value="PUT">' +
                '<div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" onclick="this.parentElement.remove()">' +
                '<div class="bg-white rounded-xl p-6 w-full max-w-md space-y-3" onclick="event.stopPropagation()">' +
                '<h3 class="font-semibold text-slate-900">Edit category</h3>' +
                '<input type="text" name="name" value="' + name + '" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">' +
                '<input type="text" name="description" value="' + description + '" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500">' +
                '<div class="flex gap-2 justify-end">' +
                '<button type="button" onclick="this.closest(\'.fixed\').remove()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Cancel</button>' +
                '<button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900">Save</button>' +
                '</div></div></div>';
            document.body.appendChild(form);
        }
    </script>
@endsection
