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
            <p class="text-secondary small mb-0">Client directory, contact profiles, and billing histories for sales invoices.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                <i class="bi bi-person-plus me-1"></i> Register Customer
            </button>
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
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control border-start-0 ps-0" placeholder="Search by name, phone, email, city, or address...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-3">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Customers List Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fs-7" style="width: 22%;">Customer Name</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 14%;">Phone Number</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 18%;">Email</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 12%;">City</th>
                        <th class="py-3 text-secondary text-uppercase fs-7" style="width: 18%;">Address</th>
                        <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 8%;">Invoices</th>
                        <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7" style="width: 8%;">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($customers as $c)
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="fw-bold text-dark fs-6 d-block">{{ $c->name }}</span>
                        </td>
                        <td class="py-3 font-monospace small text-dark">
                            {{ $c->phone ?: '—' }}
                        </td>
                        <td class="py-3 text-secondary small">
                            {{ $c->email ?: '—' }}
                        </td>
                        <td class="py-3 text-dark small fw-semibold">
                            {{ $c->city ?: '—' }}
                        </td>
                        <td class="py-3 text-secondary small text-truncate" style="max-width: 200px;">
                            {{ $c->address ?: '—' }}
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                {{ $c->sales_count }}
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $c->id }}" title="Edit Customer">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>

                                @if($c->sales_count == 0)
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
                                <div class="modal-dialog">
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
                                            <div class="modal-body d-flex flex-column gap-3">
                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Full Name / Company <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name" value="{{ old('name', $c->name) }}" required class="form-control fw-bold">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Email Address
                                                    </label>
                                                    <input type="email" name="email" value="{{ old('email', $c->email) }}" placeholder="customer@domain.com" class="form-control">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        City
                                                    </label>
                                                    <input type="text" name="city" value="{{ old('city', $c->city) }}" placeholder="e.g. Lahore, Karachi, Rawalpindi" class="form-control">
                                                </div>

                                                <div>
                                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                                        Phone Number
                                                    </label>
                                                    <input type="text" name="phone" value="{{ old('phone', $c->phone) }}" placeholder="+92 300 1234567" class="form-control font-monospace">
                                                </div>

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

<!-- Create Customer Modal -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="createCustomerModalLabel">
                        <i class="bi bi-person-plus text-primary me-2"></i> Register New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Full Name / Company <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="e.g. Royal Garments, Ahsan Textiles" class="form-control fw-bold">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Email Address
                        </label>
                        <input type="email" name="email" placeholder="customer@domain.com" class="form-control">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            City
                        </label>
                        <input type="text" name="city" placeholder="e.g. Lahore, Faisalabad, Multan" class="form-control">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Phone Number
                        </label>
                        <input type="text" name="phone" placeholder="+92 300 1234567" class="form-control font-monospace">
                    </div>

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
