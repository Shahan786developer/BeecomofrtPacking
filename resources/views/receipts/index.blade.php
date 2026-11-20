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
        @if(Auth::user()->hasPermission('receipts', 'create'))
        <div>
            <a href="{{ route('receipts.create') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-cash-stack me-1"></i> Receive Cash (New CRV)
            </a>
        </div>
        @endif
    </div>

    <!-- SUMMARY METRICS CARDS -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block">
                            Total Cash Collected {{ $isDefaultToday ? '(Today)' : '(Filtered)' }}
                        </span>
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
                        <span class="text-secondary small fw-bold text-uppercase d-block">
                            Total Vouchers Issued {{ $isDefaultToday ? '(Today)' : '(Filtered)' }}
                        </span>
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
                <div class="col-12 {{ Auth::user()->isSuperAdmin() ? 'col-md-3' : 'col-md-4' }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Search by voucher #, customer, or notes..." autocomplete="off">
                    </div>
                </div>

                <div class="col-12 col-sm-6 {{ Auth::user()->isSuperAdmin() ? 'col-md-2' : 'col-md-3' }}">
                    <select name="customer_id" class="form-select">
                        <option value="">All Customers...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->search_dropdown_label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(Auth::user()->isSuperAdmin())
                <div class="col-12 col-sm-6 col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">All Users...</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control" title="From Date">
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control" title="To Date">
                </div>

                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'customer_id', 'user_id', 'from_date', 'to_date', 'all_dates']))
                        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                <div>
                    @if($isDefaultToday)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                            <i class="bi bi-clock-history me-1"></i> Showing Today's Receipts ({{ Carbon\Carbon::today()->format('d M Y') }})
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                            <i class="bi bi-funnel me-1"></i> Custom Date Range Active
                        </span>
                    @endif
                </div>
                <div>
                    @if($isDefaultToday)
                        <a href="{{ route('receipts.index', ['all_dates' => 1]) }}" class="btn btn-sm btn-link text-decoration-none text-secondary py-0">
                            <i class="bi bi-calendar-range me-1"></i> View All Dates History
                        </a>
                    @else
                        <a href="{{ route('receipts.index') }}" class="btn btn-sm btn-link text-decoration-none text-primary py-0">
                            <i class="bi bi-calendar-check me-1"></i> Switch to Today Only
                        </a>
                    @endif
                </div>
            </div>
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
                                @if(Auth::user()->isSuperAdmin() && $r->user)
                                    <span class="badge bg-light text-secondary border font-monospace d-block mt-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-person me-1"></i>{{ $r->user->name }}
                                    </span>
                                @endif
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
                                @if($r->payment_method === 'easypaisa')
                                    <span class="badge bg-success text-white px-2 py-1 text-uppercase small shadow-sm">
                                        <i class="bi bi-phone me-1"></i>EasyPaisa
                                    </span>
                                @elseif($r->payment_method === 'jazzcash')
                                    <span class="badge bg-danger text-white px-2 py-1 text-uppercase small shadow-sm">
                                        <i class="bi bi-wallet me-1"></i>JazzCash
                                    </span>
                                @elseif($r->payment_method === 'cash')
                                    <span class="badge bg-light text-dark border px-2 py-1 text-uppercase small">
                                        <i class="bi bi-cash me-1"></i>Cash
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border px-2 py-1 text-uppercase small">
                                        {{ str_replace('_', ' ', $r->payment_method) }}
                                    </span>
                                @endif
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
                                    @if(Auth::user()->hasPermission('receipts', 'print'))
                                    <a href="{{ route('receipts.print', $r) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Print 52mm POS Thermal Slip">
                                        <i class="bi bi-receipt"></i> 52mm
                                    </a>
                                    <a href="{{ route('receipts.show', $r) }}" class="btn btn-sm btn-outline-dark" title="View & Print Full Voucher">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasPermission('receipts', 'update'))
                                    <a href="{{ route('receipts.edit', $r) }}" class="btn btn-sm btn-outline-primary" title="Edit Voucher">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasPermission('receipts', 'delete'))
                                    <form method="POST" action="{{ route('receipts.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Delete this receipt voucher? Customer balance will increase accordingly.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Voucher">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
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
