@extends('layouts.app')

@section('title', 'Customer Default List (Credit Recovery)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none text-secondary">Customers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Default List</li>
@endsection

@push('styles')
<style>
    @media print {
        .no-print, .top-navbar, .sidebar, .btn, .card-header-actions, .nav-tabs {
            display: none !important;
        }
        .main-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .print-header {
            display: block !important;
        }
        .table {
            font-size: 11px !important;
        }
    }
    .print-header {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Header & Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h4 fw-bold text-dark mb-0">Customer Default List</h1>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 font-monospace">
                    {{ $stats['all']['count'] }} Defaulter Accounts
                </span>
            </div>
            <p class="text-secondary small mb-0 mt-1">
                Field credit recovery tracking for sales team. Customers automatically listed once their previous balance exceeds 7, 15, or 30 days.
            </p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button type="button" onclick="window.print()" class="btn btn-outline-dark shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Recovery Sheet
            </button>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-people me-1"></i> Customer Accounts
            </a>
        </div>
    </div>

    <!-- Printable Header (Visible Only In Print) -->
    <div class="print-header mb-3 border-bottom pb-2">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0">BEE COMFORT — CUSTOMER DEFAULT &amp; CREDIT RECOVERY LIST</h3>
                <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }} | Tab: {{ strtoupper($tab) }} DAYS</p>
            </div>
            <div class="text-end">
                <div class="fw-bold text-danger">Total Overdue: Rs. {{ number_format($stats[$tab]['total_amount'] ?? $stats['all']['total_amount'], 2) }}</div>
                <div class="small text-muted">Total Defaulters: {{ $stats[$tab]['count'] ?? $stats['all']['count'] }}</div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3">
        <!-- All Overdue Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-bold text-uppercase">Total Defaulters</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1">{{ $stats['all']['count'] }}</h3>
                            <div class="text-muted small mt-1">
                                Overdue: <strong class="text-dark">Rs. {{ number_format($stats['all']['total_amount'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-exclamation-octagon fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7 Days Overdue Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100 {{ $tab === '7' ? 'ring-2 ring-warning' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-bold text-uppercase">7 Days Defaulters</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1">{{ $stats['7']['count'] }}</h3>
                            <div class="text-muted small mt-1">
                                Overdue: <strong class="text-dark">Rs. {{ number_format($stats['7']['total_amount'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 15 Days Overdue Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100 {{ $tab === '15' ? 'ring-2 ring-primary' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-bold text-uppercase">15 Days Defaulters</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $stats['15']['count'] }}</h3>
                            <div class="text-muted small mt-1">
                                Overdue: <strong class="text-dark">Rs. {{ number_format($stats['15']['total_amount'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar2-week fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30 Days Overdue Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100 {{ $tab === '30' ? 'ring-2 ring-info' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-bold text-uppercase">30 Days Defaulters</span>
                            <h3 class="fw-bold text-info mb-0 mt-1">{{ $stats['30']['count'] }}</h3>
                            <div class="text-muted small mt-1">
                                Overdue: <strong class="text-dark">Rs. {{ number_format($stats['30']['total_amount'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar-month fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs & Search Filter Card -->
    <div class="card border-0 shadow-sm no-print">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <!-- Navigation Tabs: All, 7 Days, 15 Days, 30 Days -->
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link py-1.5 px-3 fw-semibold {{ $tab === 'all' ? 'active bg-danger shadow-sm' : 'text-dark' }}" 
                           href="{{ route('customers.default-list', ['tab' => 'all', 'search' => $search]) }}">
                            <i class="bi bi-list-check me-1"></i> All Defaulters
                            <span class="badge {{ $tab === 'all' ? 'bg-white text-danger' : 'bg-light text-secondary border' }} ms-1">
                                {{ $stats['all']['count'] }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1.5 px-3 fw-semibold {{ $tab === '7' ? 'active bg-warning text-dark shadow-sm' : 'text-dark' }}" 
                           href="{{ route('customers.default-list', ['tab' => '7', 'search' => $search]) }}">
                            <i class="bi bi-clock-history me-1"></i> 7 Days Recovery
                            <span class="badge {{ $tab === '7' ? 'bg-dark text-white' : 'bg-light text-secondary border' }} ms-1">
                                {{ $stats['7']['count'] }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1.5 px-3 fw-semibold {{ $tab === '15' ? 'active bg-primary shadow-sm' : 'text-dark' }}" 
                           href="{{ route('customers.default-list', ['tab' => '15', 'search' => $search]) }}">
                            <i class="bi bi-calendar2-week me-1"></i> 15 Days Recovery
                            <span class="badge {{ $tab === '15' ? 'bg-white text-primary' : 'bg-light text-secondary border' }} ms-1">
                                {{ $stats['15']['count'] }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1.5 px-3 fw-semibold {{ $tab === '30' ? 'active bg-info text-white shadow-sm' : 'text-dark' }}" 
                           href="{{ route('customers.default-list', ['tab' => '30', 'search' => $search]) }}">
                            <i class="bi bi-calendar-month me-1"></i> 30 Days Recovery
                            <span class="badge {{ $tab === '30' ? 'bg-white text-info' : 'bg-light text-secondary border' }} ms-1">
                                {{ $stats['30']['count'] }}
                            </span>
                        </a>
                    </li>
                </ul>

                <!-- Search Input -->
                <form method="GET" action="{{ route('customers.default-list') }}" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Search customer, owner, phone, city..." style="min-width: 240px;">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('customers.default-list', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Defaulters Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation text-danger fs-5"></i>
                <h6 class="mb-0 fw-bold text-dark">
                    @if($tab === '7')
                        7 Days Overdue Customer Accounts
                    @elseif($tab === '15')
                        15 Days Overdue Customer Accounts
                    @elseif($tab === '30')
                        30 Days Overdue Customer Accounts
                    @else
                        All Overdue Customer Accounts
                    @endif
                </h6>
                <span class="badge bg-secondary-subtle text-secondary small ms-1">{{ $filteredDefaulters->count() }} records</span>
            </div>
            <div class="small text-muted">
                Showing customers whose previous balance remains unpaid past their allowed credit duration
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7" style="width: 3%;">#</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 22%;">Customer / Business</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 14%;">Owner Name</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 18%;">Contact Phone(s)</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 8%;">Credit Term</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 11%;">Oldest Bill Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 10%;">Overdue Time</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 12%;">Balance Due</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7 no-print" style="width: 12%;">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($filteredDefaulters as $index => $item)
                        @php
                            $c = $item['customer'];
                            $info = $item['info'];
                        @endphp
                        <tr>
                            <td class="ps-3 py-3 text-muted small font-monospace">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3">
                                <a href="{{ route('customers.ledger', $c) }}" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                    {{ $c->name }}
                                    <span class="badge bg-light text-secondary border font-monospace ms-1">{{ $c->formatted_code }}</span>
                                </a>
                                @if($c->city)
                                    <span class="small text-muted d-block mt-0.5">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $c->city }}
                                        @if($c->address) &bull; {{ Str::limit($c->address, 30) }} @endif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($c->owner_name)
                                    <span class="fw-semibold text-dark d-block">
                                        <i class="bi bi-person text-secondary me-1"></i>{{ $c->owner_name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3 font-monospace small">
                                <div class="d-flex flex-column gap-1">
                                    @if($c->phone)
                                        <div><span class="badge bg-light text-secondary border me-1">P1</span><strong class="text-dark">{{ $c->phone }}</strong></div>
                                    @endif
                                    @if($c->phone_2)
                                        <div><span class="badge bg-light text-secondary border me-1">P2</span>{{ $c->phone_2 }}</div>
                                    @endif
                                    @if($c->phone_3)
                                        <div><span class="badge bg-light text-secondary border me-1">P3</span>{{ $c->phone_3 }}</div>
                                    @endif
                                    @if($c->phone_4)
                                        <div><span class="badge bg-light text-secondary border me-1">P4</span>{{ $c->phone_4 }}</div>
                                    @endif
                                    @if(!$c->phone && !$c->phone_2 && !$c->phone_3 && !$c->phone_4)
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                @if($item['credit_days'] == 7)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-1">
                                        7 Days
                                    </span>
                                @elseif($item['credit_days'] == 15)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                        15 Days
                                    </span>
                                @elseif($item['credit_days'] == 30)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-1">
                                        30 Days
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                        {{ $item['credit_days'] }} Days
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 small">
                                <div class="fw-bold text-dark">
                                    {{ $info['invoice_date']->format('d M Y') }}
                                </div>
                                <div class="text-muted font-monospace fs-7">
                                    {{ $info['invoice_no'] }}
                                </div>
                                @if($info['due_date'])
                                    <div class="text-danger fs-7 mt-0.5">
                                        Due: {{ $info['due_date']->format('d M Y') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-danger text-white px-2 py-1 d-block mb-1 font-monospace">
                                    {{ $item['overdue_days'] }} {{ Str::plural('Day', $item['overdue_days']) }} Overdue
                                </span>
                                <span class="text-muted small fs-7 d-block">
                                    ({{ $item['days_elapsed'] }} days elapsed)
                                </span>
                            </td>
                            <td class="py-3 text-end">
                                <div class="fs-6 fw-bold text-danger font-monospace">
                                    Rs. {{ number_format($item['balance_due'], 2) }}
                                </div>
                                @if($info['unpaid_portion'] < $item['balance_due'])
                                    <div class="text-muted fs-7">
                                        Oldest Bill: Rs. {{ number_format($info['unpaid_portion'], 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="pe-3 py-3 text-end no-print">
                                <div class="d-inline-flex gap-1">
                                    <!-- Ledger Button -->
                                    <a href="{{ route('customers.ledger', $c) }}" class="btn btn-sm btn-outline-info" title="View Customer History & Ledger">
                                        <i class="bi bi-journal-text me-1"></i> Ledger
                                    </a>
                                    <!-- Receive Cash Voucher Button -->
                                    <a href="{{ route('receipts.create', ['customer_id' => $c->id]) }}" class="btn btn-sm btn-outline-success" title="Receive Cash Voucher (CRV)">
                                        <i class="bi bi-cash-coin"></i>
                                    </a>
                                    <!-- Statement Button -->
                                    <a href="{{ route('customers.print-statement', $c) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print Customer Statement">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-secondary">
                                <div class="py-4">
                                    <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                    <h5 class="fw-bold text-dark">No Defaulter Customers Found!</h5>
                                    <p class="text-muted small mb-0">
                                        @if($tab !== 'all')
                                            No customers with {{ $tab }} days terms have overdue balances matching your criteria.
                                        @else
                                            All customers have either cleared their balance (0 balance) or are well within their allowed credit days.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($filteredDefaulters->isNotEmpty())
                <tfoot class="table-light border-top">
                    <tr class="fw-bold">
                        <td colspan="7" class="text-end py-3 text-uppercase fs-7 text-secondary">
                            Total Overdue Balance ({{ $filteredDefaulters->count() }} Customers):
                        </td>
                        <td class="text-end py-3 fs-6 text-danger font-monospace">
                            Rs. {{ number_format($filteredDefaulters->sum('balance_due'), 2) }}
                        </td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
