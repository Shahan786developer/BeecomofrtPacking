@extends('layouts.app')

@section('title', 'Journal Vouchers (JV)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Journal Vouchers</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Journal Vouchers (JV)</h1>
            <p class="text-secondary small mb-0">Debit and credit account adjustment vouchers, owner capital, freight, and customer ledger entries.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vouchers.create') }}" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> New Journal Voucher (JV)
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Debit Adjustments (+)</span>
                    <h3 class="fw-bold text-dark mb-0 font-monospace fs-4">Rs. {{ number_format($totalDebits, 2) }}</h3>
                    <span class="text-muted fs-7">Increases customer balance / receivables</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Credit Adjustments (-)</span>
                    <h3 class="fw-bold text-info mb-0 font-monospace fs-4">Rs. {{ number_format($totalCredits, 2) }}</h3>
                    <span class="text-muted fs-7">Decreases customer balance / adjustments</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Vouchers</span>
                    <h3 class="fw-bold text-primary mb-0 font-monospace fs-4">{{ $allCount }}</h3>
                    <span class="text-muted fs-7">Net adjustment: Rs. {{ number_format($netAdjustment, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('vouchers.index') }}" class="row g-2 align-items-center">
                <!-- Search -->
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Voucher #, customer, owner, notes...">
                    </div>
                </div>

                <!-- Customer Filter -->
                <div class="col-6 col-md-2">
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">All Accounts...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types (Debit &amp; Credit)</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit (+)</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit (-)</option>
                    </select>
                </div>

                <!-- From Date -->
                <div class="col-6 col-md-2">
                    <input type="date" name="from_date" value="{{ $fromDate ?? '' }}" class="form-control form-control-sm" title="From Date">
                </div>

                <!-- To Date -->
                <div class="col-6 col-md-2">
                    <input type="date" name="to_date" value="{{ $toDate ?? '' }}" class="form-control form-control-sm" title="To Date">
                </div>

                <!-- Action Buttons -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">Filter Vouchers</button>
                    @if(request()->anyFilled(['search', 'customer_id', 'type', 'from_date', 'to_date']))
                        <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table List -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7" style="width: 14%;">Voucher #</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 25%;">Account / Customer</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 12%;">Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 10%;">Type</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 14%;">Amount</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 15%;">Reference / Notes</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($vouchers as $v)
                    <tr>
                        <td class="ps-3 py-3">
                            <a href="{{ route('vouchers.show', $v) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                {{ $v->voucher_no }}
                            </a>
                            @if($v->reference_no)
                                <div class="small text-muted font-monospace fs-7">Ref: {{ $v->reference_no }}</div>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($v->customer)
                                <a href="{{ route('customers.ledger', $v->customer) }}" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                    {{ $v->customer->name }}
                                </a>
                                @if($v->customer->owner_name)
                                    <span class="small text-muted d-block fs-7">Owner: {{ $v->customer->owner_name }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">General / Owner Capital</span>
                            @endif
                        </td>
                        <td class="py-3 small font-monospace text-dark">
                            {{ $v->voucher_date->format('d M Y') }}
                        </td>
                        <td class="py-3 text-center">
                            @if($v->type === 'debit')
                                <span class="badge bg-warning-subtle text-dark border border-warning px-2.5 py-1 font-monospace">
                                    DEBIT (+)
                                </span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis border border-info px-2.5 py-1 font-monospace">
                                    CREDIT (-)
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-end font-monospace fw-bold {{ $v->type === 'debit' ? 'text-dark' : 'text-info' }}">
                            Rs. {{ number_format($v->amount, 2) }}
                        </td>
                        <td class="py-3 small text-secondary">
                            {{ Str::limit($v->notes ?: '—', 45) }}
                        </td>
                        <td class="pe-3 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('vouchers.print', $v) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Print JV Voucher">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('vouchers.show', $v) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('vouchers.edit', $v) }}" class="btn btn-sm btn-outline-primary" title="Edit Voucher">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('vouchers.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Journal Voucher?');">
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
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
                            No Journal Vouchers found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
