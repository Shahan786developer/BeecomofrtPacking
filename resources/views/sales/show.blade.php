@extends('layouts.app')

@section('title', 'Invoice #' . $sale->invoice_no)

@section('breadcrumb')
    <a href="{{ route('sales.index') }}" class="hover:text-amber-600">Sales Invoices</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">{{ $sale->invoice_no }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Screen Toolbar -->
    <div class="no-print flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sales Invoice Details</h1>
            <p class="text-sm text-slate-500 mt-0.5">Official customer bill and stock delivery receipt.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" class="px-3.5 py-2 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 border border-orange-200 font-bold text-xs shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-rotate-left"></i> Bill Return
            </a>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- PRINTABLE INVOICE PAPER CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 sm:p-10 text-slate-800" id="printable-invoice">

        <!-- Invoice Brand & Number Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between border-b-2 border-slate-900 pb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-400 flex items-center justify-center font-black text-slate-950 text-lg">
                        <i class="fa-solid fa-cube"></i>
                    </div>
                    <div>
                        <span class="text-xl font-black text-slate-950 tracking-tight">BEE COMFORT</span>
                        <span class="text-xs text-slate-500 block -mt-0.5 font-medium">Packing & Distribution</span>
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-3 space-y-0.5">
                    <p>Bee Comfort Garments & Packing Line</p>
                    <p>Phone: +92 300 0000000 • Email: billing@beecomfort.local</p>
                </div>
            </div>

            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 uppercase tracking-widest">
                    Sales Invoice
                </span>
                <h2 class="text-2xl font-mono font-black text-slate-950 mt-2">{{ $sale->invoice_no }}</h2>
                <p class="text-xs text-slate-500 mt-1">Invoice Date: <strong class="text-slate-800">{{ $sale->invoice_date->format('d M Y') }}</strong></p>
            </div>
        </div>

        <!-- Customer & Delivery Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-100 text-xs">
            <div>
                <span class="font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Billed To (Customer):</span>
                <h3 class="text-base font-extrabold text-slate-900">{{ $sale->customer->name }}</h3>
                @if($sale->customer->phone)
                    <p class="text-slate-600 mt-0.5"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $sale->customer->phone }}</p>
                @endif
                @if($sale->customer->email)
                    <p class="text-slate-600"><i class="fa-solid fa-envelope text-slate-400 mr-1"></i> {{ $sale->customer->email }}</p>
                @endif
                @if($sale->customer->address)
                    <p class="text-slate-600 mt-1">{{ $sale->customer->address }}</p>
                @endif
            </div>

            <div class="sm:text-right space-y-1">
                <span class="font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Invoice Reference:</span>
                <p class="text-slate-600">Created: <strong class="text-slate-800">{{ $sale->created_at->format('d M Y, H:i') }}</strong></p>
                <p class="text-slate-600">Stock Status: <span class="text-emerald-700 font-bold">Deducted from Inventory</span></p>
            </div>
        </div>

        <!-- Items Table (Requirement #4: Unified Product Title) -->
        <div class="py-6">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-slate-600 font-bold uppercase text-[11px]">
                        <th class="py-3 w-10 text-center">#</th>
                        <th class="py-3">Product Description (Unified Title)</th>
                        <th class="py-3">SKU</th>
                        <th class="py-3 text-right">Selling Rate</th>
                        <th class="py-3 text-center">Qty Sold</th>
                        <th class="py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->details as $idx => $detail)
                    <tr>
                        <td class="py-3.5 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                        <!-- Unified Product Title (Requirement #1 & #4) -->
                        <td class="py-3.5">
                            <span class="font-extrabold text-slate-900 text-sm block">
                                {{ $detail->product_title }}
                            </span>
                        </td>
                        <td class="py-3.5 font-mono font-bold text-slate-700">
                            {{ $detail->sku_code }}
                        </td>
                        <td class="py-3.5 text-right font-bold text-slate-800">${{ number_format($detail->unit_price, 2) }}</td>
                        <td class="py-3.5 text-center font-black text-slate-900">{{ $detail->quantity }}</td>
                        <td class="py-3.5 text-right font-extrabold text-slate-950">${{ number_format($detail->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Notes -->
        <div class="pt-4 border-t-2 border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
            <div class="text-xs text-slate-500 space-y-1">
                @if($sale->notes)
                    <p><strong class="text-slate-700">Remarks:</strong> {{ $sale->notes }}</p>
                @endif
                <p class="pt-2 text-[11px] text-slate-400">All prices computed per configured Bee Comfort multiple-of-10 ceiling markup rules.</p>
            </div>

            <div class="space-y-2 text-xs text-right">
                <div class="flex justify-between sm:justify-end gap-6 text-slate-600">
                    <span>Subtotal:</span>
                    <strong class="text-slate-900 font-mono">${{ number_format($sale->subtotal, 2) }}</strong>
                </div>

                @if($sale->discount > 0)
                <div class="flex justify-between sm:justify-end gap-6 text-slate-600">
                    <span>Discount:</span>
                    <strong class="text-rose-600 font-mono">-${{ number_format($sale->discount, 2) }}</strong>
                </div>
                @endif

                <div class="pt-2 border-t border-slate-200 flex justify-between sm:justify-end gap-6 text-base font-black text-slate-950">
                    <span>Grand Total:</span>
                    <span class="text-xl font-black text-slate-950 font-mono">${{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
