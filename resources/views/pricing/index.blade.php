@extends('layouts.app')

@section('title', 'Pricing Configurations')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Pricing Rules</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pricing Configurations</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Configure percentage tiers and category-based pricing used during product registration.</p>
        </div>
    </div>

    <!-- SECTION 1: PERCENTAGE PRICING RULES -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Percentage Pricing Tiers</h2>
                <p class="text-xs text-slate-500">Configured percentage markup options. The designated default appears automatically on product registration.</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                {{ $percentageRules->count() }} Tiers
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- New Percentage Form -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Add Percentage Rule</h3>
                <form method="POST" action="{{ route('pricing.percentage.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rule Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" placeholder="e.g. Retail 22%" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Markup Percentage (%) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" max="1000" name="percentage" placeholder="e.g. 22" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white font-bold">
                    </div>

                    <!-- Boolean Yes/No Option (Requirement #8) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Set as Default Rule?</label>
                        <div class="flex items-center gap-4 text-xs font-semibold text-slate-700 pt-1">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="is_default" value="1" class="text-amber-500 focus:ring-amber-400">
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="is_default" value="0" checked class="text-amber-500 focus:ring-amber-400">
                                <span>No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                        <input type="text" name="description" placeholder="Notes or remarks"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white">
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-xs transition-colors">
                        Add Percentage Rule
                    </button>
                </form>
            </div>

            <!-- Existing Percentage Records List -->
            <div class="lg:col-span-2 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px]">
                        <tr>
                            <th class="p-3">Rule Name</th>
                            <th class="p-3 text-center">Markup</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3">Products</th>
                            <th class="p-3 pr-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($percentageRules as $pRule)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-bold text-slate-900">
                                {{ $pRule->name }}
                                @if($pRule->description)
                                    <span class="block text-[11px] text-slate-400 font-normal">{{ $pRule->description }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-center font-black text-slate-900 text-sm">
                                +{{ number_format($pRule->percentage, 2) }}%
                            </td>
                            <td class="p-3 text-center">
                                @if($pRule->is_default)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-check text-[9px]"></i> Default Rule
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('pricing.default', $pRule) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-amber-600 hover:underline">
                                            Make Default
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="p-3 text-slate-600 font-mono">
                                {{ $pRule->products->count() }} items
                            </td>
                            <td class="p-3 pr-4 text-right">
                                @if(!$pRule->is_default)
                                <form method="POST" action="{{ route('pricing.destroy', $pRule) }}" class="inline" onsubmit="return confirm('Delete this pricing rule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-slate-400 italic">Default</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400">No percentage pricing rules found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- SECTION 2: CATEGORY PRICING RULES -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Category Pricing Configurations</h2>
                <p class="text-xs text-slate-500">Category-specific pricing rules linked through searchable foreign-key category selection.</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                {{ $categoryRules->count() }} Rules
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- New Category Pricing Form -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Add Category Rule</h3>
                <form method="POST" action="{{ route('pricing.category.store') }}" class="space-y-3.5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" required class="searchable-select w-full" placeholder="Select category...">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Markup Percentage (%) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" max="1000" name="percentage" placeholder="e.g. 40" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rule Name (Optional)</label>
                        <input type="text" name="name" placeholder="Leave empty for auto-name"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                        <input type="text" name="description" placeholder="Notes"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white">
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs shadow-xs transition-colors">
                        Add Category Rule
                    </button>
                </form>
            </div>

            <!-- Existing Category Rules Table -->
            <div class="lg:col-span-2 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px]">
                        <tr>
                            <th class="p-3">Category</th>
                            <th class="p-3">Rule Name</th>
                            <th class="p-3 text-center">Markup</th>
                            <th class="p-3">Products</th>
                            <th class="p-3 pr-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categoryRules as $cRule)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-bold text-slate-900">
                                {{ $cRule->category->name ?? 'Unassigned' }}
                            </td>
                            <td class="p-3 text-slate-700 font-medium">
                                {{ $cRule->name }}
                            </td>
                            <td class="p-3 text-center font-black text-slate-900 text-sm">
                                +{{ number_format($cRule->percentage, 2) }}%
                            </td>
                            <td class="p-3 text-slate-600 font-mono">
                                {{ $cRule->products->count() }} items
                            </td>
                            <td class="p-3 pr-4 text-right">
                                <form method="POST" action="{{ route('pricing.destroy', $cRule) }}" class="inline" onsubmit="return confirm('Delete this category rule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400">No category pricing rules configured yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection
