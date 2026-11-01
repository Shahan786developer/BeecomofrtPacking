@extends('layouts.app')

@section('title', $product->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $product->title }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-primary font-monospace px-3 py-2 fs-7">
                    <i class="bi bi-upc me-1"></i> SKU: {{ $product->sku_code }}
                </span>
                <span class="badge bg-light text-secondary border px-3 py-2 fs-7">
                    Bill No: {{ $product->bill_no }}
                </span>
                <span class="badge bg-light text-secondary border px-3 py-2 fs-7">
                    Code: {{ $product->product_code }}
                </span>
            </div>
            <!-- Unified Dynamic Title (Requirement #1) -->
            <h1 class="h3 fw-bold text-dark mb-0">{{ $product->title }}</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('barcode.index', ['product_id' => $product->id]) }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Barcode Labels
            </a>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-pencil me-1"></i> Edit Product
            </a>
        </div>
    </div>

    <!-- Product Key Metrics Cards -->
    <div class="row g-3">
        <!-- Available Stock -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Available Stock</span>
                        <i class="bi bi-boxes text-primary fs-5"></i>
                    </div>
                    <div class="h2 fw-black mb-1 {{ $product->stock_quantity > 0 ? 'text-success' : ($product->stock_quantity == 0 ? 'text-warning' : 'text-danger') }}">
                        {{ $product->stock_quantity }} <span class="fs-6 fw-normal text-secondary">units</span>
                    </div>
                    <div class="small">
                        @if($product->stock_quantity <= 0 && $product->zero_stock_date)
                            <span class="text-danger fw-bold">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Depleted {{ $product->depleted_days }} days ago
                            </span>
                        @else
                            <span class="text-success fw-bold">
                                <i class="bi bi-check-circle-fill me-1"></i> Ready for retail sale
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculated Selling Price -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Selling Price</span>
                        <i class="bi bi-tag text-primary fs-5"></i>
                    </div>
                    <div class="h2 fw-black text-dark mb-1 font-monospace">
                        Rs. {{ number_format($product->selling_price, 2) }}
                    </div>
                    <span class="text-secondary small">Fixed rate loaded during barcode scan</span>
                </div>
            </div>
        </div>

        <!-- Actual Purchase Price -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Purchase Price</span>
                        <i class="bi bi-cash-stack text-secondary fs-5"></i>
                    </div>
                    <div class="h2 fw-black text-secondary mb-1 font-monospace">
                        Rs. {{ number_format($product->actual_price, 2) }}
                    </div>
                    <span class="text-secondary small">Margin: <strong class="text-success">Rs. {{ number_format($product->selling_price - $product->actual_price, 2) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Pricing Configuration -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold text-uppercase">Pricing Rule</span>
                        <i class="bi bi-percent text-info fs-5"></i>
                    </div>
                    <div class="h4 fw-bold text-dark mb-1">
                        {{ $product->pricingRule->name }}
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        +{{ number_format($product->pricingRule->percentage, 2) }}% markup rule
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movements Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">Recent Stock Movements</h5>
                <span class="text-secondary small">Audit trail of sales, returns, and inventory adjustments</span>
            </div>
            <a href="{{ route('products.movements', $product) }}" class="btn btn-outline-primary btn-sm">
                View Full Movement Ledger <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Date & Time</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Type</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Change</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Previous Stock</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">New Stock</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase fs-7">Notes / Reference</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($product->stockMovements as $move)
                    <tr>
                        <td class="ps-4 py-3 text-secondary font-monospace small">
                            {{ $move->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $move->type === 'sale' ? 'bg-primary-subtle text-primary' : ($move->type === 'return' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }} text-uppercase">
                                {{ $move->type }}
                            </span>
                        </td>
                        <td class="py-3 text-center fw-bold {{ $move->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $move->quantity_change > 0 ? '+' : '' }}{{ $move->quantity_change }}
                        </td>
                        <td class="py-3 text-center text-secondary">{{ $move->previous_stock }}</td>
                        <td class="py-3 text-center fw-bold text-dark">{{ $move->new_stock }}</td>
                        <td class="pe-4 py-3 text-secondary small">{{ $move->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">No stock movements recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
