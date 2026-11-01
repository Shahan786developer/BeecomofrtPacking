@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Sales Invoices</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Sales Invoices</h1>
            <p class="text-secondary small mb-0">Record retail sales with instant barcode SKU scanning and automatic inventory reduction.</p>
        </div>
        <div>
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="bi bi-cart-plus me-1"></i> New Sales Invoice
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Search Invoice / Customer</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or customer name..." class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'from_date', 'to_date']))
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Invoice No</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Customer</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Items Sold</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Subtotal</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Discount</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Total Amount</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($sales as $sale)
                    <tr>
                        <td class="ps-4 py-3">
                            <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-primary text-decoration-none font-monospace fs-6">
                                {{ $sale->invoice_no }}
                            </a>
                        </td>
                        <td class="py-3">
                            <span class="fw-bold text-dark d-block">{{ $sale->customer->name }}</span>
                            @if($sale->customer->phone)
                                <span class="text-secondary small"><i class="bi bi-telephone me-1"></i>{{ $sale->customer->phone }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-secondary">
                            {{ $sale->invoice_date->format('d M Y') }}
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                {{ $sale->details->count() }} item(s) ({{ $sale->details->sum('quantity') }} pcs)
                            </span>
                        </td>
                        <td class="py-3 text-end text-secondary">
                            ${{ number_format($sale->subtotal, 2) }}
                        </td>
                        <td class="py-3 text-end text-danger">
                            {{ $sale->discount > 0 ? '-$' . number_format($sale->discount, 2) : '—' }}
                        </td>
                        <td class="py-3 text-end">
                            <span class="fw-black text-dark fs-6">${{ number_format($sale->total_amount, 2) }}</span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-primary" title="View Invoice">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" class="btn btn-outline-warning" title="Process Return">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Return
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-receipt text-secondary display-6 d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">No sales invoices recorded yet</h6>
                            <p class="small text-secondary mb-3">Begin by scanning SKU barcodes on a new sales invoice.</p>
                            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus me-1"></i> Create First Sales Invoice
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
