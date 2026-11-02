@extends('layouts.app')

@section('title', 'Customer Ledger — ' . $customer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">Customers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">{{ $customer->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ledger &amp; Financial History</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- 1. CUSTOMER PROFILE HEADER & ACTIONS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-uppercase px-2.5 py-1">Customer Account</span>
                        <span class="text-secondary small">
                            <i class="bi bi-calendar3 me-1"></i> Customer Since: <strong>{{ $customer->created_at ? $customer->created_at->format('d M Y') : 'Active' }}</strong>
                        </span>
                        @if($customer->balance_due > 0)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold px-2 py-1">
                                <i class="bi bi-exclamation-circle me-1"></i> Balance Pending
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1">
                                <i class="bi bi-check-circle me-1"></i> Clear Account
                            </span>
                        @endif
                    </div>
                    <h1 class="h3 fw-bold text-dark mb-1">{{ $customer->name }}</h1>
                    @if($customer->owner_name)
                        <div class="text-secondary small mb-2">
                            <i class="bi bi-person-badge text-primary me-1"></i> Owner: <strong class="text-dark">{{ $customer->owner_name }}</strong>
                        </div>
                    @endif
                    
                    <div class="d-flex flex-wrap gap-3 text-secondary small">
                        @if($customer->phone)
                            <span><i class="bi bi-telephone text-primary me-1"></i> Phone 1: <strong class="text-dark">{{ $customer->phone }}</strong></span>
                        @endif
                        @if($customer->phone_2)
                            <span><i class="bi bi-telephone text-secondary me-1"></i> Phone 2: <strong class="text-dark">{{ $customer->phone_2 }}</strong></span>
                        @endif
                        @if($customer->phone_3)
                            <span><i class="bi bi-telephone text-secondary me-1"></i> Phone 3: <strong class="text-dark">{{ $customer->phone_3 }}</strong></span>
                        @endif
                        @if($customer->phone_4)
                            <span><i class="bi bi-telephone text-secondary me-1"></i> Phone 4: <strong class="text-dark">{{ $customer->phone_4 }}</strong></span>
                        @endif
                        @if($customer->email)
                            <span><i class="bi bi-envelope text-primary me-1"></i> {{ $customer->email }}</span>
                        @endif
                        @if($customer->city)
                            <span><i class="bi bi-geo-alt text-primary me-1"></i> City: <strong class="text-dark">{{ $customer->city }}</strong></span>
                        @endif
                        @if($customer->address)
                            <span><i class="bi bi-pin-map text-primary me-1"></i> {{ $customer->address }}</span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons: Receive Cash, New Invoice, Statement Print -->
                <div class="d-flex flex-wrap gap-2 no-print">
                    <a href="{{ route('receipts.create', ['customer_id' => $customer->id, 'redirect_to' => 'customer_ledger']) }}" class="btn btn-success fw-bold shadow-sm">
                        <i class="bi bi-cash-stack me-1"></i> Receive Cash (CRV)
                    </a>
                    <a href="{{ route('sales.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary fw-bold shadow-sm">
                        <i class="bi bi-cart-plus me-1"></i> New Invoice
                    </a>
                    <a href="{{ route('customers.print-statement', ['customer' => $customer->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-outline-dark fw-bold">
                        <i class="bi bi-printer me-1"></i> Print Statement
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. DATE RANGE & CUSTOMER SWITCHER FILTER BAR -->
    <div class="card border-0 shadow-sm no-print">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('customers.ledger', $customer) }}" class="row g-2 align-items-end">
                <!-- Customer Switcher -->
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Switch Customer Account</label>
                    <select name="customer_switch" class="form-select form-select-sm" onchange="if(this.value) window.location.href='/customers/' + this.value + '/ledger'">
                        @foreach($allCustomers as $c)
                            <option value="{{ $c->id }}" {{ $c->id == $customer->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->city ? '(' . $c->city . ')' : '' }} — Due: Rs. {{ number_format($c->balance_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Start Date -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                </div>

                <!-- End Date -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                </div>

                <!-- Buttons -->
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter Ledger
                    </button>
                    @if($startDate || $endDate || request('type') || request('search'))
                        <a href="{{ route('customers.ledger', $customer) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- 3. FINANCIAL SUMMARY METRIC CARDS -->
    <div class="row g-3">
        <!-- Lifetime Invoiced & Total Invoices -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Invoices</span>
                    <h3 class="fw-bold text-dark mb-0 font-monospace fs-4">Rs. {{ number_format($lifetimeInvoiced, 2) }}</h3>
                    <span class="text-secondary fs-7">Total Purchased: {{ $customer->sales()->count() }} bills</span>
                </div>
            </div>
        </div>

        <!-- Cash Received via CRV -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success-subtle border border-success-subtle">
                <div class="card-body p-3">
                    <span class="text-success small fw-bold text-uppercase d-block mb-1">Cash Received (CRV)</span>
                    <h3 class="fw-bold text-success mb-0 font-monospace fs-4">Rs. {{ number_format($lifetimePaid, 2) }}</h3>
                    <span class="text-success-emphasis fs-7">Payments Collected</span>
                </div>
            </div>
        </div>

        <!-- Bill Returns Refunded -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Returns</span>
                    <h3 class="fw-bold text-danger mb-0 font-monospace fs-4">Rs. {{ number_format($lifetimeReturned, 2) }}</h3>
                    <span class="text-danger fs-7">Credit Notes Adjusted</span>
                </div>
            </div>
        </div>

        <!-- Balance Due / Remaining Outstanding -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 {{ $lifetimeBalanceDue > 0 ? 'bg-danger-subtle border border-danger-subtle' : 'bg-light' }}">
                <div class="card-body p-3">
                    <span class="{{ $lifetimeBalanceDue > 0 ? 'text-danger' : 'text-secondary' }} small fw-bold text-uppercase d-block mb-1">Remaining Balance Due</span>
                    <h3 class="fw-black {{ $lifetimeBalanceDue > 0 ? 'text-danger' : 'text-dark' }} mb-0 font-monospace fs-4">
                        Rs. {{ number_format($lifetimeBalanceDue, 2) }}
                    </h3>
                    @if($lifetimeBalanceDue > 0)
                        <span class="text-danger fw-bold fs-7">
                            <i class="bi bi-clock-history me-1"></i> Pending Payment
                        </span>
                    @else
                        <span class="text-success fw-bold fs-7">
                            <i class="bi bi-check-all me-1"></i> All Paid Up
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($startDate || $endDate)
    <!-- Period Specific Summary Banner (When Date Filter is active) -->
    <div class="alert alert-info border-0 shadow-sm py-2 px-3 d-flex flex-wrap justify-content-between align-items-center mb-0">
        <div>
            <i class="bi bi-info-circle me-1"></i>
            Filtered Period: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Start' }}</strong> to <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Today' }}</strong>
            &bull; Opening Balance: <strong>Rs. {{ number_format($openingBalance, 2) }}</strong>
        </div>
        <div>
            Period Debits: <strong class="text-dark">Rs. {{ number_format($rangeDebits, 2) }}</strong> &bull;
            Period Credits: <strong class="text-success">Rs. {{ number_format($rangeCredits, 2) }}</strong> &bull;
            Period Closing: <strong class="text-primary font-monospace">Rs. {{ number_format($closingBalance, 2) }}</strong>
        </div>
    </div>
    @endif

    <!-- 4. SEARCH & TRANSACTION TYPE FILTER PILLS -->
    <div class="card border-0 shadow-sm no-print">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Bar -->
                <div class="col-12 col-md-6 col-lg-7">
                    <form method="GET" action="{{ route('customers.ledger', $customer) }}" class="d-flex gap-2">
                        @if($startDate)<input type="hidden" name="start_date" value="{{ $startDate }}">@endif
                        @if($endDate)<input type="hidden" name="end_date" value="{{ $endDate }}">@endif
                        @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                        
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-secondary">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" 
                                placeholder="Search by voucher #, invoice #, CRV, note, product..." 
                                class="form-control border-start-0 ps-0" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">Search</button>
                        @if(!empty($search))
                            <a href="{{ route('customers.ledger', array_merge(['customer' => $customer->id], request()->except('search'))) }}" class="btn btn-outline-secondary" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Transaction Type Filter Pills -->
                <div class="col-12 col-md-6 col-lg-5 d-flex justify-content-md-end gap-1 flex-wrap">
                    @php
                        $baseParams = ['customer' => $customer->id];
                        if($startDate) $baseParams['start_date'] = $startDate;
                        if($endDate) $baseParams['end_date'] = $endDate;
                        if($search) $baseParams['search'] = $search;
                    @endphp
                    <a href="{{ route('customers.ledger', $baseParams) }}" 
                        class="btn btn-sm {{ empty($typeFilter) ? 'btn-primary' : 'btn-outline-secondary' }} fw-bold">
                        All ({{ $allTransactions->count() }})
                    </a>
                    <a href="{{ route('customers.ledger', array_merge($baseParams, ['type' => 'sale'])) }}" 
                        class="btn btn-sm {{ $typeFilter === 'sale' ? 'btn-primary' : 'btn-outline-secondary' }} fw-bold">
                        Invoices ({{ $allTransactions->where('type', 'sale')->count() }})
                    </a>
                    <a href="{{ route('customers.ledger', array_merge($baseParams, ['type' => 'receipt'])) }}" 
                        class="btn btn-sm {{ $typeFilter === 'receipt' ? 'btn-success' : 'btn-outline-secondary' }} fw-bold">
                        CRV ({{ $allTransactions->where('type', 'receipt')->count() }})
                    </a>
                    <a href="{{ route('customers.ledger', array_merge($baseParams, ['type' => 'return'])) }}" 
                        class="btn btn-sm {{ $typeFilter === 'return' ? 'btn-danger' : 'btn-outline-secondary' }} fw-bold">
                        Returns ({{ $allTransactions->where('type', 'return')->count() }})
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. COMPLETE CHRONOLOGICAL LEDGER TABLE -->
    <div id="table-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Customer Transaction History &amp; Ledger</h5>
                    <span class="text-secondary small">Comprehensive audit log of Invoices (Debits), Payments (Credits via CRV), and Returns with Running Balance.</span>
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1.5 font-monospace">
                    {{ $filteredTransactions->count() }} Entries
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 15%;">Date &amp; Time</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 12%;">Type</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 15%;">Voucher #</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 25%;">Details / Merchandise</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 11%;">Debit (Sale)</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 11%;">Credit (Paid/Ret)</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 12%;">Running Balance</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7 no-print" style="width: 8%;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @if($openingBalance > 0 && $startDate)
                        <tr class="table-light">
                            <td class="ps-4 py-3 font-monospace small">
                                <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-secondary text-uppercase fw-bold px-2 py-1">Opening</span>
                            </td>
                            <td class="py-3 font-monospace text-muted">—</td>
                            <td class="py-3 text-dark fw-semibold">Balance Brought Forward</td>
                            <td class="py-3 text-end font-monospace text-muted">—</td>
                            <td class="py-3 text-end font-monospace text-muted">—</td>
                            <td class="py-3 text-end font-monospace fw-bold text-dark">
                                Rs. {{ number_format($openingBalance, 2) }}
                            </td>
                            <td class="pe-4 py-3 text-end no-print">—</td>
                        </tr>
                        @endif

                        @forelse($filteredTransactions as $tx)
                        <tr>
                            <!-- Date & Time -->
                            <td class="ps-4 py-3 font-monospace small">
                                <strong class="text-dark d-block">{{ $tx['date_time']->format('d M Y') }}</strong>
                                <span class="text-secondary fs-7"><i class="bi bi-clock me-1"></i>{{ $tx['date_time']->format('h:i A') }}</span>
                            </td>

                            <!-- Type Badge -->
                            <td class="py-3">
                                <span class="badge {{ $tx['badge_class'] }} text-uppercase fw-bold px-2 py-1">
                                    {{ $tx['type_label'] }}
                                </span>
                            </td>

                            <!-- Voucher / Doc No -->
                            <td class="py-3">
                                <a href="{{ $tx['view_url'] }}" class="font-monospace fw-bold text-decoration-none {{ $tx['type'] === 'sale' ? 'text-primary' : ($tx['type'] === 'return' ? 'text-danger' : 'text-success') }}">
                                    {{ $tx['doc_no'] }}
                                </a>
                                @if(!empty($tx['related_doc_no']))
                                    <div class="text-secondary fs-7 mt-0.5">
                                        Ref: <span class="font-monospace">{{ $tx['related_doc_no'] }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Details / Items / Notes -->
                            <td class="py-3">
                                @if($tx['type'] === 'sale')
                                    @if(!empty($tx['items']) && count($tx['items']) > 0)
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($tx['items'] as $item)
                                                <li class="text-dark">
                                                    <strong>{{ $item['name'] }}</strong>
                                                    <span class="text-secondary"> &bull; {{ $item['quantity'] }} pcs @ Rs. {{ number_format($item['unit_price'], 2) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-secondary small">Sale Invoice</span>
                                    @endif
                                @elseif($tx['type'] === 'return')
                                    @if(!empty($tx['items']) && count($tx['items']) > 0)
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($tx['items'] as $item)
                                                <li class="text-danger">
                                                    <strong>{{ $item['name'] }}</strong>
                                                    <span class="text-secondary"> &bull; {{ $item['quantity'] }} pcs returned</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-secondary small">Merchandise Return</span>
                                    @endif
                                @else
                                    <!-- CRV Receipt -->
                                    <span class="fw-semibold text-success"><i class="bi bi-cash me-1"></i>Payment Received</span>
                                @endif

                                @if(!empty($tx['notes']))
                                    <div class="text-secondary small fst-italic mt-1">
                                        <i class="bi bi-info-circle me-1"></i> {{ $tx['notes'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Debit -->
                            <td class="py-3 text-end font-monospace fw-bold {{ $tx['debit'] > 0 ? 'text-dark' : 'text-muted' }}">
                                {{ $tx['debit'] > 0 ? 'Rs. ' . number_format($tx['debit'], 2) : '—' }}
                            </td>

                            <!-- Credit -->
                            <td class="py-3 text-end font-monospace fw-bold {{ $tx['credit'] > 0 ? ($tx['type'] === 'receipt' ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                {{ $tx['credit'] > 0 ? 'Rs. ' . number_format($tx['credit'], 2) : '—' }}
                            </td>

                            <!-- Running Balance -->
                            <td class="py-3 text-end font-monospace fw-bold {{ $tx['running_balance'] > 0 ? 'text-primary' : 'text-secondary' }}">
                                Rs. {{ number_format($tx['running_balance'], 2) }}
                            </td>

                            <!-- Actions -->
                            <td class="pe-4 py-3 text-end no-print">
                                <a href="{{ $tx['view_url'] }}" class="btn btn-sm btn-outline-info" title="View Document">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
                                <h6 class="fw-bold text-dark">No transaction records found.</h6>
                                <p class="small text-secondary mb-3">Sales, returns, and cash receipts for this customer will automatically appear here.</p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('sales.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cart-plus me-1"></i> Create Sale
                                    </a>
                                    <a href="{{ route('receipts.create', ['customer_id' => $customer->id]) }}" class="btn btn-success btn-sm">
                                        <i class="bi bi-cash-stack me-1"></i> Receive Cash
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($filteredTransactions->count() > 0)
                    <tfoot class="table-light border-top">
                        <tr class="fw-bold">
                            <td colspan="4" class="ps-4 py-3 text-uppercase text-secondary fs-7">
                                Lifetime Account Total
                            </td>
                            <td class="py-3 text-end font-monospace text-dark">
                                Rs. {{ number_format($lifetimeInvoiced, 2) }}
                            </td>
                            <td class="py-3 text-end font-monospace text-success">
                                Rs. {{ number_format($lifetimePaid + $lifetimeReturned, 2) }}
                            </td>
                            <td class="py-3 text-end font-monospace text-danger">
                                Due: Rs. {{ number_format($lifetimeBalanceDue, 2) }}
                            </td>
                            <td class="pe-4 py-3 text-end no-print"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
