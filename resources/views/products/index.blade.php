@extends('layouts.app')

@section('title', 'Product Catalog')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Products</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Product Catalog</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Manage registered merchandise, automatic SKUs, selling prices, and stock.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('barcode.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-barcode text-slate-400"></i> Barcode Studio
            </a>
            <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Register Product
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search Products</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, SKU, Bill, or Code..." 
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Category</label>
                <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Stock Status</label>
                <select name="stock_status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (> 0)</option>
                    <option value="zero" {{ request('stock_status') === 'zero' ? 'selected' : '' }}>Zero Stock (= 0)</option>
                    <option value="negative" {{ request('stock_status') === 'negative' ? 'selected' : '' }}>Negative Stock (< 0)</option>
                    <option value="depleted_month" {{ request('stock_status') === 'depleted_month' ? 'selected' : '' }}>Depleted 30+ Days</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'stock_status']))
                    <a href="{{ route('products.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="p-4 pl-6">Product Title</th>
                        <th class="p-4">SKU</th>
                        <th class="p-4">Bill No</th>
                        <th class="p-4 text-right">Actual Price</th>
                        <th class="p-4">Pricing Rule</th>
                        <th class="p-4 text-right">Selling Price</th>
                        <th class="p-4 text-center">Available Stock</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <!-- Dynamic Unified Title (Requirement #1) -->
                        <td class="p-4 pl-6">
                            <a href="{{ route('products.show', $product) }}" class="font-bold text-sm text-slate-900 hover:text-amber-600 block">
                                {{ $product->title }}
                            </a>
                            <span class="text-[11px] text-slate-400 mt-0.5 block">
                                Code: {{ $product->product_code }} • {{ $product->dozen->label }}
                            </span>
                        </td>

                        <!-- 7-Digit Numeric SKU -->
                        <td class="p-4">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $product->sku_code }}
                            </span>
                        </td>

                        <!-- Bill No -->
                        <td class="p-4 font-mono text-slate-600">
                            {{ $product->bill_no }}
                        </td>

                        <!-- Actual Price -->
                        <td class="p-4 text-right font-medium text-slate-700">
                            ${{ number_format($product->actual_price, 2) }}
                        </td>

                        <!-- Pricing Rule -->
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                {{ $product->pricingRule->name }}
                            </span>
                        </td>

                        <!-- Selling Price -->
                        <td class="p-4 text-right">
                            <span class="font-black text-sm text-slate-900">
                                ${{ number_format($product->selling_price, 2) }}
                            </span>
                        </td>

                        <!-- Stock Quantity -->
                        <td class="p-4 text-center">
                            @if($product->stock_quantity > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $product->stock_quantity }} units
                                </span>
                            @elseif($product->stock_quantity == 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    0 (Zero)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $product->stock_quantity }} (Negative)
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="p-4 pr-6 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('barcode.index', ['product_id' => $product->id]) }}" class="p-1.5 rounded-lg border border-slate-200 hover:bg-amber-50 hover:text-amber-700 text-slate-500" title="Print Barcode">
                                    <i class="fa-solid fa-barcode"></i>
                                </a>
                                <a href="{{ route('products.movements', $product) }}" class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-500" title="Movements">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-500" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            No products found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
