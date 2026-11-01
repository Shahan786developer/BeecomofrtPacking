@extends('layouts.app')

@section('title', 'Product Categories')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Categories</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Product Categories</h1>
            <p class="text-sm text-slate-500 mt-1">Manage master product lines (first part of unified product titles).</p>
        </div>
        <a href="{{ route('products.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Register Product with Category
        </a>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Add Category Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-amber-500"></i> New Category
            </h3>
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Category Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="e.g. Silk Touch, Active Sport"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-semibold">
                    <p class="text-[11px] text-slate-400 mt-0.5">Appears as prefix in combined Product Title</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Description (Optional)
                    </label>
                    <textarea name="description" rows="2" placeholder="Fabric, material, or line notes"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-sm transition-colors">
                    Save Category
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 sm:px-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Registered Categories</h3>
                    <p class="text-xs text-slate-500">Searchable foreign-key categories</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                    {{ $categories->total() }} Categories
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-4 pl-6">Category Name</th>
                            <th class="p-4">Description</th>
                            <th class="p-4 text-center">Assigned Products</th>
                            <th class="p-4 pr-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4 pl-6 font-extrabold text-slate-900 text-sm">
                                {{ $cat->name }}
                            </td>
                            <td class="p-4 text-slate-500">
                                {{ $cat->description ?? '—' }}
                            </td>
                            <td class="p-4 text-center font-bold text-indigo-700 font-mono">
                                {{ $cat->products_count }} products
                            </td>
                            <td class="p-4 pr-6 text-right">
                                @if($cat->products_count == 0)
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-1" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-slate-400 italic">In Use</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">No categories found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
