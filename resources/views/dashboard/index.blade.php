@extends('layouts.app')

@section('title', 'Operations Dashboard')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Dashboard</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Real-time inventory levels, customer billing, and stock depletion tracking.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('sales.create') }}" class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-cash-register"></i> New Sale
            </a>
            <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    <!-- ONE-MONTH ZERO/NEGATIVE STOCK ALERT BANNER (If any) -->
    @if($oneMonthDepletedProducts->count() > 0)
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-300/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-lg flex-shrink-0 shadow-xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">
                        Critical Attention: One-Month Depleted Stock Alert ({{ $oneMonthDepletedProducts->count() }} Products)
                    </h3>
                    <p class="text-xs text-slate-600 mt-0.5">
                        These items have remained depleted (≤ 0 stock) for 30+ days. Per inventory policy, products remain active.
                    </p>
                </div>
            </div>
            <a href="{{ route('stock.index', ['status' => 'depleted_month']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 transition-colors whitespace-nowrap self-start sm:self-auto">
                Review Depleted Stock &rarr;
            </a>
        </div>

        <!-- Compact Card List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-4">
            @foreach($oneMonthDepletedProducts as $depProduct)
            <div class="bg-white rounded-xl p-3.5 border border-amber-200/90 shadow-xs flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-xs text-slate-900">{{ $depProduct->title }}</h4>
                    <span class="font-mono text-[11px] text-slate-500 block mt-0.5">SKU: {{ $depProduct->sku_code }}</span>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[11px] font-black {{ $depProduct->stock_quantity < 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                        {{ $depProduct->stock_quantity }} units
                    </span>
                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $depProduct->depleted_days }} days depleted</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SUMMARY METRICS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. Total Products -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Products</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalProducts) }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <a href="{{ route('products.index') }}" class="font-bold text-amber-600 hover:text-amber-700">View Products &rarr;</a>
                <span>Catalog</span>
            </div>
        </div>

        <!-- 2. Total Stock Units -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Stock</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($totalStockQuantity) }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <a href="{{ route('stock.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700">Warehouse Levels &rarr;</a>
                <span>Units</span>
            </div>
        </div>

        <!-- 3. Zero / Negative Stock -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Out / Backorder</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">
                        {{ $zeroStockCount }} <span class="text-xs font-bold text-slate-400">zero</span> / 
                        <span class="text-rose-600">{{ $negativeStockCount }}</span> <span class="text-xs font-bold text-slate-400">neg</span>
                    </h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <a href="{{ route('stock.index', ['status' => 'zero']) }}" class="font-bold text-amber-600 hover:text-amber-700">Review Items &rarr;</a>
                <span>Audited</span>
            </div>
        </div>

        <!-- 4. Total Sales Invoices & Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sales</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">${{ number_format($totalSalesAmount, 2) }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium">{{ $totalSalesCount }} Invoices</span>
                <a href="{{ route('sales.index') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Invoices &rarr;</a>
            </div>
        </div>

    </div>

    <!-- SECONDARY METRICS ROW -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <!-- Returns Card -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Customer Returns</span>
                <h4 class="text-xl font-bold text-slate-900 mt-0.5">${{ number_format($totalReturnAmount, 2) }}</h4>
                <span class="text-[11px] text-slate-400">{{ $totalReturnsCount }} return records processed</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
        </div>

        <!-- Net Revenue Card -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Net Realized Sales</span>
                <h4 class="text-xl font-bold text-emerald-600 mt-0.5">${{ number_format(max(0, $totalSalesAmount - $totalReturnAmount), 2) }}</h4>
                <span class="text-[11px] text-slate-400">Sales minus Returns</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Customers</span>
                <h4 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($totalCustomers) }}</h4>
                <span class="text-[11px] text-slate-400">Registered client accounts</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

    </div>

    <!-- CHARTS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sales vs Returns Trend -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Sales & Returns Activity</h3>
                    <p class="text-xs text-slate-500">Transaction amounts over the last 7 days</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span> Sales</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Returns</span>
                </div>
            </div>
            <div class="h-60">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Category Stock Doughnut -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="mb-3">
                <h3 class="font-bold text-sm text-slate-900">Inventory by Category</h3>
                <p class="text-xs text-slate-500">Stock distribution across lines</p>
            </div>
            <div class="h-60 flex items-center justify-center">
                <canvas id="categoryStockChart"></canvas>
            </div>
        </div>

    </div>

    <!-- RECENT ACTIVITY TABLES -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Invoices -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 px-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">Recent Sales Invoices</h3>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-amber-600 hover:underline">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="p-3 pl-5">Invoice No</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Date</th>
                            <th class="p-3 pr-5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                        <tr class="hover:bg-slate-50/70">
                            <td class="p-3 pl-5 font-bold">
                                <a href="{{ route('sales.show', $sale) }}" class="text-slate-900 hover:text-amber-600">
                                    {{ $sale->invoice_no }}
                                </a>
                            </td>
                            <td class="p-3 text-slate-700">{{ $sale->customer->name }}</td>
                            <td class="p-3 text-slate-500">{{ $sale->invoice_date->format('d M Y') }}</td>
                            <td class="p-3 pr-5 text-right font-black text-slate-900">${{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-slate-400">No invoices yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Returns -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 px-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">Recent Bill Returns</h3>
                <a href="{{ route('returns.index') }}" class="text-xs font-bold text-amber-600 hover:underline">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="p-3 pl-5">Return No</th>
                            <th class="p-3">Original Invoice</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3 pr-5 text-right">Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentReturns as $ret)
                        <tr class="hover:bg-slate-50/70">
                            <td class="p-3 pl-5 font-bold">
                                <a href="{{ route('returns.show', $ret) }}" class="text-orange-600 hover:underline">
                                    {{ $ret->return_no }}
                                </a>
                            </td>
                            <td class="p-3 font-mono text-slate-700">{{ $ret->sale->invoice_no }}</td>
                            <td class="p-3 text-slate-700">{{ $ret->customer->name }}</td>
                            <td class="p-3 pr-5 text-right font-black text-orange-600">${{ number_format($ret->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-slate-400">No returns yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartDates) !!},
                    datasets: [
                        {
                            label: 'Sales ($)',
                            data: {!! json_encode($salesTrend) !!},
                            backgroundColor: '#0f172a',
                            borderRadius: 6,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Returns ($)',
                            data: {!! json_encode($returnsTrend) !!},
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                            barPercentage: 0.6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { callback: v => '$' + v }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        const catCtx = document.getElementById('categoryStockChart')?.getContext('2d');
        if (catCtx) {
            const catData = {!! json_encode($categoriesStock) !!};
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: catData.map(c => c.name),
                    datasets: [{
                        data: catData.map(c => c.stock),
                        backgroundColor: ['#f59e0b', '#0f172a', '#10b981', '#06b6d4', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endpush
