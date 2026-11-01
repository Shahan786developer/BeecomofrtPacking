@extends('layouts.app')

@section('title', 'Global Stock Movements Ledger')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('stock.index') }}" class="text-decoration-none">Stock Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">Movements Ledger</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Stock Movements Ledger</h1>
            <p class="text-secondary small mb-0">Audit log tracking all additions, deductions, sales, and return stock transactions.</p>
        </div>
        <div>
            <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('stock.movements') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-secondary mb-1">Search Product / SKU</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Title, SKU, or Code..." class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Movement Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Movement Types</option>
                        <option value="initial" {{ request('type') === 'initial' ? 'selected' : '' }}>Initial Registration</option>
                        <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sales Invoice Deduction</option>
                        <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Bill Return Restoration</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        Filter Ledger
                    </button>
                    @if(request()->anyFilled(['search', 'type']))
                        <a href="{{ route('stock.movements') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Timestamp</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Product Title (Unified Title)</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Type</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Change</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Previous Stock</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">New Stock</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase fs-7">Transaction / Notes</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($movements as $m)
                    <tr>
                        <td class="ps-4 py-3 font-monospace text-secondary small text-nowrap">
                            {{ $m->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3">
                            <a href="{{ route('products.show', $m->product) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $m->product->title }}
                            </a>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                {{ $m->product->sku_code }}
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $m->type === 'sale' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($m->type === 'return' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle') }} text-uppercase">
                                {{ $m->type }}
                            </span>
                        </td>
                        <td class="py-3 text-center fw-bold fs-6 {{ $m->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                        </td>
                        <td class="py-3 text-center text-secondary">{{ $m->previous_stock }}</td>
                        <td class="py-3 text-center fw-bold text-dark">{{ $m->new_stock }}</td>
                        <td class="pe-4 py-3 text-secondary small">
                            {{ $m->notes ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">No stock movements found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
