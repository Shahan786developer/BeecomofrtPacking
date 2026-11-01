@extends('layouts.app')

@section('title', 'Warehouse Stock Levels')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Stock Levels</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Stock & Inventory Levels</h1>
            <p class="text-secondary small mb-0">Real-time inventory quantities, backorder tracking, and physical audit adjustments.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock.movements') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i> Movement Ledger
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Intake New Stock
            </a>
        </div>
    </div>

    <!-- 5 Summary Filter Cards -->
    <div class="row g-3">
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('stock.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ !request('status') ? 'border-primary border-2' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-secondary text-uppercase fw-bold fs-7 d-block">All Products</span>
                        <div class="h3 fw-black text-dark mb-0 mt-1">{{ number_format($stats['total_products']) }}</div>
                        <span class="text-secondary small">Catalog items</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('stock.index', ['status' => 'in_stock']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ request('status') === 'in_stock' ? 'border-success border-2' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-success text-uppercase fw-bold fs-7 d-block">Positive Stock</span>
                        <div class="h3 fw-black text-success mb-0 mt-1">{{ number_format($stats['total_stock']) }}</div>
                        <span class="text-secondary small">Available units</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('stock.index', ['status' => 'zero']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ request('status') === 'zero' ? 'border-warning border-2' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-warning text-uppercase fw-bold fs-7 d-block">Zero Stock</span>
                        <div class="h3 fw-black text-warning mb-0 mt-1">{{ number_format($stats['zero_stock']) }}</div>
                        <span class="text-secondary small">0 units in warehouse</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('stock.index', ['status' => 'negative']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ request('status') === 'negative' ? 'border-danger border-2' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-danger text-uppercase fw-bold fs-7 d-block">Negative Stock</span>
                        <div class="h3 fw-black text-danger mb-0 mt-1">{{ number_format($stats['negative_stock']) }}</div>
                        <span class="text-secondary small">Backorder units</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4 col-xl">
            <a href="{{ route('stock.index', ['status' => 'depleted_month']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ request('status') === 'depleted_month' ? 'border-danger border-2' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-danger text-uppercase fw-bold fs-7 d-block">Depleted 30d+</span>
                        <div class="h3 fw-black text-danger mb-0 mt-1">{{ number_format($stats['depleted_month']) }}</div>
                        <span class="text-secondary small">Critical attention</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('stock.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-secondary mb-1">Search Products</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, SKU, or Code..." class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        Filter Stock
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'status']))
                        <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Product Title</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Current Stock</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Depletion Status</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Selling Price</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4 py-3">
                            <a href="{{ route('products.show', $product) }}" class="fw-bold text-dark text-decoration-none d-block">
                                {{ $product->title }}
                            </a>
                            <span class="text-secondary small">Bill: {{ $product->bill_no }} &bull; Code: {{ $product->product_code }}</span>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                {{ $product->sku_code }}
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            @if($product->stock_quantity > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                    {{ $product->stock_quantity }} units
                                </span>
                            @elseif($product->stock_quantity === 0)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                                    0 units (Zero)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                                    {{ $product->stock_quantity }} units (Negative)
                                </span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($product->stock_quantity <= 0 && $product->zero_stock_date)
                                <div>
                                    <span class="fw-bold text-danger small">Depleted: {{ $product->zero_stock_date->format('d M Y') }}</span>
                                    <span class="text-secondary small d-block">({{ $product->depleted_days }} days elapsed)</span>
                                </div>
                            @else
                                <span class="text-success small fw-bold"><i class="bi bi-check-circle me-1"></i> In Stock</span>
                            @endif
                        </td>
                        <td class="py-3 text-end font-monospace fw-bold text-dark">
                            ${{ number_format($product->selling_price, 2) }}
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" onclick="openAdjustModal({{ $product->id }}, '{{ addslashes($product->title) }}', {{ $product->stock_quantity }})"
                                    class="btn btn-outline-primary">
                                    <i class="bi bi-sliders me-1"></i> Adjust
                                </button>
                                <a href="{{ route('products.movements', $product) }}" class="btn btn-outline-secondary" title="View Movements">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">No products found matching filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>

<!-- ADJUST STOCK MODAL (Bootstrap 5) -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="adjustStockModalLabel">
                    <i class="bi bi-sliders text-primary me-2"></i> Adjust Stock Count
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="adjust-stock-form" action="">
                @csrf
                <div class="modal-body p-4 d-flex flex-column gap-3">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Product:</span>
                        <h6 id="adjust-modal-title" class="fw-bold text-dark mb-0 mt-1"></h6>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-secondary small fw-bold text-uppercase d-block">Current Stock Count:</span>
                        <span id="adjust-modal-current" class="h5 fw-black text-dark mb-0 mt-1 d-block font-monospace"></span>
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Adjustment Action <span class="text-danger">*</span>
                        </label>
                        <select name="adjustment_type" required class="form-select fw-semibold">
                            <option value="add">Add Units to Inventory (+)</option>
                            <option value="subtract">Subtract Units from Inventory (-)</option>
                            <option value="set">Set Exact Count (Physical Audit)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Quantity of Units <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="quantity" required min="0" placeholder="Number of units" class="form-control fw-bold font-monospace">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Adjustment Reason / Note <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="notes" required placeholder="e.g. Audit reconciliation, damaged goods" class="form-control">
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let adjustModalInstance = null;

    function openAdjustModal(productId, productTitle, currentStock) {
        document.getElementById('adjust-modal-title').textContent = productTitle;
        document.getElementById('adjust-modal-current').textContent = currentStock + ' units';
        document.getElementById('adjust-stock-form').action = `/stock/${productId}/adjust`;

        if (!adjustModalInstance) {
            adjustModalInstance = new bootstrap.Modal(document.getElementById('adjustStockModal'));
        }
        adjustModalInstance.show();
    }
</script>
@endpush
