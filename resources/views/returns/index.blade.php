@extends('layouts.app')

@section('title', 'Bill Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Bill Returns</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Customer Bill Returns</h1>
            <p class="text-secondary small mb-0">Process customer merchandise returns, validate quantities against invoices, and restore inventory.</p>
        </div>
        <div>
            <a href="{{ route('returns.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Process New Return
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('returns.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Search Return / Invoice</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Return #, Invoice #, or customer..." class="form-control border-start-0 ps-0">
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
                        <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Returns Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Return No</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Original Invoice</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Customer</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Return Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Units Returned</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Refund Amount</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($returns as $ret)
                    <tr>
                        <td class="ps-4 py-3">
                            <a href="{{ route('returns.show', $ret) }}" class="fw-bold text-danger text-decoration-none font-monospace fs-6">
                                {{ $ret->return_no }}
                            </a>
                        </td>
                        <td class="py-3">
                            <a href="{{ route('sales.show', $ret->sale) }}" class="text-primary fw-bold text-decoration-none font-monospace">
                                {{ $ret->sale->invoice_no }}
                            </a>
                        </td>
                        <td class="py-3 fw-bold text-dark">
                            {{ $ret->customer->name }}
                        </td>
                        <td class="py-3 text-secondary">
                            {{ $ret->return_date->format('d M Y') }}
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                +{{ $ret->details->sum('quantity') }} unit(s)
                            </span>
                        </td>
                        <td class="py-3 text-end">
                            <span class="fw-bold text-danger font-monospace fs-6">${{ number_format($ret->total_amount, 2) }}</span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="{{ route('returns.show', $ret) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-text me-1"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-arrow-counterclockwise text-secondary display-6 d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">No customer bill returns recorded yet</h6>
                            <p class="small text-secondary mb-3">When merchandise is returned, process it against the original sales invoice.</p>
                            <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Process First Return
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
