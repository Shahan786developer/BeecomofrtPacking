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
            <p class="text-secondary small mb-0">Configure your business logo, credentials, and invoice details printed on sales invoices.</p>
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
                    <h5 class="fw-bold text-dark mb-0">Business Identity &amp; Logo</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                        @csrf

                        <!-- Company Logo Upload -->
                        <div class="p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-2">
                                <i class="bi bi-image text-primary me-1"></i> Bee Comfort Company Logo
                            </label>
                            
                            @if(!empty($company->logo) && file_exists(public_path($company->logo)))
                                <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-white rounded border">
                                    <div class="p-1 bg-white border rounded">
                                        <img src="{{ asset($company->logo) }}" alt="Company Logo" style="max-height: 60px; max-width: 160px; object-fit: contain;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="small fw-bold text-dark d-block">Current Logo Active</span>
                                        <span class="text-secondary fs-8">This logo is fetched and printed on A4 sales invoices.</span>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                        <label class="form-check-label small text-danger fw-semibold" for="remove_logo">
                                            Remove Logo
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <input type="file" name="logo" id="logo" accept="image/*" class="form-control form-control-sm">
                                <div class="form-text small text-secondary mt-1">Upload PNG, JPG, WEBP, or SVG logo (recommended height 50-80px with transparent background).</div>
                            </div>
                        </div>

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
                                    Phone / WhatsApp Contact (Internal Record)
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
                                @if(!empty($company->logo) && file_exists(public_path($company->logo)))
                                    <div class="mb-2">
                                        <img src="{{ asset($company->logo) }}" alt="Logo" style="max-height: 48px; max-width: 180px; object-fit: contain;">
                                    </div>
                                @endif
                                <h3 class="fw-black text-dark text-uppercase mb-0 fs-4">{{ $company->name }}</h3>
                                @if(!empty($company->tagline))
                                    <div class="text-primary small fw-semibold">{{ $company->tagline }}</div>
                                @endif
                                <div class="text-secondary small mt-2">
                                    {{ $company->address }}<br>
                                    Email: {{ $company->email }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary text-white text-uppercase px-2 py-1">SALES INVOICE</span>
                                <div class="font-monospace small text-dark fw-bold mt-1">BC-SIN-01</div>
                            </div>
                        </div>

                        <div class="bg-white rounded p-3 border small text-secondary">
                            <strong class="text-dark d-block mb-1">Invoice Notes / Policy Preview:</strong>
                            <em>{{ $company->terms_conditions ?: 'Thank you for your business. Please inspect goods upon receipt.' }}</em>
                        </div>
                    </div>

                    <div class="small text-secondary">
                        <i class="bi bi-info-circle text-primary me-1"></i> The uploaded logo and company info will immediately reflect on all printed A4 sales invoices.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
