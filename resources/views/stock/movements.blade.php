@extends('layouts.app')

@section('title', 'Global Stock Movements Ledger')

@section('breadcrumb')
    <a href="{{ route('stock.index') }}" class="hover:text-amber-600">Stock Management</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">Movements Ledger</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Stock Movements Ledger</h1>
            <p class="text-sm text-slate-500 mt-1">Audit log tracking all additions, deductions, sales, and return stock transactions.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('stock.movements') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Search Product / SKU</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, SKU, Code..."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Movement Type</label>
                <select name="type" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
                    <option value="">All Types</option>
                    <option value="initial" {{ request('type') === 'initial' ? 'selected' : '' }}>Initial Stock Registration</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sales Invoice Deduction</option>
                    <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Bill Return Restoration</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                    Filter Ledger
                </button>
                @if(request()->anyFilled(['search', 'type']))
                    <a href="{{ route('stock.movements') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-4 pl-6">Timestamp</th>
                        <th class="p-4">Product Title (Unified Title)</th>
                        <th class="p-4">SKU</th>
                        <th class="p-4">Type</th>
                        <th class="p-4 text-center">Change</th>
                        <th class="p-4 text-center">Previous Stock</th>
                        <th class="p-4 text-center">New Stock</th>
                        <th class="p-4 pr-6">Transaction Reference / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-4 pl-6 font-mono text-slate-500 whitespace-nowrap">
                            {{ $m->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="p-4 font-extrabold text-slate-900">
                            <a href="{{ route('products.show', $m->product) }}" class="hover:text-amber-600">
                                {{ $m->product->title }}
                            </a>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-700">
                            {{ $m->product->sku_code }}
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                {{ $m->type === 'sale' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($m->type === 'return' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                {{ $m->type }}
                            </span>
                        </td>
                        <td class="p-4 text-center font-black text-sm {{ $m->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                        </td>
                        <td class="p-4 text-center text-slate-600 font-medium">{{ $m->previous_stock }}</td>
                        <td class="p-4 text-center font-black text-slate-900">{{ $m->new_stock }}</td>
                        <td class="p-4 pr-6 text-slate-600">
                            {{ $m->notes ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">No stock movements found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
