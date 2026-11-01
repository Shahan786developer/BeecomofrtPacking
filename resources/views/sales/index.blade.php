@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Sales Invoices</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sales / Customer Invoices</h1>
            <p class="text-sm text-slate-500 mt-1">Generate invoices with instant barcode SKU scanning and automatic stock deduction.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-sm shadow-md shadow-amber-400/20 transition-all flex items-center gap-2">
            <i class="fa-solid fa-cart-plus"></i> New Sales Invoice
        </a>
    </div>

    <!-- Filters Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Search Invoice / Customer</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or customer name..."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                    Filter Invoices
                </button>
                @if(request()->anyFilled(['search', 'from_date', 'to_date']))
                    <a href="{{ route('sales.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-4 pl-6">Invoice No</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Date</th>
                        <th class="p-4 text-center">Items Sold</th>
                        <th class="p-4 text-right">Subtotal</th>
                        <th class="p-4 text-right">Discount</th>
                        <th class="p-4 text-right">Total Amount</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-4 pl-6">
                            <a href="{{ route('sales.show', $sale) }}" class="font-extrabold text-sm text-indigo-600 hover:text-indigo-800">
                                {{ $sale->invoice_no }}
                            </a>
                        </td>
                        <td class="p-4 font-bold text-slate-900">
                            {{ $sale->customer->name }}
                            @if($sale->customer->phone)
                                <span class="block text-[11px] text-slate-400 font-normal">{{ $sale->customer->phone }}</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-600 font-medium">{{ $sale->invoice_date->format('d M Y') }}</td>
                        <td class="p-4 text-center font-bold text-slate-700">
                            {{ $sale->details->count() }} item(s) ({{ $sale->details->sum('quantity') }} pcs)
                        </td>
                        <td class="p-4 text-right font-medium text-slate-700">${{ number_format($sale->subtotal, 2) }}</td>
                        <td class="p-4 text-right text-slate-500">${{ number_format($sale->discount, 2) }}</td>
                        <td class="p-4 text-right font-black text-sm text-slate-900">${{ number_format($sale->total_amount, 2) }}</td>
                        <td class="p-4 pr-6 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('sales.show', $sale) }}" class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs flex items-center gap-1">
                                    <i class="fa-solid fa-eye text-slate-400"></i> View
                                </a>
                                <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" class="px-2.5 py-1 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 text-orange-800 font-bold text-xs flex items-center gap-1" title="Process Bill Return">
                                    <i class="fa-solid fa-rotate-left"></i> Return
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                            <p class="font-medium">No sales invoices recorded yet.</p>
                            <a href="{{ route('sales.create') }}" class="inline-block mt-3 px-3.5 py-1.5 rounded-lg bg-amber-400 text-slate-950 font-bold text-xs">
                                Create First Sales Invoice
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
