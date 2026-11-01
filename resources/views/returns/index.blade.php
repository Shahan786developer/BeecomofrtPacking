@extends('layouts.app')

@section('title', 'Bill Returns')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Bill Returns</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Customer Bill Returns</h1>
            <p class="text-sm text-slate-500 mt-1">Process invoice merchandise returns, validate quantities against sales, and restore stock.</p>
        </div>
        <a href="{{ route('returns.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-sm shadow-md shadow-amber-400/20 transition-all flex items-center gap-2">
            <i class="fa-solid fa-rotate-left"></i> Process New Return
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('returns.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Search Return / Invoice</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Return #, Invoice #, or customer..."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                    Filter Returns
                </button>
                @if(request()->anyFilled(['search', 'from_date', 'to_date']))
                    <a href="{{ route('returns.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-4 pl-6">Return No</th>
                        <th class="p-4">Original Invoice</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Return Date</th>
                        <th class="p-4 text-center">Items Returned</th>
                        <th class="p-4 text-right">Return Amount</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $ret)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-4 pl-6">
                            <a href="{{ route('returns.show', $ret) }}" class="font-extrabold text-sm text-orange-600 hover:text-orange-800">
                                {{ $ret->return_no }}
                            </a>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-700">
                            <a href="{{ route('sales.show', $ret->sale) }}" class="text-indigo-600 hover:underline">
                                {{ $ret->sale->invoice_no }}
                            </a>
                        </td>
                        <td class="p-4 font-bold text-slate-900">{{ $ret->customer->name }}</td>
                        <td class="p-4 text-slate-600 font-medium">{{ $ret->return_date->format('d M Y') }}</td>
                        <td class="p-4 text-center font-bold text-slate-700">
                            {{ $ret->details->sum('quantity') }} unit(s)
                        </td>
                        <td class="p-4 text-right font-black text-sm text-orange-600">${{ number_format($ret->total_amount, 2) }}</td>
                        <td class="p-4 pr-6 text-right">
                            <a href="{{ route('returns.show', $ret) }}" class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs inline-flex items-center gap-1">
                                <i class="fa-solid fa-eye text-slate-400"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-slate-400">
                            <i class="fa-solid fa-rotate-left text-3xl mb-2 text-slate-300"></i>
                            <p class="font-medium">No customer bill returns recorded yet.</p>
                            <a href="{{ route('returns.create') }}" class="inline-block mt-3 px-3.5 py-1.5 rounded-lg bg-amber-400 text-slate-950 font-bold text-xs">
                                Process First Return
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $returns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
