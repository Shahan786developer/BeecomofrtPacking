@extends('layouts.app')

@section('title', 'Company Profile & Invoice Branding')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Company Profile</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Company Profile &amp; Invoice Branding</h1>
            <p class="text-secondary small mb-0">Configure your business credentials, contact numbers, and terms printed on all sales invoices.</p>
        </div>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-receipt me-1"></i> Sales Invoices
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Edit Form (Col 7) -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Business Identity Details</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.company.update') }}" class="d-flex flex-column gap-3">
                        @csrf

                        <!-- Business Name -->
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Company / Business Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="form-control fw-bold fs-6">
                            <div class="form-text small text-secondary">Prints as the prominent header on invoices and statements (e.g. BEE COMFORT).</div>
                        </div>

                        <!-- Tagline -->
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Tagline / Business Slogan
                            </label>
                            <input type="text" name="tagline" value="{{ old('tagline', $company->tagline) }}" class="form-control" placeholder="e.g. Packing & Retail Garment Solutions">
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Warehouse / Shop Address
                            </label>
                            <input type="text" name="address" value="{{ old('address', $company->address) }}" class="form-control" placeholder="e.g. Warehouse • Pakistan">
                        </div>

                        <div class="row g-3">
                            <!-- Phone -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Phone / WhatsApp Contact
                                </label>
                                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control font-monospace" placeholder="e.g. +92 300 0000000">
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Official Billing Email
                                </label>
                                <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control" placeholder="e.g. orders@beecomfort.com">
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div>
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Invoice Terms &amp; Conditions / Note
                            </label>
                            <textarea name="terms_conditions" rows="3" class="form-control" placeholder="Policy printed on bottom of customer invoices...">{{ old('terms_conditions', $company->terms_conditions) }}</textarea>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save Company Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Invoice Header Preview (Col 5) -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Invoice Header Preview</h5>
                </div>

                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="p-4 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                            <div>
                                <h3 class="fw-black text-dark text-uppercase mb-0 fs-4">{{ $company->name }}</h3>
                                <div class="text-primary small fw-semibold">{{ $company->tagline }}</div>
                                <div class="text-secondary small mt-2">
                                    {{ $company->address }}<br>
                                    Phone / WhatsApp: {{ $company->phone }}<br>
                                    Email: {{ $company->email }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary text-white text-uppercase px-2 py-1">INVOICE</span>
                                <div class="font-monospace small text-dark fw-bold mt-1">BC-INV-XXXX</div>
                            </div>
                        </div>

                        <div class="bg-white rounded p-3 border small text-secondary">
                            <strong class="text-dark d-block mb-1">Invoice Notes / Policy Preview:</strong>
                            <em>{{ $company->terms_conditions ?: 'Thank you for your business. Please inspect goods upon receipt.' }}</em>
                        </div>
                    </div>

                    <div class="small text-secondary">
                        <i class="bi bi-info-circle text-primary me-1"></i> Any updates saved here will instantly reflect on all printed customer invoices and A4 statements.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
