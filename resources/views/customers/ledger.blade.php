@extends('layouts.app')

@section('title', 'Customer Ledger — ' . $customer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">Customers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">{{ $customer->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ledger &amp; Purchase History</li>
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
                    </div>
                    <h1 class="h3 fw-bold text-dark mb-2">{{ $customer->name }}</h1>
                    
                    <div class="d-flex flex-wrap gap-3 text-secondary small">
                        @if($customer->phone)
                            <span><i class="bi bi-telephone text-primary me-1"></i> <strong class="text-dark">{{ $customer->phone }}</strong></span>
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

                <div class="d-flex flex-wrap gap-2 no-print">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> All Customers
                    </a>
                    <a href="{{ route('sales.create') }}" class="btn btn-primary fw-bold shadow-sm">
                        <i class="bi bi-cart-plus me-1"></i> New Invoice
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-outline-dark fw-bold">
                        <i class="bi bi-printer me-1"></i> Print Statement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. HIGH-LEVEL SUMMARY METRIC CARDS (Requirement: total qty, return qty, amounts above cards) -->
    <div class="row g-3">
        <!-- Total Invoices -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Invoices</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $totalInvoicesCount }}</h3>
                    <span class="text-secondary fs-7">Sales Bills Created</span>
                </div>
            </div>
        </div>

        <!-- Total Units Purchased -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Purchased</span>
                    <h3 class="fw-bold text-primary mb-0">{{ number_format($totalPurchasedQty) }} <small class="fs-6 fw-normal text-secondary">units</small></h3>
                    <span class="text-secondary fs-7">Gross Items Billed</span>
                </div>
            </div>
        </div>

        <!-- Total Invoiced Amount -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Invoiced</span>
                    <h3 class="fw-black text-dark mb-0 font-monospace fs-4">Rs. {{ number_format($totalInvoicedAmount, 2) }}</h3>
                    <span class="text-secondary fs-7">Gross Billing Value</span>
                </div>
            </div>
        </div>

        <!-- Total Return Quantity & Count -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Returns</span>
                    <h3 class="fw-bold text-danger mb-0">{{ number_format($totalReturnedQty) }} <small class="fs-6 fw-normal text-secondary">units</small></h3>
                    <span class="text-danger fs-7">{{ $totalReturnsCount }} Return Voucher(s)</span>
                </div>
            </div>
        </div>

        <!-- Total Refund Amount -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Refunded</span>
                    <h3 class="fw-black text-danger mb-0 font-monospace fs-4">Rs. {{ number_format($totalRefundedAmount, 2) }}</h3>
                    <span class="text-danger fs-7">Credit Notes Issued</span>
                </div>
            </div>
        </div>

        <!-- Net Purchased / Balance -->
        <div class="col-12 col-md-6 col-xl">
            <div class="card border-0 shadow-sm h-100 bg-primary-subtle border border-primary-subtle">
                <div class="card-body p-3">
                    <span class="text-primary small fw-bold text-uppercase d-block mb-1">Net Customer Spend</span>
                    <h3 class="fw-black text-primary mb-0 font-monospace fs-4">Rs. {{ number_format($netBalance, 2) }}</h3>
                    <span class="text-primary-emphasis fs-7 fw-semibold">Net Retained: {{ number_format($netUnitsKept) }} units</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. CONTROLS: LIVE SEARCH & TYPE FILTER -->
    <div class="card border-0 shadow-sm no-print">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Bar -->
                <div class="col-12 col-md-7 col-lg-8">
                    <form method="GET" action="{{ route('customers.ledger', $customer) }}" class="d-flex gap-2">
                        @if(request('type'))
                            <input type="hidden" name="type" value="{{ request('type') }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-secondary">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" 
                                placeholder="Search by invoice #, return #, product, or date..." 
                                class="form-control border-start-0 ps-0" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">Search</button>
                        <a href="{{ route('customers.ledger', $customer) }}" class="btn btn-outline-secondary btn-clear-search {{ !empty($search) ? '' : 'd-none' }}" title="Clear Search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </form>
                </div>

                <!-- Transaction Type Filter Pills -->
                <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-md-end gap-1">
                    <a href="{{ route('customers.ledger', ['customer' => $customer, 'search' => request('search')]) }}" 
                        class="btn btn-sm {{ empty($typeFilter) ? 'btn-primary' : 'btn-outline-secondary' }} fw-bold">
                        All ({{ $allTransactions->count() }})
                    </a>
                    <a href="{{ route('customers.ledger', ['customer' => $customer, 'type' => 'sale', 'search' => request('search')]) }}" 
                        class="btn btn-sm {{ $typeFilter === 'sale' ? 'btn-primary' : 'btn-outline-secondary' }} fw-bold">
                        Invoices ({{ $totalInvoicesCount }})
                    </a>
                    <a href="{{ route('customers.ledger', ['customer' => $customer, 'type' => 'return', 'search' => request('search')]) }}" 
                        class="btn btn-sm {{ $typeFilter === 'return' ? 'btn-danger' : 'btn-outline-secondary' }} fw-bold">
                        Returns ({{ $totalReturnsCount }})
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. COMPLETE CHRONOLOGICAL LEDGER & HISTORY TABLE -->
    <div id="table-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Customer Transaction History &amp; Ledger</h5>
                    <span class="text-secondary small">Comprehensive chronologically ordered audit log of all bills, returns, and item changes.</span>
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1.5 font-monospace">
                    {{ $filteredTransactions->count() }} Entries
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 17%;">Date &amp; Time</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 12%;">Type</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 16%;">Voucher #</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 25%;">Purchased / Returned Merchandise</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 8%;">Quantity</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 11%;">Debit (Rs.)</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7" style="width: 11%;">Credit (Rs.)</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7 no-print" style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($filteredTransactions as $tx)
                        <tr>
                            <!-- Date & Exact Time -->
                            <td class="ps-4 py-3 font-monospace small">
                                <strong class="text-dark d-block">{{ $tx['date_time']->format('d M Y') }}</strong>
                                <span class="text-secondary fs-7"><i class="bi bi-clock me-1"></i>{{ $tx['date_time']->format('h:i:s A') }}</span>
                            </td>

                            <!-- Transaction Type Badge -->
                            <td class="py-3">
                                <span class="badge {{ $tx['badge_class'] }} text-uppercase fw-bold px-2 py-1">
                                    {{ $tx['type_label'] }}
                                </span>
                            </td>

                            <!-- Document / Voucher No -->
                            <td class="py-3">
                                <a href="{{ $tx['view_url'] }}" class="font-monospace fw-bold text-decoration-none {{ $tx['type'] === 'sale' ? 'text-primary' : 'text-danger' }}">
                                    {{ $tx['doc_no'] }}
                                </a>
                                @if(!empty($tx['related_doc_no']))
                                    <div class="text-secondary fs-7 mt-0.5">
                                        Ref: <span class="font-monospace">{{ $tx['related_doc_no'] }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Merchandise Items Summary -->
                            <td class="py-3">
                                @if(!empty($tx['items']) && count($tx['items']) > 0)
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach($tx['items'] as $item)
                                            <li class="text-dark">
                                                <strong class="text-dark">{{ $item['name'] }}</strong>
                                                <span class="text-secondary"> &bull; {{ $item['quantity'] }} pcs @ Rs. {{ number_format($item['unit_price'], 2) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-secondary small">No item details recorded.</span>
                                @endif

                                @if(!empty($tx['notes']))
                                    <div class="text-secondary small fst-italic mt-1">
                                        <i class="bi bi-info-circle me-1"></i> {{ $tx['notes'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Total Units -->
                            <td class="py-3 text-center">
                                <span class="badge {{ $tx['type'] === 'sale' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} font-monospace px-2 py-1">
                                    {{ $tx['quantity_formatted'] }} pcs
                                </span>
                            </td>

                            <!-- Debit (Sale Billed) -->
                            <td class="py-3 text-end font-monospace fw-bold {{ $tx['debit'] > 0 ? 'text-dark' : 'text-muted' }}">
                                {{ $tx['debit'] > 0 ? 'Rs. ' . number_format($tx['debit'], 2) : '—' }}
                            </td>

                            <!-- Credit (Return Refunded) -->
                            <td class="py-3 text-end font-monospace fw-bold {{ $tx['credit'] > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $tx['credit'] > 0 ? 'Rs. ' . number_format($tx['credit'], 2) : '—' }}
                            </td>

                            <!-- Actions -->
                            <td class="pe-4 py-3 text-end no-print">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ $tx['view_url'] }}" class="btn btn-sm btn-outline-info" title="View Document">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
                                <h6 class="fw-bold text-dark">No transaction records found for this customer.</h6>
                                <p class="small text-secondary mb-3">Invoices and returns generated for this customer will automatically appear here with full date, time, and quantity audit.</p>
                                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus me-1"></i> Create First Sale
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($filteredTransactions->count() > 0)
                    <tfoot class="table-light border-top">
                        <tr class="fw-bold">
                            <td colspan="4" class="ps-4 py-3 text-uppercase text-secondary fs-7">
                                Account Total Summary
                            </td>
                            <td class="py-3 text-center font-monospace">
                                {{ number_format($netUnitsKept) }} pcs net
                            </td>
                            <td class="py-3 text-end font-monospace text-dark">
                                Rs. {{ number_format($totalInvoicedAmount, 2) }}
                            </td>
                            <td class="py-3 text-end font-monospace text-danger">
                                Rs. {{ number_format($totalRefundedAmount, 2) }}
                            </td>
                            <td class="pe-4 py-3 text-end no-print font-monospace text-primary">
                                Net: Rs. {{ number_format($netBalance, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
