@extends('layouts.app')

@section('title', 'Customer Barcode Generator')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('barcode.index') }}">Barcode Studio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customer Barcodes</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Customer Barcode Generator</h1>
            <p class="text-secondary small mb-0">Generate customer thermal barcode labels formatted for standard 32 mm &times; 22 mm sticker rolls.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i> Customers Directory
            </a>
            <a href="{{ route('barcode.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Printer Settings
            </a>
        </div>
    </div>

    <!-- Mode Tabs -->
    <ul class="nav nav-pills gap-2 bg-white p-2 rounded-3 shadow-sm border">
        <li class="nav-item">
            <a class="nav-link text-secondary" href="{{ route('barcode.index') }}">
                <i class="bi bi-boxes me-1"></i> Product Barcodes (32&times;22mm)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="{{ route('barcode.customer') }}">
                <i class="bi bi-person-badge me-1"></i> Customer Barcodes (32&times;22mm)
            </a>
        </li>
    </ul>

    <!-- Main Grid -->
    <div class="row g-4 items-start">

        <!-- Controls (Col 7) -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Generate Customer Thermal Stickers</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('barcode.customer.print') }}" target="_blank" id="customer-barcode-form" class="d-flex flex-column gap-4">
                        @csrf

                        <!-- Customer Selection -->
                        <div>
                            <label for="customer_select" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Select Customer <span class="text-danger">*</span>
                            </label>
                            <select name="customer_id" id="customer_select" required class="searchable-select form-select">
                                <option value="">Choose a customer...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (isset($selectedCustomer) && $selectedCustomer->id == $c->id) ? 'selected' : '' }}
                                        data-name="{{ $c->name }}"
                                        data-code="{{ $c->formatted_code }}"
                                        data-owner="{{ $c->owner_name ?? '' }}"
                                        data-phone="{{ $c->phone ?? '' }}"
                                        data-city="{{ $c->city ?? '' }}"
                                        data-due="{{ $c->balance_due }}">
                                        {{ $c->search_dropdown_label }} [Code: {{ $c->formatted_code }}]
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-secondary mt-1">
                                Salesmen can scan this barcode via mobile camera in Sale Orders and Cash Receipts (CRV) to instantly select this customer.
                            </div>
                        </div>

                        <!-- Label Content Preview Details -->
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Row 1 (Brand):</span>
                                    <div class="fw-bold text-dark font-monospace fs-7 text-uppercase">
                                        {{ $config->brand_name ?? 'BEE COMFORT' }}
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Row 2 (Customer):</span>
                                    <div id="row2-display" class="fw-bold text-primary font-monospace fs-7 text-uppercase">
                                        —
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Row 3 (City / Phone):</span>
                                    <div id="row3-display" class="fw-black text-danger font-monospace fs-6 text-uppercase">
                                        —
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="row g-3 pt-2 border-top">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Customer Outstanding Balance</label>
                                <input type="text" id="customer_due_display" value="Rs. 0.00" readonly class="form-control fw-bold font-monospace bg-light">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="print_quantity" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Stickers to Print <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="quantity" id="print_quantity" value="1" min="1" max="1000" required class="form-control fw-bold font-monospace text-primary fs-5">
                                <div class="form-text small text-secondary">Typically 1 to 5 stickers for ledger card &amp; shop counter.</div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-3 border-top d-flex gap-3">
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2.5 shadow-sm">
                                <i class="bi bi-printer me-2"></i> Print Customer Thermal Barcode (32&times;22mm)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Visual Preview (Col 5) -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Live Thermal Preview (32mm &times; 22mm)</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center bg-light">

                    <p class="text-muted small mb-3">Actual thermal roll print representation (5 Rows):</p>

                    <!-- Exact 32mm x 22mm Scale Preview Box -->
                    <div style="background: #ffffff; width: 280px; height: 192px; border: 2px dashed #94a3b8; border-radius: 8px; padding: 8px 12px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center;">
                        
                        <!-- Row 1: Brand -->
                        <div class="fw-bold text-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            {{ $config->brand_name ?? 'BEE COMFORT' }}
                        </div>

                        <!-- Row 2: Customer Name -->
                        <div id="preview-customer-name" class="fw-bold text-uppercase text-truncate w-100" style="font-size: 12px; line-height: 1.1;">
                            SELECT CUSTOMER
                        </div>

                        <!-- Row 3: Details (City / Phone / Owner) -->
                        <div id="preview-customer-details" class="fw-bold text-dark text-uppercase text-truncate w-100" style="font-size: 11px; line-height: 1;">
                            CITY • PHONE
                        </div>

                        <!-- Row 4: Barcode SVG -->
                        <div style="width: 100%; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                            <svg id="preview-barcode-svg" style="max-width: 100%; height: 50px;"></svg>
                        </div>

                        <!-- Row 5: Customer Code -->
                        <div id="preview-customer-code" class="font-monospace fw-bold" style="font-size: 13px; letter-spacing: 1.5px;">
                            B000
                        </div>
                    </div>

                    <div class="badge bg-secondary-subtle text-secondary border mt-4 px-3 py-2">
                        <i class="bi bi-info-circle me-1"></i> Formatted for direct POS &amp; Xprinter / Gprinter thermal rolls.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer_select');
        const row2Display = document.getElementById('row2-display');
        const row3Display = document.getElementById('row3-display');
        const customerDueDisplay = document.getElementById('customer_due_display');

        const prevName = document.getElementById('preview-customer-name');
        const prevDetails = document.getElementById('preview-customer-details');
        const prevCode = document.getElementById('preview-customer-code');
        const barcodeSvg = document.getElementById('preview-barcode-svg');

        function updateCustomerPreview() {
            const selectedOpt = customerSelect.options[customerSelect.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                row2Display.textContent = '—';
                row3Display.textContent = '—';
                customerDueDisplay.value = 'Rs. 0.00';
                prevName.textContent = 'SELECT CUSTOMER';
                prevDetails.textContent = 'CITY • PHONE';
                prevCode.textContent = 'B000';
                renderBarcode('B000');
                return;
            }

            const name = selectedOpt.getAttribute('data-name') || '';
            const code = selectedOpt.getAttribute('data-code') || ('B' + selectedOpt.value);
            const city = selectedOpt.getAttribute('data-city') || '';
            const phone = selectedOpt.getAttribute('data-phone') || '';
            const due = selectedOpt.getAttribute('data-due') || '0';

            const details = [city, phone].filter(Boolean).join(' • ') || 'CUSTOMER';

            row2Display.textContent = name;
            row3Display.textContent = details;
            customerDueDisplay.value = 'Rs. ' + parseFloat(due).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            prevName.textContent = name;
            prevDetails.textContent = details;
            prevCode.textContent = code;

            renderBarcode(code);
        }

        function renderBarcode(code) {
            try {
                JsBarcode(barcodeSvg, code, {
                    format: "CODE128",
                    lineColor: "#000000",
                    width: 1.8,
                    height: 48,
                    displayValue: false,
                    margin: 0
                });
            } catch (err) {
                console.error("Barcode rendering error:", err);
            }
        }

        customerSelect.addEventListener('change', updateCustomerPreview);

        // Initial preview render
        updateCustomerPreview();
    });
</script>
@endpush
