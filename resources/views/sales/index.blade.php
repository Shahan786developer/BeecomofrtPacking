@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Sales Invoices</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE TITLE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Sales Invoices</h1>
            <p class="text-secondary small mb-0">Customer sales records, automated SKU deductions, and printed receipts.</p>
        </div>
    </div>

    <!-- CONTROLS: SEARCH ABOVE + ACTION BUTTONS (CREATE & PRINT) -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Functionality Above -->
                <div class="col-12 col-md-7 col-lg-8">
                    <form method="GET" action="{{ route('sales.index') }}" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by invoice # or customer name..." 
                                class="form-control border-start-0 ps-0">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">
                            Search
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Action Buttons: Create & Process Return -->
                <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-md-end gap-2">
                    <a href="{{ route('sales.create') }}" class="btn btn-primary fw-bold shadow-sm flex-fill flex-md-grow-0">
                        <i class="bi bi-cart-plus me-1"></i> Create Invoice
                    </a>
                    <a href="{{ route('returns.create') }}" class="btn btn-outline-warning fw-bold flex-fill flex-md-grow-0">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Process Return
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
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Invoice No</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Customer</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Items Sold</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Total Amount</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($sales as $sale)
                    <tr>
                        <!-- Invoice Number -->
                        <td class="ps-3 py-3">
                            <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-primary text-decoration-none font-monospace fs-6">
                                {{ $sale->invoice_no }}
                            </a>
                        </td>

                        <!-- Customer Details -->
                        <td class="py-3">
                            <span class="fw-bold text-dark d-block">{{ $sale->customer->name }}</span>
                            @if($sale->customer->phone)
                                <span class="text-secondary small"><i class="bi bi-telephone me-1"></i>{{ $sale->customer->phone }}</span>
                            @endif
                        </td>

                        <!-- Date -->
                        <td class="py-3 text-secondary small">
                            {{ $sale->invoice_date->format('d M Y') }}
                        </td>

                        <!-- Items Sold Count -->
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                {{ $sale->details->count() }} item(s) ({{ $sale->details->sum('quantity') }} pcs)
                            </span>
                        </td>

                        <!-- Grand Total -->
                        <td class="py-3 text-end">
                            <span class="fw-black text-dark font-monospace fs-6">
                                ${{ number_format($sale->total_amount, 2) }}
                            </span>
                        </td>

                        <!-- Action Buttons (View, Print, Return) -->
                        <td class="pe-3 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-primary" title="View Invoice">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('sales.show', $sale) }}" onclick="setTimeout(() => window.print(), 300)" class="btn btn-outline-secondary" title="Print Invoice">
                                    <i class="bi bi-printer me-1"></i> Print
                                </a>
                                <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" class="btn btn-outline-warning" title="Process Return">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Return
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="bi bi-receipt display-6 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold text-dark">No sales invoices recorded yet.</h6>
                            <p class="small text-secondary mb-3">Scan barcodes to generate your first customer bill.</p>
                            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus me-1"></i> Create First Invoice
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
