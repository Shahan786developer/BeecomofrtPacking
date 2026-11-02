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
        <div class="card-body p-3 d-flex flex-column gap-3">
            <div class="row g-2 align-items-center">
                <!-- Search Functionality Above -->
                <div class="col-12 col-md-7 col-lg-8">
                    <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2">
                        @if(request('parent_category'))
                            <input type="hidden" name="parent_category" value="{{ request('parent_category') }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by product title, SKU code, product code, or bill..." 
                                class="form-control border-start-0 ps-0" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">
                            Search
                        </button>
                        <a href="{{ route('products.index', request('parent_category') ? ['parent_category' => request('parent_category')] : []) }}" class="btn btn-outline-secondary btn-clear-search {{ request('search') ? '' : 'd-none' }}" title="Clear Search">
                            <i class="bi bi-x-lg"></i>
                        </a>
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

            <!-- Parent Category Filter Pills -->
            <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
                <span class="small fw-bold text-secondary text-uppercase me-1">Category Filter:</span>
                <a href="{{ route('products.index', array_merge(request()->except('parent_category', 'page'))) }}" 
                   class="btn btn-sm {{ !request('parent_category') ? 'btn-dark' : 'btn-outline-secondary' }}">
                    All Items
                </a>
                <a href="{{ route('products.index', array_merge(request()->except('page'), ['parent_category' => 'Bee Comfort'])) }}" 
                   class="btn btn-sm {{ request('parent_category') === 'Bee Comfort' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-shield-check me-1"></i> Bee Comfort
                </a>
                <a href="{{ route('products.index', array_merge(request()->except('page'), ['parent_category' => 'China'])) }}" 
                   class="btn btn-sm {{ request('parent_category') === 'China' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-box-seam me-1"></i> China
                </a>
            </div>
        </div>
    </div>

    <!-- LIST TABLE -->
    <div id="table-container">
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
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <a href="{{ route('products.show', $product) }}" class="fw-bold text-dark text-decoration-none fs-6">
                                    {{ $product->title }}
                                </a>
                                @if($product->parent_category === 'China')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0.5 px-1.5" style="font-size: 0.7rem;">China</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0.5 px-1.5" style="font-size: 0.7rem;">Bee Comfort</span>
                                @endif
                            </div>
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

                        <!-- Actual Purchase Price (Rupees) -->
                        <td class="py-3 text-end font-monospace text-secondary">
                            Rs. {{ number_format($product->actual_price, 2) }}
                        </td>

                        <!-- Selling Price (Rupees) -->
                        <td class="py-3 text-end">
                            <span class="fw-black text-dark font-monospace fs-6">
                                Rs. {{ number_format($product->selling_price, 2) }}
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

                        <!-- Action Icons (View, Edit, Print) -->
                        <td class="pe-3 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info" title="View Product">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Edit Product">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('barcode.index', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-dark" title="Print Barcode Labels">
                                    <i class="bi bi-printer"></i>
                                </a>
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

</div>
@endsection
