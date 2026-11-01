@extends('layouts.app')

@section('title', 'Products & Stock')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Products</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE TITLE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Products &amp; Inventory</h1>
            <p class="text-secondary small mb-0">Product catalog, unified titles, SKU barcodes, and live warehouse stock.</p>
        </div>
    </div>

    <!-- CONTROLS: SEARCH ABOVE + ACTION BUTTONS (CREATE & PRINT) -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Functionality Above -->
                <div class="col-12 col-md-7 col-lg-8">
                    <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by product title, SKU code, product code, or bill..." 
                                class="form-control border-start-0 ps-0">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">
                            Search
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Action Buttons: Create & Print -->
                <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-md-end gap-2">
                    <a href="{{ route('products.create') }}" class="btn btn-primary fw-bold shadow-sm flex-fill flex-md-grow-0">
                        <i class="bi bi-plus-lg me-1"></i> Create Product
                    </a>
                    <a href="{{ route('barcode.index') }}" class="btn btn-outline-primary fw-bold flex-fill flex-md-grow-0">
                        <i class="bi bi-printer me-1"></i> Print Barcodes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- LIST TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Product Title (Unified Title)</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">SKU Code</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Bill No</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Purchase Price</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Selling Price</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Stock</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($products as $product)
                    <tr>
                        <!-- Unified Product Title -->
                        <td class="ps-3 py-3">
                            <a href="{{ route('products.show', $product) }}" class="fw-bold text-dark text-decoration-none d-block fs-6">
                                {{ $product->title }}
                            </a>
                            <span class="text-secondary small">
                                Code: <strong class="text-dark">{{ $product->product_code }}</strong> &bull; {{ $product->dozen->label }}
                            </span>
                        </td>

                        <!-- 7-digit SKU -->
                        <td class="py-3">
                            <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                {{ $product->sku_code }}
                            </span>
                        </td>

                        <!-- Bill No -->
                        <td class="py-3 font-monospace small text-secondary">
                            {{ $product->bill_no }}
                        </td>

                        <!-- Actual Purchase Price -->
                        <td class="py-3 text-end font-monospace text-secondary">
                            ${{ number_format($product->actual_price, 2) }}
                        </td>

                        <!-- Selling Price -->
                        <td class="py-3 text-end">
                            <span class="fw-black text-dark font-monospace fs-6">
                                ${{ number_format($product->selling_price, 2) }}
                            </span>
                        </td>

                        <!-- Available Stock -->
                        <td class="py-3 text-center">
                            @if($product->stock_quantity > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                    {{ $product->stock_quantity }} units
                                </span>
                            @elseif($product->stock_quantity == 0)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                                    0 (Zero)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                                    {{ $product->stock_quantity }} (Negative)
                                </span>
                            @endif
                        </td>

                        <!-- Action Buttons (Edit, Print, Stock) -->
                        <td class="pe-3 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="Edit Product">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <a href="{{ route('barcode.index', ['product_id' => $product->id]) }}" class="btn btn-outline-secondary" title="Print Barcode">
                                    <i class="bi bi-printer me-1"></i> Print
                                </a>
                                <button type="button" onclick="openAdjustModal({{ $product->id }}, '{{ addslashes($product->title) }}', {{ $product->stock_quantity }})" class="btn btn-outline-warning" title="Adjust Stock">
                                    <i class="bi bi-sliders"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-box-seam display-6 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold text-dark">No products found.</h6>
                            <p class="small text-secondary mb-3">Add your first product to generate automated SKU barcodes and inventory tracking.</p>
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Create First Product
                            </a>
                        </td>
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

<!-- ADJUST STOCK MODAL -->
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
