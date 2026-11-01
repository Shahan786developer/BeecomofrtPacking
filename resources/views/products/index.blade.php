@extends('layouts.app')

@section('title', 'Product Catalog')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Products</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Product Catalog</h1>
            <p class="text-secondary small mb-0">Manage registered merchandise, automatic SKUs, selling prices, and stock.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('barcode.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-upc-scan me-1"></i> Barcode Studio
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Register Product
            </a>
        </div>
    </div>

    <!-- Search & Filters Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Search Products</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, SKU, Bill, or Code..." class="form-control border-start-0 ps-0">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Stock Status</label>
                    <select name="stock_status" class="form-select">
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (> 0)</option>
                        <option value="zero" {{ request('stock_status') === 'zero' ? 'selected' : '' }}>Zero Stock (= 0)</option>
                        <option value="negative" {{ request('stock_status') === 'negative' ? 'selected' : '' }}>Negative Stock (< 0)</option>
                        <option value="depleted_month" {{ request('stock_status') === 'depleted_month' ? 'selected' : '' }}>Depleted 30+ Days</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'stock_status']))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Product Title</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Bill No</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Actual Price</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Pricing Rule</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Selling Price</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Available Stock</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($products as $product)
                    <tr>
                        <!-- Dynamic Unified Title (Requirement #1) -->
                        <td class="ps-4 py-3">
                            <a href="{{ route('products.show', $product) }}" class="fw-bold text-dark text-decoration-none d-block">
                                {{ $product->title }}
                            </a>
                            <span class="text-secondary small">
                                Code: {{ $product->product_code }} &bull; {{ $product->dozen->label }}
                            </span>
                        </td>

                        <!-- 7-Digit Numeric SKU -->
                        <td class="py-3">
                            <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                {{ $product->sku_code }}
                            </span>
                        </td>

                        <!-- Bill No -->
                        <td class="py-3 font-monospace text-secondary small">
                            {{ $product->bill_no }}
                        </td>

                        <!-- Actual Price -->
                        <td class="py-3 text-end font-monospace text-secondary">
                            ${{ number_format($product->actual_price, 2) }}
                        </td>

                        <!-- Pricing Rule -->
                        <td class="py-3">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $product->pricingRule->name }}
                            </span>
                        </td>

                        <!-- Selling Price -->
                        <td class="py-3 text-end">
                            <span class="fw-black text-dark font-monospace fs-6">
                                ${{ number_format($product->selling_price, 2) }}
                            </span>
                        </td>

                        <!-- Stock Quantity -->
                        <td class="py-3 text-center">
                            @if($product->stock_quantity > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                    {{ $product->stock_quantity }} units
                                </span>
                            @elseif($product->stock_quantity == 0)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                                    0 units (Zero)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                                    {{ $product->stock_quantity }} units (Negative)
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('barcode.index', ['product_id' => $product->id]) }}" class="btn btn-outline-primary" title="Print Barcode">
                                    <i class="bi bi-upc-scan"></i>
                                </a>
                                <a href="{{ route('products.movements', $product) }}" class="btn btn-outline-secondary" title="Stock Movements">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-box-seam text-secondary display-6 d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">No products found matching your search.</h6>
                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-lg me-1"></i> Register First Product
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
@endsection
