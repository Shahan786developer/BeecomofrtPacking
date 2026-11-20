@extends('layouts.app')

@section('title', 'Customer Accounts')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Customers</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Customer Accounts</h1>
            <p class="text-secondary small mb-0">Client directory, owner profiles, 4 contact numbers, and billing ledger histories.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.default-list') }}" class="btn btn-outline-danger shadow-sm">
                <i class="bi bi-shield-exclamation me-1"></i> Default List (Recovery)
            </a>
            @if(Auth::user()->hasPermission('customers', 'create'))
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                <i class="bi bi-person-plus me-1"></i> Register Customer
            </button>
            @endif
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Search by company, code (e.g. B001), owner, phone, email, city, NTN, STRN..." autocomplete="off">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-3">Search</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-clear-search {{ !empty($search) ? '' : 'd-none' }}">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers List Table -->
    <div id="table-container">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 22%;">Company &amp; Owner</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 22%;">Phone Numbers (1 - 4)</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 13%;">City</th>
                            <th class="py-3 text-secondary text-uppercase fs-7" style="width: 14%;">Email / Address / Tax</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 9%;">Credit Term</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 7%;">Invoices</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 13%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($customers as $c)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <a href="{{ route('customers.ledger', $c) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6" title="View Customer History & Ledger">
                                        {{ $c->name }} <i class="bi bi-journal-text text-primary small ms-1"></i>
                                    </a>
                                    <span class="badge bg-light text-primary border font-monospace small">{{ $c->formatted_code }}</span>
                                </div>
                                @if($c->owner_name)
                                    <span class="small text-secondary d-block mt-0.5">
                                        <i class="bi bi-person text-muted me-1"></i>Owner: <strong class="text-dark">{{ $c->owner_name }}</strong>
                                    </span>
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
                            <td class="py-3 text-dark small fw-semibold">
                                {{ $c->city ?: '—' }}
                            </td>
                            <td class="py-3 text-secondary small">
                                @if($c->email)
                                    <div class="text-truncate" style="max-width: 180px;"><i class="bi bi-envelope me-1"></i>{{ $c->email }}</div>
                                @endif
                                @if($c->address)
                                    <div class="text-truncate text-muted" style="max-width: 180px;"><i class="bi bi-geo-alt me-1"></i>{{ $c->address }}</div>
                                @endif
                                @if($c->ntn || $c->strn)
                                    <div class="mt-1 d-flex flex-wrap gap-1">
                                        @if($c->ntn)<span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">NTN: {{ $c->ntn }}</span>@endif
                                        @if($c->strn)<span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">STRN: {{ $c->strn }}</span>@endif
                                    </div>
                                @endif
                                @if(!$c->email && !$c->address && !$c->ntn && !$c->strn)
                                    —
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                @if($c->credit_days == 7)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-0.5">7 Days</span>
                                @elseif($c->credit_days == 15)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5">15 Days</span>
                                @elseif($c->credit_days == 30)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0.5">30 Days</span>
                                @else
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5">None</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                                    {{ $c->sales_count }}
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('customers.ledger', $c) }}" class="btn btn-sm btn-outline-info" title="Customer Ledger & Invoices History">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                    @if(Auth::user()->canAccessModule('barcode'))
                                    <a href="{{ route('barcode.customer', ['customer_id' => $c->id]) }}" class="btn btn-sm btn-outline-secondary" title="Print Customer Barcode (32x22mm)">
                                        <i class="bi bi-upc-scan"></i>
                                    </a>
                                    @endif
                                    @if(Auth::user()->hasPermission('customers', 'update'))
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $c->id }}" title="Edit Customer">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif

                                    @if(Auth::user()->hasPermission('customers', 'delete') && $c->sales_count == 0)
                                        <form method="POST" action="{{ route('customers.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Customer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Edit Customer Modal -->
                                <div class="modal fade text-start" id="editCustomerModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('customers.update', $c) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="bi bi-pencil-square text-primary me-2"></i> Edit Customer Account
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body d-flex flex-column gap-3 p-4">
                                                    <!-- Names & Code: Company, Code & Owner -->
                                                    <div class="row g-3">
                                                        <div class="col-12 col-md-5">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                Company / Business Name <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" name="name" value="{{ old('name', $c->name) }}" required class="form-control fw-bold">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                Customer Code
                                                            </label>
                                                            <input type="text" name="code" value="{{ old('code', $c->code) }}" placeholder="e.g. B001" class="form-control font-monospace">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                Owner Name
                                                            </label>
                                                            <input type="text" name="owner_name" value="{{ old('owner_name', $c->owner_name) }}" placeholder="e.g. Haji Muhammad Ali" class="form-control">
                                                        </div>
                                                    </div>

                                                    <!-- 4 Phone Numbers -->
                                                    <div>
                                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-2">
                                                            Contact Phone Numbers (Up to 4 Numbers)
                                                        </label>
                                                        <div class="row g-2">
                                                            <div class="col-12 col-sm-6 col-md-3">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light text-secondary fw-bold">#1</span>
                                                                    <input type="text" name="phone" value="{{ old('phone', $c->phone) }}" placeholder="Primary Phone" class="form-control font-monospace">
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-md-3">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light text-secondary fw-bold">#2</span>
                                                                    <input type="text" name="phone_2" value="{{ old('phone_2', $c->phone_2) }}" placeholder="Phone 2" class="form-control font-monospace">
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-md-3">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light text-secondary fw-bold">#3</span>
                                                                    <input type="text" name="phone_3" value="{{ old('phone_3', $c->phone_3) }}" placeholder="Phone 3" class="form-control font-monospace">
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-sm-6 col-md-3">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light text-secondary fw-bold">#4</span>
                                                                    <input type="text" name="phone_4" value="{{ old('phone_4', $c->phone_4) }}" placeholder="Phone 4" class="form-control font-monospace">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- City & Email -->
                                                    <div class="row g-3">
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                City
                                                            </label>
                                                            <input type="text" name="city" value="{{ old('city', $c->city) }}" placeholder="e.g. Lahore, Karachi, Rawalpindi" class="form-control">
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                Email Address
                                                            </label>
                                                            <input type="email" name="email" value="{{ old('email', $c->email) }}" placeholder="customer@domain.com" class="form-control">
                                                        </div>
                                                    </div>

                                                    <!-- Tax & FBR Details: NTN & STRN -->
                                                    <div class="row g-3">
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                NTN (National Tax No.)
                                                            </label>
                                                            <input type="text" name="ntn" value="{{ old('ntn', $c->ntn) }}" placeholder="e.g. 1234567-8" class="form-control font-monospace">
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                                STRN (Sales Tax Reg No.)
                                                            </label>
                                                            <input type="text" name="strn" value="{{ old('strn', $c->strn) }}" placeholder="e.g. 12-34-5678-901-23" class="form-control font-monospace">
                                                        </div>
                                                    </div>

                                                    <!-- Credit Recovery Term (Days) -->
                                                    <div>
                                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                            Credit / Recovery Term (Default Cycle)
                                                        </label>
                                                        <select name="credit_days" class="form-select">
                                                            <option value="" {{ old('credit_days', $c->credit_days) ? '' : 'selected' }}>None / Cash Basis (No Credit Cycle)</option>
                                                            <option value="7" {{ old('credit_days', $c->credit_days) == 7 ? 'selected' : '' }}>7 Days (Weekly Recovery)</option>
                                                            <option value="15" {{ old('credit_days', $c->credit_days) == 15 ? 'selected' : '' }}>15 Days (Bi-Weekly Recovery)</option>
                                                            <option value="30" {{ old('credit_days', $c->credit_days) == 30 ? 'selected' : '' }}>30 Days (Monthly Recovery)</option>
                                                        </select>
                                                        <div class="form-text fs-7 text-muted">Customer automatically enters Default List if previous balance is unpaid past these days.</div>
                                                    </div>

                                                    <!-- Physical Address -->
                                                    <div>
                                                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                            Physical / Store Address
                                                        </label>
                                                        <textarea name="address" rows="2" class="form-control" placeholder="Street, shop, or plaza details">{{ old('address', $c->address) }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-person-x fs-1 d-block mb-2 text-muted"></i>
                                No customer accounts found matching your query.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $customers->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Create Customer Modal -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="createCustomerModalLabel">
                        <i class="bi bi-person-plus text-primary me-2"></i> Register New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3 p-4">
                    <!-- Names & Code: Company, Code & Owner -->
                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Company / Business Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" required placeholder="e.g. Royal Garments, Ahsan Textiles" class="form-control fw-bold">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Customer Code
                            </label>
                            <input type="text" name="code" placeholder="e.g. B001" class="form-control font-monospace">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Owner Name
                            </label>
                            <input type="text" name="owner_name" placeholder="e.g. Haji Muhammad Ali" class="form-control">
                        </div>
                    </div>

                    <!-- 4 Phone Numbers -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-2">
                            Contact Phone Numbers (Up to 4 Numbers)
                        </label>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary fw-bold">#1</span>
                                    <input type="text" name="phone" placeholder="Primary Phone" class="form-control font-monospace">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary fw-bold">#2</span>
                                    <input type="text" name="phone_2" placeholder="Phone 2" class="form-control font-monospace">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary fw-bold">#3</span>
                                    <input type="text" name="phone_3" placeholder="Phone 3" class="form-control font-monospace">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary fw-bold">#4</span>
                                    <input type="text" name="phone_4" placeholder="Phone 4" class="form-control font-monospace">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- City & Email -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                City
                            </label>
                            <input type="text" name="city" placeholder="e.g. Lahore, Faisalabad, Multan" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Email Address
                            </label>
                            <input type="email" name="email" placeholder="customer@domain.com" class="form-control">
                        </div>
                    </div>

                    <!-- Tax & FBR Details: NTN & STRN -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                NTN (National Tax No.)
                            </label>
                            <input type="text" name="ntn" placeholder="e.g. 1234567-8" class="form-control font-monospace">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                STRN (Sales Tax Reg No.)
                            </label>
                            <input type="text" name="strn" placeholder="e.g. 12-34-5678-901-23" class="form-control font-monospace">
                        </div>
                    </div>

                    <!-- Credit Recovery Term (Days) -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Credit / Recovery Term (Default Cycle)
                        </label>
                        <select name="credit_days" class="form-select">
                            <option value="" selected>None / Cash Basis (No Credit Cycle)</option>
                            <option value="7">7 Days (Weekly Recovery)</option>
                            <option value="15">15 Days (Bi-Weekly Recovery)</option>
                            <option value="30">30 Days (Monthly Recovery)</option>
                        </select>
                        <div class="form-text fs-7 text-muted">Customer automatically enters Default List if previous balance is unpaid past these days.</div>
                    </div>

                    <!-- Physical Address -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Physical / Store Address
                        </label>
                        <textarea name="address" rows="2" class="form-control" placeholder="Street, market, or warehouse address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
