@extends('layouts.app')

@section('title', 'Operations Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Executive Dashboard</h1>
            <p class="text-secondary small mb-0">Real-time inventory levels, customer billing, and stock depletion tracking.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-cart-plus me-1"></i> New Sales Invoice
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-outline-secondary">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- ONE-MONTH ZERO/NEGATIVE STOCK ALERT BANNER (If any) -->
    @if($oneMonthDepletedProducts->count() > 0)
    <div class="card border-danger-subtle bg-danger-subtle shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger text-white rounded-3 p-3 d-flex align-items-center justify-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-danger mb-1">
                            Critical Attention: One-Month Depleted Stock Alert ({{ $oneMonthDepletedProducts->count() }} Products)
                        </h5>
                        <p class="text-danger-emphasis small mb-0">
                            These items have remained depleted (&le; 0 stock) for 30+ days. Per inventory policy, products remain active.
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('stock.index', ['status' => 'depleted_month']) }}" class="btn btn-danger btn-sm fw-bold whitespace-nowrap">
                        Review Depleted Stock &rarr;
                    </a>
                </div>
            </div>

            <!-- Compact Alert Items Grid -->
            <div class="row g-2 mt-3">
                @foreach($oneMonthDepletedProducts as $depProduct)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="bg-white rounded-3 p-3 border d-flex justify-content-between align-items-center shadow-xs">
                        <div>
                            <span class="fw-bold text-dark small d-block text-truncate" style="max-width: 200px;">{{ $depProduct->title }}</span>
                            <span class="font-monospace text-secondary small">SKU: {{ $depProduct->sku_code }}</span>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $depProduct->stock_quantity < 0 ? 'bg-danger text-white' : 'bg-warning text-dark' }} px-2 py-1 font-monospace">
                                {{ $depProduct->stock_quantity }} units
                            </span>
                            <span class="text-secondary small d-block mt-0.5">{{ $depProduct->depleted_days }}d depleted</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- SUMMARY METRICS -->
    <div class="row g-3">
        <!-- 1. Total Products -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Total Products</span>
                        <div class="rounded-3 bg-primary-subtle text-primary p-2">
                            <i class="bi bi-boxes fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-dark mb-1 font-monospace">{{ number_format($totalProducts) }}</div>
                        <span class="text-secondary small">Active merchandise catalog</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.index') }}" class="small fw-bold text-primary text-decoration-none">
                            View Products &rarr;
                        </a>
                        <span class="text-secondary small">Catalog</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Stock Units -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Total Stock</span>
                        <div class="rounded-3 bg-success-subtle text-success p-2">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-success mb-1 font-monospace">{{ number_format($totalStockQuantity) }}</div>
                        <span class="text-secondary small">Total physical warehouse units</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('stock.index') }}" class="small fw-bold text-success text-decoration-none">
                            Warehouse Levels &rarr;
                        </a>
                        <span class="text-secondary small">Units</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Zero / Negative Stock -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Out / Backorder</span>
                        <div class="rounded-3 bg-warning-subtle text-warning p-2">
                            <i class="bi bi-exclamation-triangle fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-dark mb-1 font-monospace">
                            {{ $zeroStockCount }} <span class="fs-6 fw-normal text-secondary">zero</span> / 
                            <span class="text-danger">{{ $negativeStockCount }}</span> <span class="fs-6 fw-normal text-secondary">neg</span>
                        </div>
                        <span class="text-secondary small">Zero stock &amp; backorders</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('stock.index', ['status' => 'zero']) }}" class="small fw-bold text-warning-emphasis text-decoration-none">
                            Review Items &rarr;
                        </a>
                        <span class="text-secondary small">Audited</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Sales Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Total Sales</span>
                        <div class="rounded-3 bg-primary-subtle text-primary p-2">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-primary mb-1 font-monospace">${{ number_format($totalSalesAmount, 2) }}</div>
                        <span class="text-secondary small">{{ $totalSalesCount }} Customer Invoices</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('sales.index') }}" class="small fw-bold text-primary text-decoration-none">
                            View Invoices &rarr;
                        </a>
                        <span class="text-secondary small">Revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECONDARY METRICS ROW -->
    <div class="row g-3">
        <!-- Customer Returns -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Customer Returns</span>
                        <div class="h4 fw-bold text-danger mb-0 font-monospace">${{ number_format($totalReturnAmount, 2) }}</div>
                        <span class="text-secondary small">{{ $totalReturnsCount }} returns processed</span>
                    </div>
                    <div class="rounded-3 bg-danger-subtle text-danger p-2">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Realized Sales -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Net Realized Sales</span>
                        <div class="h4 fw-bold text-success mb-0 font-monospace">${{ number_format(max(0, $totalSalesAmount - $totalReturnAmount), 2) }}</div>
                        <span class="text-secondary small">Sales minus Returns</span>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-2">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Registered Customers</span>
                        <div class="h4 fw-bold text-dark mb-0 font-monospace">{{ number_format($totalCustomers) }}</div>
                        <span class="text-secondary small">Client profiles</span>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="row g-4">
        <!-- Sales & Returns Activity (7 Cols) -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Sales &amp; Returns Activity</h5>
                        <span class="text-secondary small">Transactions over the last 7 days</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 small">
                        <span class="d-flex align-items-center gap-1 text-primary fw-bold">
                            <span class="p-1 rounded bg-primary d-inline-block"></span> Sales
                        </span>
                        <span class="d-flex align-items-center gap-1 text-warning fw-bold">
                            <span class="p-1 rounded bg-warning d-inline-block"></span> Returns
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div style="height: 260px;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory by Category (4 Cols) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Inventory by Category</h5>
                    <span class="text-secondary small">Stock distribution across lines</span>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center">
                    <div style="height: 260px; width: 100%;">
                        <canvas id="categoryStockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT ACTIVITY TABLES -->
    <div class="row g-4">
        <!-- Recent Invoices -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Recent Sales Invoices</h5>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Invoice No</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Customer</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Date</th>
                                <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($recentSales as $sale)
                            <tr>
                                <td class="ps-4 py-3 font-monospace fw-bold">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-primary text-decoration-none">
                                        {{ $sale->invoice_no }}
                                    </a>
                                </td>
                                <td class="py-3 text-dark fw-semibold">{{ $sale->customer->name }}</td>
                                <td class="py-3 text-secondary small">{{ $sale->invoice_date->format('d M Y') }}</td>
                                <td class="pe-4 py-3 text-end font-monospace fw-bold text-dark">${{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">No invoices recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Returns -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Recent Bill Returns</h5>
                    <a href="{{ route('returns.index') }}" class="btn btn-sm btn-outline-warning">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Return No</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Original Invoice</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Customer</th>
                                <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Refund</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($recentReturns as $ret)
                            <tr>
                                <td class="ps-4 py-3 font-monospace fw-bold">
                                    <a href="{{ route('returns.show', $ret) }}" class="text-danger text-decoration-none">
                                        {{ $ret->return_no }}
                                    </a>
                                </td>
                                <td class="py-3 font-monospace small text-primary">{{ $ret->sale->invoice_no }}</td>
                                <td class="py-3 text-dark fw-semibold">{{ $ret->customer->name }}</td>
                                <td class="pe-4 py-3 text-end font-monospace fw-bold text-danger">${{ number_format($ret->total_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">No returns recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                            backgroundColor: '#0d6efd',
                            borderRadius: 6,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Returns ($)',
                            data: {!! json_encode($returnsTrend) !!},
                            backgroundColor: '#ffc107',
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
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6f42c1'],
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
