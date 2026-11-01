@extends('layouts.app')

@section('title', 'Stock Movements — ' . $product->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.show', $product) }}" class="text-decoration-none">{{ $product->title }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stock Movements</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary font-monospace px-2 py-1">
                    SKU: {{ $product->sku_code }}
                </span>
                <span class="text-secondary small">Current Stock: <strong class="text-dark">{{ $product->stock_quantity }} units</strong></span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">Stock Movement History</h1>
            <p class="text-secondary small mb-0">Complete audit log for {{ $product->title }}</p>
        </div>
        <div>
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Product
            </a>
        </div>
    </div>

    <!-- Movements Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Timestamp</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Movement Type</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Change</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Previous Stock</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">New Stock</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase fs-7">Notes / Reference</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($movements as $move)
                    <tr>
                        <td class="ps-4 py-3 font-monospace text-secondary small">
                            {{ $move->created_at->format('d M Y, H:i:s') }}
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $move->type === 'sale' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($move->type === 'return' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle') }} text-uppercase">
                                {{ $move->type }}
                            </span>
                        </td>
                        <td class="py-3 text-center fw-bold fs-6 {{ $move->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $move->quantity_change > 0 ? '+' : '' }}{{ $move->quantity_change }}
                        </td>
                        <td class="py-3 text-center text-secondary">{{ $move->previous_stock }}</td>
                        <td class="py-3 text-center fw-bold text-dark">{{ $move->new_stock }}</td>
                        <td class="pe-4 py-3 text-secondary small">{{ $move->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">No movements recorded for this product yet.</td>
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
