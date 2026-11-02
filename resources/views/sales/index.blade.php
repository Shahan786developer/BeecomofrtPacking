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
            <p class="text-secondary small mb-0">Manage customer billing records, separate China and Bee Comfort bills, and print A4 invoices.</p>
        </div>
    </div>

    <!-- TABS: BEE COMFORT BILLS VS CHINA BILLS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 d-flex flex-column gap-3">
            <div class="row g-2 align-items-center">
                <!-- Search Functionality Above -->
                <div class="col-12 col-md-7 col-lg-8">
                    <form method="GET" action="{{ route('sales.index') }}" class="d-flex gap-2">
                        @if(request('bill_type'))
                            <input type="hidden" name="bill_type" value="{{ request('bill_type') }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by invoice #, customer name, owner, or phone..." 
                                class="form-control border-start-0 ps-0" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">
                            Search
                        </button>
                        <a href="{{ route('sales.index', request('bill_type') ? ['bill_type' => request('bill_type')] : []) }}" class="btn btn-outline-secondary btn-clear-search {{ request('search') ? '' : 'd-none' }}" title="Clear Search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </form>
                </div>

                <!-- Action Buttons: Create & Process Return -->
                <div class="col-12 col-md-5 col-lg-4 d-flex justify-content-md-end gap-2">
                    <a href="{{ route('sales.create', ['bill_type' => request('bill_type') === 'china' ? 'china' : 'bee_comfort']) }}" class="btn btn-primary fw-bold shadow-sm flex-fill flex-md-grow-0">
                        <i class="bi bi-cart-plus me-1"></i> Create {{ request('bill_type') === 'china' ? 'China' : 'Bee Comfort' }} Bill
                    </a>
                    <a href="{{ route('returns.create', ['return_type' => request('bill_type') === 'china' ? 'china' : 'bee_comfort']) }}" class="btn btn-outline-warning fw-bold flex-fill flex-md-grow-0">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Return
                    </a>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <ul class="nav nav-pills gap-2 pt-2 border-top">
                <li class="nav-item">
                    <a href="{{ route('sales.index', array_merge(request()->except('bill_type', 'page'), ['bill_type' => 'bee_comfort'])) }}" 
                       class="nav-link py-2 px-3 fw-bold {{ (!request('bill_type') || request('bill_type') === 'bee_comfort') ? 'active bg-primary' : 'bg-light text-dark' }}">
                        <i class="bi bi-shield-check me-1"></i> Bee Comfort Bills
                        <span class="badge bg-white text-primary ms-1">{{ $beeComfortCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sales.index', array_merge(request()->except('page'), ['bill_type' => 'china'])) }}" 
                       class="nav-link py-2 px-3 fw-bold {{ request('bill_type') === 'china' ? 'active bg-danger text-white' : 'bg-light text-dark' }}">
                        <i class="bi bi-box-seam me-1"></i> China Bills
                        <span class="badge {{ request('bill_type') === 'china' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1">{{ $chinaCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sales.index', array_merge(request()->except('page'), ['bill_type' => 'all'])) }}" 
                       class="nav-link py-2 px-3 fw-bold {{ request('bill_type') === 'all' ? 'active bg-dark text-white' : 'bg-light text-secondary' }}">
                        <i class="bi bi-collection me-1"></i> All Bills
                        <span class="badge bg-secondary text-white ms-1">{{ $allCount ?? 0 }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- LIST TABLE -->
    <div id="table-container">
        <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Invoice No</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Customer &amp; Owner</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Items Sold</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Total Amount</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($sales as $sale)
                    <tr>
                        <!-- Invoice Number & Bill Type Badge -->
                        <td class="ps-3 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-primary text-decoration-none font-monospace fs-6">
                                    {{ $sale->invoice_no }}
                                </a>
                                @if($sale->bill_type === 'china')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0.5 px-1.5" style="font-size: 0.7rem;">China</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0.5 px-1.5" style="font-size: 0.7rem;">Bee Comfort</span>
                                @endif
                            </div>
                        </td>

                        <!-- Customer Details -->
                        <td class="py-3">
                            <a href="{{ route('customers.ledger', $sale->customer_id) }}" class="fw-bold text-dark text-decoration-none d-block hover-primary" title="View Customer Ledger">
                                {{ $sale->customer->name }} <i class="bi bi-journal-text text-primary small ms-1"></i>
                            </a>
                            <div class="small text-secondary">
                                @if($sale->customer->owner_name)
                                    <span>Owner: <strong class="text-dark">{{ $sale->customer->owner_name }}</strong></span>
                                @endif
                                @if($sale->customer->phone)
                                    <span class="ms-1 font-monospace"><i class="bi bi-telephone me-0.5"></i>{{ $sale->customer->phone }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Date -->
                        <td class="py-3 text-secondary small font-monospace">
                            {{ $sale->invoice_date->format('d M Y') }}
                        </td>

                        <!-- Items Sold Count -->
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-dark border px-2.5 py-1">
                                {{ $sale->details->count() }} item(s) ({{ $sale->details->sum('quantity') }} pcs)
                            </span>
                        </td>

                        <!-- Grand Total -->
                        <td class="py-3 text-end">
                            <span class="fw-black text-dark font-monospace fs-6">
                                Rs. {{ number_format($sale->total_amount, 2) }}
                            </span>
                        </td>

                        <!-- Action Buttons (Print, View, Edit, Return) -->
                        <td class="pe-3 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-outline-dark fw-bold" title="Print Standard A4 Invoice">
                                    <i class="bi bi-printer me-1"></i> Print
                                </a>
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" title="View Invoice">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-outline-primary" title="Edit Invoice">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('returns.create', ['sale_id' => $sale->id, 'return_type' => $sale->bill_type ?? 'bee_comfort']) }}" class="btn btn-sm btn-outline-warning" title="Process Return">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="bi bi-receipt display-6 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold text-dark">No {{ request('bill_type') === 'china' ? 'China' : 'Bee Comfort' }} bills recorded yet.</h6>
                            <p class="small text-secondary mb-3">Create your first invoice to generate billing and deplete warehouse stock.</p>
                            <a href="{{ route('sales.create', ['bill_type' => request('bill_type') === 'china' ? 'china' : 'bee_comfort']) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus me-1"></i> Create First Bill
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

</div>
@endsection
