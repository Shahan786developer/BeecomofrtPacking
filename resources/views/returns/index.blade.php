@extends('layouts.app')

@section('title', 'Customer Bill Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Bill Returns</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE TITLE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Customer Bill Returns</h1>
            <p class="text-secondary small mb-0">Merchandise returns, sales invoice quantity validation, and inventory restorations.</p>
        </div>
    </div>

    <!-- CONTROLS: SEARCH ABOVE + ACTION BUTTONS (CREATE & PRINT) -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Functionality Above -->
                <div class="col-12 col-md-8">
                    <form method="GET" action="{{ route('returns.index') }}" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by return #, original invoice #, or customer name..." 
                                class="form-control border-start-0 ps-0">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">
                            Search
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Action Button: Create Return -->
                <div class="col-12 col-md-4 d-flex justify-content-md-end">
                    <a href="{{ route('returns.create') }}" class="btn btn-primary fw-bold shadow-sm w-100 w-md-auto">
                        <i class="bi bi-plus-lg me-1"></i> Create Return
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
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Return No</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Original Invoice</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Customer</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Return Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Units Returned</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Refund Amount</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($returns as $ret)
                    <tr>
                        <!-- Return Number -->
                        <td class="ps-3 py-3">
                            <a href="{{ route('returns.show', $ret) }}" class="fw-bold text-danger text-decoration-none font-monospace fs-6">
                                {{ $ret->return_no }}
                            </a>
                        </td>

                        <!-- Original Invoice -->
                        <td class="py-3">
                            <a href="{{ route('sales.show', $ret->sale) }}" class="text-primary fw-bold text-decoration-none font-monospace">
                                {{ $ret->sale->invoice_no }}
                            </a>
                        </td>

                        <!-- Customer Details -->
                        <td class="py-3 fw-bold text-dark">
                            {{ $ret->customer->name }}
                        </td>

                        <!-- Date -->
                        <td class="py-3 text-secondary small">
                            {{ $ret->return_date->format('d M Y') }}
                        </td>

                        <!-- Units -->
                        <td class="py-3 text-center">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                +{{ $ret->details->sum('quantity') }} unit(s)
                            </span>
                        </td>

                        <!-- Refund -->
                        <td class="py-3 text-end font-monospace fw-bold text-danger fs-6">
                            ${{ number_format($ret->total_amount, 2) }}
                        </td>

                        <!-- Action Buttons (Receipt / Print) -->
                        <td class="pe-3 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('returns.show', $ret) }}" class="btn btn-outline-primary" title="View Receipt">
                                    <i class="bi bi-file-text me-1"></i> View
                                </a>
                                <a href="{{ route('returns.show', $ret) }}" onclick="setTimeout(() => window.print(), 300)" class="btn btn-outline-secondary" title="Print Receipt">
                                    <i class="bi bi-printer me-1"></i> Print
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-arrow-counterclockwise display-6 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold text-dark">No customer bill returns recorded yet.</h6>
                            <p class="small text-secondary mb-3">When merchandise is returned, validate quantities against sales and restore inventory.</p>
                            <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Create First Return
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $returns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
