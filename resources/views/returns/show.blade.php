@extends('layouts.app')

@section('title', 'Return Receipt #' . $return->return_no)

@section('breadcrumb')
    <a href="{{ route('returns.index') }}" class="hover:text-amber-600">Bill Returns</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">{{ $return->return_no }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Screen Toolbar -->
    <div class="no-print flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Merchandise Return Receipt</h1>
            <p class="text-sm text-slate-500 mt-0.5">Customer return confirmation and stock restoration voucher.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sales.show', $return->sale) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5">
                <i class="fa-solid fa-file-invoice"></i> Original Invoice
            </a>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- PRINTABLE RETURN RECEIPT CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 sm:p-10 text-slate-800" id="printable-return">

        <!-- Return Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between border-b-2 border-orange-500 pb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center font-black text-white text-lg">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <span class="text-xl font-black text-slate-950 tracking-tight">BEE COMFORT</span>
                        <span class="text-xs text-slate-500 block -mt-0.5 font-medium">Customer Merchandise Return</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-3 space-y-0.5">
                    <p>Warehouse Intake & Packing Quality Control</p>
                    <p>Phone: +92 300 0000000 • Email: returns@beecomfort.local</p>
                </div>
            </div>

            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-black bg-orange-100 text-orange-900 border border-orange-300 uppercase tracking-widest">
                    Return Receipt
                </span>
                <h2 class="text-2xl font-mono font-black text-slate-950 mt-2">{{ $return->return_no }}</h2>
                <p class="text-xs text-slate-500 mt-1">Return Date: <strong class="text-slate-800">{{ $return->return_date->format('d M Y') }}</strong></p>
            </div>
        </div>

        <!-- Reference & Customer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-100 text-xs">
            <div>
                <span class="font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Customer Details:</span>
                <h3 class="text-base font-extrabold text-slate-900">{{ $return->customer->name }}</h3>
                @if($return->customer->phone)
                    <p class="text-slate-600 mt-0.5"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $return->customer->phone }}</p>
                @endif
                @if($return->customer->address)
                    <p class="text-slate-600 mt-1">{{ $return->customer->address }}</p>
                @endif
            </div>

            <div class="sm:text-right space-y-1">
                <span class="font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Original Invoice:</span>
                <p class="text-slate-800 font-bold font-mono text-sm">
                    <a href="{{ route('sales.show', $return->sale) }}" class="text-indigo-600 hover:underline">
                        {{ $return->sale->invoice_no }}
                    </a>
                </p>
                <p class="text-slate-500">Invoice Date: {{ $return->sale->invoice_date->format('d M Y') }}</p>
                <p class="text-emerald-700 font-bold pt-1">
                    <i class="fa-solid fa-check-double mr-1"></i> Stock Restored into Warehouse
                </p>
            </div>
        </div>

        <!-- Returned Items (Unified Product Title) -->
        <div class="py-6">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-slate-600 font-bold uppercase text-[11px]">
                        <th class="py-3 w-10 text-center">#</th>
                        <th class="py-3">Returned Product (Unified Title)</th>
                        <th class="py-3">SKU</th>
                        <th class="py-3 text-right">Selling Rate</th>
                        <th class="py-3 text-center">Qty Returned</th>
                        <th class="py-3 text-right">Refund Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($return->details as $idx => $item)
                    <tr>
                        <td class="py-3.5 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                        <!-- Unified Product Title (Requirement #1 & #5) -->
                        <td class="py-3.5 font-extrabold text-slate-900 text-sm">
                            {{ $item->product_title }}
                        </td>
                        <td class="py-3.5 font-mono font-bold text-slate-700">
                            {{ $item->sku_code }}
                        </td>
                        <td class="py-3.5 text-right font-bold text-slate-800">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3.5 text-center font-black text-orange-600 text-sm">+{{ $item->quantity }}</td>
                        <td class="py-3.5 text-right font-black text-slate-950">${{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Reason -->
        <div class="pt-4 border-t-2 border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
            <div class="text-xs text-slate-500 space-y-1">
                @if($return->reason)
                    <p><strong class="text-slate-700">Return Reason:</strong> {{ $return->reason }}</p>
                @endif
                <p class="pt-1 text-[11px] text-slate-400">Inventory was automatically augmented by the returned quantities.</p>
            </div>

            <div class="space-y-2 text-xs text-right">
                <div class="flex justify-between sm:justify-end gap-6 text-slate-600">
                    <span>Total Quantity Returned:</span>
                    <strong class="text-slate-900 font-mono">{{ $return->details->sum('quantity') }} unit(s)</strong>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-between sm:justify-end gap-6 text-base font-black text-slate-950">
                    <span>Total Refund Credited:</span>
                    <span class="text-xl font-black text-orange-600 font-mono">${{ number_format($return->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
