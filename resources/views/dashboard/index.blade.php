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
            <p class="text-secondary small mb-0">Real-time inventory levels, customer billing, cash receipts, and pending receivables tracking.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('receipts.create') }}" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-cash-stack me-1"></i> Receive Cash (CRV)
            </a>
            <a href="{{ route('sales.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-cart-plus me-1"></i> New Sales Invoice
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-outline-secondary">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- PRIMARY SUMMARY METRICS -->
    <div class="row g-3">
        <!-- 1. Total Products -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Total Catalog</span>
                        <div class="rounded-3 bg-primary-subtle text-primary p-2">
                            <i class="bi bi-boxes fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-dark mb-1 font-monospace">{{ number_format($totalProducts) }}</div>
                        <span class="text-secondary small">Active merchandise items</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.index') }}" class="small fw-bold text-primary text-decoration-none">
                            View Catalog &rarr;
                        </a>
                        <span class="text-secondary small">Products</span>
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
                        <div class="rounded-3 bg-info-subtle text-info p-2">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-dark mb-1 font-monospace">{{ number_format($totalStockQuantity) }}</div>
                        <span class="text-secondary small">Physical warehouse units</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('stock.index') }}" class="small fw-bold text-info text-decoration-none">
                            Warehouse Stock &rarr;
                        </a>
                        <span class="text-secondary small">Units</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Cash Received (CRV) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 bg-success-subtle border border-success-subtle">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-success small fw-bold text-uppercase">Cash Received (CRV)</span>
                        <div class="rounded-3 bg-success text-white p-2">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black text-success mb-1 font-monospace">Rs. {{ number_format($totalCashReceived, 2) }}</div>
                        <span class="text-success-emphasis small">Total customer payments</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('receipts.index') }}" class="small fw-bold text-success text-decoration-none">
                            View All CRVs &rarr;
                        </a>
                        <span class="text-success small">Collected</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Outstanding Pending Receivables (Alert) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 {{ $totalReceivables > 0 ? 'bg-danger-subtle border border-danger-subtle' : '' }}">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="{{ $totalReceivables > 0 ? 'text-danger' : 'text-secondary' }} small fw-bold text-uppercase">Pending Receivables</span>
                        <div class="rounded-3 {{ $totalReceivables > 0 ? 'bg-danger text-white' : 'bg-secondary-subtle text-secondary' }} p-2">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <div class="h2 fw-black {{ $totalReceivables > 0 ? 'text-danger' : 'text-dark' }} mb-1 font-monospace">Rs. {{ number_format($totalReceivables, 2) }}</div>
                        <span class="{{ $totalReceivables > 0 ? 'text-danger' : 'text-secondary' }} small">{{ $pendingCustomers->count() }} Customers with due balance</span>
                    </div>
                    <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="#receivables-section" class="small fw-bold text-danger text-decoration-none">
                            Customer Aging &rarr;
                        </a>
                        <span class="text-danger small">Overdue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP 10 SELLING PRODUCTS & TOP CUSTOMERS VISUALIZATION -->
    <div class="row g-4">
        <!-- Top 10 Selling Products Bar Graph (8 Cols) -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Top 10 Selling Products</h5>
                        <span class="text-secondary small">Volume (units) sold across all customer invoices</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                        Ranked by Sold Qty
                    </span>
                </div>
                <div class="card-body p-4">
                    @if(count($topProductsLabels) > 0)
                        <div style="height: 320px;">
                            <canvas id="topProductsBarChart"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 text-secondary">
                            <i class="bi bi-bar-chart fs-1 d-block mb-2 text-muted"></i>
                            <p class="mb-0">No sales transactions recorded yet to rank products.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Customers Ranking (4 Cols) -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Top Customers</h5>
                        <span class="text-secondary small">Highest volume client accounts</span>
                    </div>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-primary">All &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topCustomers as $index => $c)
                        <li class="list-group-item p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge {{ $index == 0 ? 'bg-warning text-dark' : ($index == 1 ? 'bg-secondary text-white' : ($index == 2 ? 'bg-bronze text-white bg-dark' : 'bg-light text-secondary border')) }} rounded-circle p-2 fw-bold" style="width: 28px; height: 28px; display:inline-flex; align-items:center; justify-content:center;">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <a href="{{ route('customers.ledger', $c) }}" class="fw-bold text-dark text-decoration-none d-block">
                                        {{ $c->name }}
                                    </a>
                                    <span class="text-secondary fs-7">{{ $c->sales_count }} bills &bull; {{ $c->city ?: 'Pakistan' }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark font-monospace small">Rs. {{ number_format($c->total_purchased, 2) }}</div>
                                @if($c->balance_due > 0)
                                    <span class="badge bg-danger-subtle text-danger fs-8">Due: Rs. {{ number_format($c->balance_due, 2) }}</span>
                                @else
                                    <span class="badge bg-success-subtle text-success fs-8">Clear</span>
                                @endif
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item text-center py-4 text-secondary">
                            No customers with sales recorded.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOMER PENDING RECEIVABLES & AGING BREAKDOWN SECTION -->
    <div id="receivables-section">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-danger mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Customer Pending Receivables &amp; Aging Ledger
                    </h5>
                    <span class="text-secondary small">Clients with outstanding unpaid balances, overdue duration, and quick payment vouchers.</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-danger text-white fs-7 px-3 py-1.5 font-monospace">
                        Total Due: Rs. {{ number_format($totalReceivables, 2) }}
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 25%;">Customer &amp; Owner</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 15%;">Contact</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 13%;">Total Invoiced</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 13%;">Paid via CRV</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 14%;">Balance Due</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 10%;">Aging</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 10%;">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($pendingCustomers as $pc)
                        <tr>
                            <!-- Customer & Owner -->
                            <td class="ps-4 py-3">
                                <a href="{{ route('customers.ledger', $pc) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $pc->name }}
                                </a>
                                @if($pc->owner_name)
                                    <span class="text-secondary fs-7"><i class="bi bi-person me-1"></i>Owner: {{ $pc->owner_name }}</span>
                                @endif
                            </td>

                            <!-- Contact -->
                            <td class="py-3 text-secondary small">
                                <div><i class="bi bi-telephone me-1 text-primary"></i>{{ $pc->phone ?: '—' }}</div>
                                @if($pc->city)<span class="fs-7 text-muted">{{ $pc->city }}</span>@endif
                            </td>

                            <!-- Total Invoiced -->
                            <td class="py-3 text-end font-monospace small">
                                Rs. {{ number_format($pc->total_invoiced, 2) }}
                            </td>

                            <!-- Paid via CRV -->
                            <td class="py-3 text-end font-monospace small text-success fw-bold">
                                Rs. {{ number_format($pc->total_paid, 2) }}
                            </td>

                            <!-- Balance Due -->
                            <td class="py-3 text-end font-monospace fw-bold text-danger fs-6">
                                Rs. {{ number_format($pc->balance_due, 2) }}
                            </td>

                            <!-- Aging Days Overdue -->
                            <td class="py-3 text-center">
                                @php $days = $pc->pending_days; @endphp
                                @if($days >= 45)
                                    <span class="badge bg-danger text-white font-monospace px-2 py-1">
                                        <i class="bi bi-fire me-1"></i>{{ $days }}d overdue
                                    </span>
                                @elseif($days >= 30)
                                    <span class="badge bg-warning text-dark font-monospace px-2 py-1">
                                        {{ $days }}d pending
                                    </span>
                                @elseif($days > 0)
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        {{ $days }}d recent
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted font-monospace px-2 py-1">0d</span>
                                @endif
                            </td>

                            <!-- Action: Receive Cash -->
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('receipts.create', ['customer_id' => $pc->id]) }}" class="btn btn-sm btn-success fw-bold py-1 px-2.5" title="Record Cash Receipt Voucher">
                                        <i class="bi bi-cash-stack me-1"></i> Pay
                                    </a>
                                    <a href="{{ route('customers.ledger', $pc) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View Full Ledger">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-success fw-semibold">
                                <i class="bi bi-check-circle-fill fs-3 d-block mb-2 text-success"></i>
                                Excellent! All customer accounts are currently fully settled. No pending receivables.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SALES & RETURNS 7-DAY ACTIVITY & CATEGORY BREAKDOWN -->
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
                                <td class="pe-4 py-3 text-end font-monospace fw-bold text-dark">Rs. {{ number_format($sale->total_amount, 2) }}</td>
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
                                <td class="pe-4 py-3 text-end font-monospace fw-bold text-danger">Rs. {{ number_format($ret->total_amount, 2) }}</td>
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

    <!-- ONE-MONTH ZERO/NEGATIVE STOCK ALERT BANNER -->
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

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Top 10 Selling Products Bar Chart
        const topProdCtx = document.getElementById('topProductsBarChart')?.getContext('2d');
        if (topProdCtx) {
            new Chart(topProdCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topProductsLabels) !!},
                    datasets: [{
                        label: 'Sold Quantity (Units)',
                        data: {!! json_encode($topProductsQtys) !!},
                        backgroundColor: '#0d6efd',
                        borderRadius: 6,
                        barPercentage: 0.65,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', // horizontal bar chart for clear product title readability!
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.parsed.x + ' units sold';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { weight: '600', size: 11 },
                                callback: function(val, index) {
                                    let label = this.getLabelForValue(val);
                                    return label.length > 25 ? label.substr(0, 25) + '...' : label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Sales vs Returns Activity Chart
        const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartDates) !!},
                    datasets: [
                        {
                            label: 'Sales (Rs.)',
                            data: {!! json_encode($salesTrend) !!},
                            backgroundColor: '#0d6efd',
                            borderRadius: 6,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Returns (Rs.)',
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
                            ticks: { callback: v => 'Rs. ' + v }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 3. Category Stock Doughnut Chart
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
