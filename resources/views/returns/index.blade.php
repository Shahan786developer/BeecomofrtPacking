@extends('layouts.app')

@section('title', 'Customer Bill Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Bill Returns</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE TITLE -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Customer Bill Returns</h1>
            <p class="text-secondary small mb-0">Merchandise returns, separate China and Bee Comfort bill returns, and stock restorations.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('returns.create', ['return_type' => 'bee_comfort']) }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> + New Bee Comfort Return
            </a>
            <a href="{{ route('returns.create', ['return_type' => 'china']) }}" class="btn btn-danger fw-bold shadow-sm">
                <i class="bi bi-box-seam me-1"></i> + New China Return
            </a>
        </div>
    </div>

    <!-- CONTROLS & TABS: BEE COMFORT RETURNS VS CHINA RETURNS -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 d-flex flex-column gap-3">
            <div class="row g-2 align-items-center">
                <!-- Search Functionality Above -->
                <div class="col-12 col-md-9 col-lg-10">
                    <form method="GET" action="{{ route('returns.index') }}" class="d-flex gap-2">
                        @if(request('return_type'))
                            <input type="hidden" name="return_type" value="{{ request('return_type') }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by return #, original invoice #, or customer/owner name..." 
                                class="form-control border-start-0 ps-0" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-3">
                            Search
                        </button>
                        <a href="{{ route('returns.index', request('return_type') ? ['return_type' => request('return_type')] : []) }}" class="btn btn-outline-secondary btn-clear-search {{ request('search') ? '' : 'd-none' }}" title="Clear Search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </form>
                </div>

                <!-- Sales Link -->
                <div class="col-12 col-md-3 col-lg-2 d-flex justify-content-md-end">
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm fw-bold w-100">
                        <i class="bi bi-receipt me-1"></i> Sales Invoices
                    </a>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <ul class="nav nav-pills gap-2 pt-2 border-top">
                <li class="nav-item">
                    <a href="{{ route('returns.index', array_merge(request()->except('return_type', 'page'), ['return_type' => 'bee_comfort'])) }}" 
                       class="nav-link py-2 px-3 fw-bold {{ (!request('return_type') || request('return_type') === 'bee_comfort') ? 'active bg-primary' : 'bg-light text-dark' }}">
                        <i class="bi bi-shield-check me-1"></i> Bee Comfort Returns
                        <span class="badge bg-white text-primary ms-1">{{ $beeComfortCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('returns.index', array_merge(request()->except('page'), ['return_type' => 'china'])) }}" 
                       class="nav-link py-2 px-3 fw-bold {{ request('return_type') === 'china' ? 'active bg-danger text-white' : 'bg-light text-dark' }}">
                        <i class="bi bi-box-seam me-1"></i> China Returns
                        <span class="badge {{ request('return_type') === 'china' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1">{{ $chinaCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('returns.index', array_merge(request()->except('page'), ['return_type' => 'all'])) }}" 
                       class="nav-link py-2 px-3 fw-bold {{ request('return_type') === 'all' ? 'active bg-dark text-white' : 'bg-light text-secondary' }}">
                        <i class="bi bi-collection me-1"></i> All Returns
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
                        <th class="ps-3 py-3 text-secondary text-uppercase fs-7">Return No</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Original Invoice</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Customer &amp; Owner</th>
                        <th class="py-3 text-secondary text-uppercase fs-7">Return Date</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7">Units Returned</th>
                        <th class="py-3 text-end text-secondary text-uppercase fs-7">Refund Amount</th>
                        <th class="pe-3 py-3 text-end text-secondary text-uppercase fs-7">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($returns as $ret)
                    <tr>
                        <!-- Return Number & Badge -->
                        <td class="ps-3 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('returns.show', $ret) }}" class="fw-bold text-danger text-decoration-none font-monospace fs-6">
                                    {{ $ret->return_no }}
                                </a>
                                @if($ret->return_type === 'china')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0.5 px-1.5" style="font-size: 0.7rem;">China</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0.5 px-1.5" style="font-size: 0.7rem;">Bee Comfort</span>
                                @endif
                            </div>
                        </td>

                        <!-- Original Invoice -->
                        <td class="py-3">
                            <a href="{{ route('sales.show', $ret->sale) }}" class="text-primary fw-bold text-decoration-none font-monospace">
                                {{ $ret->sale->invoice_no }}
                            </a>
                        </td>

                        <!-- Customer Details -->
                        <td class="py-3">
                            <a href="{{ route('customers.ledger', $ret->customer_id) }}" class="fw-bold text-dark text-decoration-none hover-primary" title="View Customer Ledger">
                                {{ $ret->customer->name }} <i class="bi bi-journal-text text-primary small ms-1"></i>
                            </a>
                            @if($ret->customer->owner_name)
                                <div class="text-secondary small">Owner: <strong class="text-dark">{{ $ret->customer->owner_name }}</strong></div>
                            @endif
                        </td>

                        <!-- Date -->
                        <td class="py-3 text-secondary small font-monospace">
                            {{ $ret->return_date->format('d M Y') }}
                        </td>

                        <!-- Units -->
                        <td class="py-3 text-center">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1">
                                +{{ $ret->details->sum('quantity') }} unit(s)
                            </span>
                        </td>

                        <!-- Refund -->
                        <td class="py-3 text-end font-monospace fw-bold text-danger fs-6">
                            Rs. {{ number_format($ret->total_amount, 2) }}
                        </td>

                        <!-- Action Buttons (View, Edit) -->
                        <td class="pe-3 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('returns.show', $ret) }}" class="btn btn-sm btn-outline-info" title="View Return Receipt">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('returns.edit', $ret) }}" class="btn btn-sm btn-outline-primary" title="Edit Return">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-arrow-counterclockwise display-6 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold text-dark">No {{ request('return_type') === 'china' ? 'China' : 'Bee Comfort' }} bill returns recorded yet.</h6>
                            <p class="small text-secondary mb-3">When merchandise is returned, validate quantities against sales and restore inventory.</p>
                            <a href="{{ route('returns.create', ['return_type' => request('return_type') === 'china' ? 'china' : 'bee_comfort']) }}" class="btn btn-primary btn-sm">
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

</div>
@endsection
