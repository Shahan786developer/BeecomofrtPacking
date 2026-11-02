@extends('layouts.app')

@section('title', 'Cash Receipt Vouchers (CRV)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Cash Receipts</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Cash Receipt Vouchers (CRV)</h1>
            <p class="text-secondary small mb-0">Record payments received from customers and update outstanding credit ledger balances.</p>
        </div>
        <div>
            <a href="{{ route('receipts.create') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-cash-stack me-1"></i> Receive Cash (New CRV)
            </a>
        </div>
    </div>

    <!-- SUMMARY METRICS CARDS -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Total Cash Collected</span>
                        <div class="h3 fw-black text-success mb-0 font-monospace">Rs. {{ number_format($totalCollected, 2) }}</div>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-3">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Total Vouchers Issued</span>
                        <div class="h3 fw-black text-dark mb-0 font-monospace">{{ $receipts->total() }}</div>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3">
                        <i class="bi bi-receipt-cutoff fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">Quick Actions</span>
                        <div class="d-flex gap-2 mt-1">
                            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">Customer Ledger</a>
                            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">Sales Bills</a>
                        </div>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-3">
                        <i class="bi bi-journal-text fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTERS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('receipts.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Search by voucher #, customer, owner, or notes..." autocomplete="off">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <select name="customer_id" class="form-select">
                        <option value="">All Customers...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->owner_name ? '('.$c->owner_name.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" title="From Date">
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" title="To Date">
                </div>

                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'customer_id', 'from_date', 'to_date']))
                        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- RECEIPTS TABLE -->
    <div id="table-container">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Voucher No</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Customer &amp; Owner</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Payment Date</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Method</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Amount Received</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Description / Remarks</th>
                            <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($receipts as $r)
                        <tr>
                            <!-- Voucher No -->
                            <td class="ps-3 py-3">
                                <a href="{{ route('receipts.show', $r) }}" class="fw-bold text-success font-monospace text-decoration-none fs-6">
                                    {{ $r->voucher_no }}
                                </a>
                            </td>

                            <!-- Customer Details -->
                            <td class="py-3">
                                <a href="{{ route('customers.ledger', $r->customer_id) }}" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                    {{ $r->customer->name }} <i class="bi bi-journal-text text-primary small ms-1"></i>
                                </a>
                                @if($r->customer->owner_name)
                                    <span class="small text-secondary">Owner: <strong class="text-dark">{{ $r->customer->owner_name }}</strong></span>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="py-3 font-monospace small text-secondary">
                                {{ $r->payment_date->format('d M Y') }}
                            </td>

                            <!-- Method -->
                            <td class="py-3">
                                <span class="badge bg-light text-dark border px-2 py-1 text-uppercase small">
                                    {{ str_replace('_', ' ', $r->payment_method) }}
                                </span>
                            </td>

                            <!-- Amount -->
                            <td class="py-3 text-end">
                                <span class="fw-black text-success font-monospace fs-6">
                                    Rs. {{ number_format($r->amount, 2) }}
                                </span>
                            </td>

                            <!-- Notes -->
                            <td class="py-3 small text-secondary" style="max-width: 200px;">
                                {{ $r->notes ?: '—' }}
                            </td>

                            <!-- Actions -->
                            <td class="pe-3 py-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('receipts.show', $r) }}" class="btn btn-sm btn-outline-dark" title="Print Receipt Voucher">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="{{ route('receipts.edit', $r) }}" class="btn btn-sm btn-outline-primary" title="Edit Voucher">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('receipts.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Delete this receipt voucher? Customer balance will increase accordingly.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Voucher">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-cash-stack display-6 text-muted d-block mb-2"></i>
                                <h6 class="fw-bold text-dark">No cash receipt vouchers found.</h6>
                                <p class="small text-secondary mb-3">Record payments received from customers to balance their ledgers.</p>
                                <a href="{{ route('receipts.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Receive First Payment
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receipts->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $receipts->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
