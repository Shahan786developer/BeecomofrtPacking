@extends('layouts.app')

@section('title', 'Warehouse Stock Levels')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Stock Levels</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Stock & Inventory Levels</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Real-time inventory quantities, backorder tracking, and physical audit adjustments.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('stock.movements') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Movement Ledger
            </a>
            <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Intake New Stock
            </a>
        </div>
    </div>

    <!-- 5 Summary Filter Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <a href="{{ route('stock.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors {{ !request('status') ? 'ring-2 ring-slate-900' : '' }}">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">All Products</span>
            <h4 class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_products']) }}</h4>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Catalog items</span>
        </a>

        <a href="{{ route('stock.index', ['status' => 'in_stock']) }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors {{ request('status') === 'in_stock' ? 'ring-2 ring-emerald-500' : '' }}">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Positive Stock</span>
            <h4 class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['total_stock']) }}</h4>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Available units</span>
        </a>

        <a href="{{ route('stock.index', ['status' => 'zero']) }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors {{ request('status') === 'zero' ? 'ring-2 ring-amber-500' : '' }}">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Zero Stock</span>
            <h4 class="text-xl font-black text-amber-600 mt-1">{{ number_format($stats['zero_stock']) }}</h4>
            <span class="text-[10px] text-slate-400 mt-0.5 block">0 units</span>
        </a>

        <a href="{{ route('stock.index', ['status' => 'negative']) }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors {{ request('status') === 'negative' ? 'ring-2 ring-rose-500' : '' }}">
            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Negative Stock</span>
            <h4 class="text-xl font-black text-rose-600 mt-1">{{ number_format($stats['negative_stock']) }}</h4>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Backorders</span>
        </a>

        <a href="{{ route('stock.index', ['status' => 'depleted_month']) }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors {{ request('status') === 'depleted_month' ? 'ring-2 ring-amber-500' : '' }}">
            <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider">Depleted 30d+</span>
            <h4 class="text-xl font-black text-amber-600 mt-1">{{ number_format($stats['depleted_month']) }}</h4>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Alert status</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('stock.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search Products</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, SKU, or Code..."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Category</label>
                <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors">
                    Filter Stock
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'status']))
                    <a href="{{ route('stock.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Stock Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="p-4 pl-6">Product Title</th>
                        <th class="p-4">SKU</th>
                        <th class="p-4 text-center">Current Stock</th>
                        <th class="p-4">Depletion Status</th>
                        <th class="p-4 text-right">Selling Price</th>
                        <th class="p-4 pr-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="p-4 pl-6">
                            <a href="{{ route('products.show', $product) }}" class="font-bold text-sm text-slate-900 hover:text-amber-600 block">
                                {{ $product->title }}
                            </a>
                            <span class="text-[11px] text-slate-400">Bill: {{ $product->bill_no }} • Code: {{ $product->product_code }}</span>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-700">
                            <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">{{ $product->sku_code }}</span>
                        </td>
                        <td class="p-4 text-center">
                            @if($product->stock_quantity > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $product->stock_quantity }} units
                                </span>
                            @elseif($product->stock_quantity === 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    0 units (Zero)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $product->stock_quantity }} units (Negative)
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($product->stock_quantity <= 0 && $product->zero_stock_date)
                                <div>
                                    <span class="font-semibold text-slate-700">Depleted: {{ $product->zero_stock_date->format('d M Y') }}</span>
                                    <span class="block text-[11px] text-slate-400">{{ $product->depleted_days }} days elapsed</span>
                                </div>
                            @else
                                <span class="text-emerald-700 font-semibold">In Stock</span>
                            @endif
                        </td>
                        <td class="p-4 text-right font-black text-slate-900">${{ number_format($product->selling_price, 2) }}</td>
                        <td class="p-4 pr-6 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" onclick="openAdjustModal({{ $product->id }}, '{{ addslashes($product->title) }}', {{ $product->stock_quantity }})"
                                    class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center gap-1">
                                    <i class="fa-solid fa-sliders text-slate-400"></i> Adjust
                                </button>
                                <a href="{{ route('products.movements', $product) }}" class="p-1 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-800" title="Movements">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No products found.</td>
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

<!-- ADJUST MODAL -->
<div id="adjust-stock-modal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Adjust Stock Count</h3>
            <button type="button" onclick="closeAdjustModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" id="adjust-stock-form" action="" class="space-y-4">
            @csrf
            <div>
                <span class="block text-xs font-bold text-slate-500 uppercase">Product</span>
                <h4 id="adjust-modal-title" class="text-sm font-black text-slate-900 mt-0.5"></h4>
            </div>

            <div>
                <span class="block text-xs font-bold text-slate-500 uppercase">Current Stock</span>
                <span id="adjust-modal-current" class="text-base font-bold text-slate-800"></span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Adjustment Action <span class="text-rose-500">*</span></label>
                <select name="adjustment_type" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-semibold">
                    <option value="add">Add Units to Inventory (+)</option>
                    <option value="subtract">Subtract Units from Inventory (-)</option>
                    <option value="set">Set Exact Count (Physical Audit)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Quantity <span class="text-rose-500">*</span></label>
                <input type="number" name="quantity" required min="0" placeholder="Number of units"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Adjustment Reason <span class="text-rose-500">*</span></label>
                <input type="text" name="notes" required placeholder="e.g. Audit reconciliation, damaged packaging"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAdjustModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-600">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 font-bold text-xs text-slate-950">Save Adjustment</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAdjustModal(productId, productTitle, currentStock) {
        document.getElementById('adjust-modal-title').textContent = productTitle;
        document.getElementById('adjust-modal-current').textContent = currentStock + ' units';
        document.getElementById('adjust-stock-form').action = `/stock/${productId}/adjust`;
        document.getElementById('adjust-stock-modal').classList.remove('hidden');
    }

    function closeAdjustModal() {
        document.getElementById('adjust-stock-modal').classList.add('hidden');
    }
</script>
@endpush
