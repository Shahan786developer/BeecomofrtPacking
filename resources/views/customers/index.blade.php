@extends('layouts.app')

@section('title', 'Customers Directory')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Customers</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Customer Accounts Directory</h1>
            <p class="text-secondary small mb-0">Manage customer profiles, phone numbers, and purchasing histories for sales invoices.</p>
        </div>
        <div>
            <button type="button" onclick="document.getElementById('name-input').focus()" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus me-1"></i> Register New Customer
            </button>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="row g-4 align-items-start">

        <!-- Add Customer Form (Col 4) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-person-plus text-primary me-2"></i> Register Customer
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('customers.store') }}" class="d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Full Name / Company <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name-input" name="name" required placeholder="e.g. Royal Garments Ltd." class="form-control fw-bold">
                        </div>

                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Phone Number
                            </label>
                            <input type="text" name="phone" placeholder="+92 300 1234567" class="form-control font-monospace">
                        </div>

                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Email Address
                            </label>
                            <input type="email" name="email" placeholder="customer@domain.com" class="form-control">
                        </div>

                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Address / City
                            </label>
                            <textarea name="address" rows="2" placeholder="Physical store or billing address" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold mt-2">
                            Save Customer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Customers Table (Col 8) -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Registered Customers</h5>
                        <span class="text-secondary small">Customer accounts selectable during invoice generation</span>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1">
                        {{ $customers->total() }} Customers
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase fs-7">Customer Name</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Contact</th>
                                <th class="py-3 text-secondary text-uppercase fs-7">Location</th>
                                <th class="py-3 text-center text-secondary text-uppercase fs-7">Invoices</th>
                                <th class="py-3 text-center text-secondary text-uppercase fs-7">Returns</th>
                                <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($customers as $c)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark d-block fs-6">{{ $c->name }}</span>
                                </td>
                                <td class="py-3 font-monospace small text-secondary">
                                    {{ $c->phone ?? ($c->email ?? '—') }}
                                </td>
                                <td class="py-3 text-secondary small text-truncate" style="max-width: 180px;">
                                    {{ $c->address ?? '—' }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                        {{ $c->sales_count }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-1">
                                        {{ $c->returns_count }}
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    @if($c->sales_count == 0)
                                    <form method="POST" action="{{ route('customers.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Delete customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge bg-light text-secondary border small">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">No customer accounts registered yet.</td>
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

</div>
@endsection
