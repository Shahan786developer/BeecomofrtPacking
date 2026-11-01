@extends('layouts.app')

@section('title', $product->title)

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-amber-600">Products</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">{{ $product->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                    SKU: {{ $product->sku_code }}
                </span>
                <span class="text-xs text-slate-500 font-medium">Bill No: {{ $product->bill_no }}</span>
            </div>
            <!-- Dynamic Combined Product Title (Requirement #1) -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $product->title }}</h1>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('barcode.index', ['product_id' => $product->id]) }}" class="px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-barcode"></i> Print Barcode Labels
            </a>
            <a href="{{ route('products.edit', $product) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
        </div>
    </div>

    <!-- Product Details Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Stock Status Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Stock</p>
            <h3 class="text-3xl font-black mt-1 {{ $product->stock_quantity > 0 ? 'text-emerald-600' : ($product->stock_quantity == 0 ? 'text-amber-600' : 'text-rose-600') }}">
                {{ $product->stock_quantity }}
            </h3>
            <div class="mt-2 text-xs">
                @if($product->stock_quantity <= 0 && $product->zero_stock_date)
                    <span class="text-rose-600 font-semibold">
                        Depleted since {{ $product->zero_stock_date->format('d M Y') }} ({{ $product->depleted_days }} days ago)
                    </span>
                @else
                    <span class="text-emerald-700 font-semibold">Available for sale</span>
                @endif
            </div>
        </div>

        <!-- Selling Price Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Calculated Selling Price</p>
            <h3 class="text-3xl font-black text-slate-900 mt-1">${{ number_format($product->selling_price, 2) }}</h3>
            <p class="text-xs text-slate-500 mt-2">Saved fixed price for invoice scanning</p>
        </div>

        <!-- Actual Price Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Actual Purchase Price</p>
            <h3 class="text-3xl font-black text-slate-700 mt-1">${{ number_format($product->actual_price, 2) }}</h3>
            <p class="text-xs text-slate-500 mt-2">Margin: ${{ number_format($product->selling_price - $product->actual_price, 2) }}</p>
        </div>

        <!-- Pricing Rule Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pricing Configuration</p>
            <h3 class="text-lg font-bold text-amber-900 mt-1">{{ $product->pricingRule->name }}</h3>
            <p class="text-xs text-slate-500 mt-2">+{{ number_format($product->pricingRule->percentage, 2) }}% markup rule</p>
        </div>
    </div>

    <!-- Stock Movement History Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-base text-slate-900">Recent Stock Movements</h3>
                <p class="text-xs text-slate-500">Audit trail of sales, returns, and inventory additions</p>
            </div>
            <a href="{{ route('products.movements', $product) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                View Full Movement Ledger &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="p-3 pl-6">Date & Time</th>
                        <th class="p-3">Type</th>
                        <th class="p-3 text-center">Change</th>
                        <th class="p-3 text-center">Previous Stock</th>
                        <th class="p-3 text-center">New Stock</th>
                        <th class="p-3 pr-6">Notes / Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->stockMovements as $move)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 pl-6 text-slate-500">{{ $move->created_at->format('d M Y, H:i') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $move->type === 'sale' ? 'bg-indigo-50 text-indigo-700' : ($move->type === 'return' ? 'bg-orange-50 text-orange-700' : 'bg-emerald-50 text-emerald-700') }}">
                                {{ $move->type }}
                            </span>
                        </td>
                        <td class="p-3 text-center font-extrabold {{ $move->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $move->quantity_change > 0 ? '+' : '' }}{{ $move->quantity_change }}
                        </td>
                        <td class="p-3 text-center text-slate-600">{{ $move->previous_stock }}</td>
                        <td class="p-3 text-center font-bold text-slate-900">{{ $move->new_stock }}</td>
                        <td class="p-3 pr-6 text-slate-600">{{ $move->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">No stock movements recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
