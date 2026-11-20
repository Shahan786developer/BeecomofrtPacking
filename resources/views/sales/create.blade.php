@extends('layouts.app')

@section('title', $billType === 'china' ? 'New China Sales Invoice' : 'New Bee Comfort Sales Invoice')

@push('styles')
<style>
    @keyframes rowHighlightPulse {
        0% { background-color: #fef08a; }
        50% { background-color: #bbf7d0; }
        100% { background-color: transparent; }
    }
    .row-highlight-pulse {
        animation: rowHighlightPulse 1.2s ease-in-out;
    }

    @keyframes qtyPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.22); background-color: #dcfce7; border-color: #16a34a; }
        100% { transform: scale(1); }
    }
    .qty-animated {
        animation: qtyPulse 0.8s ease-in-out;
        border-color: #16a34a !important;
        font-weight: 900 !important;
    }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $billType === 'china' ? 'New China Bill' : 'New Bee Comfort Bill' }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Compact Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="h4 fw-bold text-dark mb-0">
                {{ $billType === 'china' ? 'New China Sales Invoice' : 'New Bee Comfort Sales Invoice' }}
            </h1>
            <span class="badge {{ $billType === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }} px-2.5 py-1 text-uppercase fw-bold fs-7">
                <i class="bi bi-lock-fill me-1"></i> {{ $billType === 'china' ? 'China Catalog Locked' : 'Bee Comfort Catalog Locked' }}
            </span>
            <span class="text-secondary small">
                ({{ count($products) }} {{ $billType === 'china' ? 'China' : 'Bee Comfort' }} items available)
            </span>
        </div>
        <div class="d-flex gap-2">
            @if($billType === 'china')
                <a href="{{ route('sales.create', ['bill_type' => 'bee_comfort']) }}" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="bi bi-arrow-left-right me-1"></i> Switch to Bee Comfort Bill
                </a>
            @else
                <a href="{{ route('sales.create', ['bill_type' => 'china']) }}" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="bi bi-arrow-left-right me-1"></i> Switch to China Bill
                </a>
            @endif
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Invoices List
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('sales.store') }}" id="sale-form" class="d-flex flex-column gap-3">
        @csrf

        <!-- 1. COMPACT INVOICE METADATA CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <!-- Bill Category (Locked) -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Bill Category</label>
                        <div class="d-flex align-items-center justify-content-between px-2.5 py-1.5 {{ $billType === 'china' ? 'bg-danger-subtle border border-danger-subtle' : 'bg-primary-subtle border border-primary-subtle' }} rounded">
                            <span class="fw-bold fs-7 text-uppercase {{ $billType === 'china' ? 'text-danger' : 'text-primary' }}">
                                <i class="bi bi-tag-fill me-1"></i> {{ $billType === 'china' ? 'China Bill' : 'Bee Comfort Bill' }}
                            </span>
                            <i class="bi bi-lock-fill {{ $billType === 'china' ? 'text-danger' : 'text-primary' }}" title="Locked to this catalog"></i>
                        </div>
                        <input type="hidden" name="bill_type" id="bill_type" value="{{ $billType }}">
                    </div>

                    <!-- Invoice Number -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="invoice_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice No <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="invoice_no" id="invoice_no" value="{{ old('invoice_no', $nextInvoiceNo) }}" required
                            class="form-control form-control-sm font-monospace fw-bold">
                    </div>

                    <!-- Customer Selection -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="customer_id" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                Customer Account <span class="text-danger">*</span>
                            </label>
                            <button type="button" onclick="openNewCustomerModal()" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-bold fs-7">
                                <i class="bi bi-person-plus me-0.5"></i> + New Customer
                            </button>
                        </div>
                        <select name="customer_id" id="customer_id" required class="searchable-select form-select form-select-sm">
                            <option value="">Select Customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                    data-previous-balance="{{ $customer->getPreviousBalance() }}"
                                    data-phone="{{ $customer->phone }}"
                                    data-owner="{{ $customer->owner_name }}"
                                    data-city="{{ $customer->city }}"
                                    {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} — Owner: {{ $customer->owner_name ?: '—' }} • City: {{ $customer->city ?: '—' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 d-flex flex-wrap align-items-center justify-content-between gap-1">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="show_previous_balance" id="show_previous_balance" value="1" {{ old('show_previous_balance') ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-dark" for="show_previous_balance" style="font-size: 0.75rem;">
                                    <i class="bi bi-wallet2 text-primary me-0.5"></i> Show Prev Balance
                                </label>
                            </div>
                            <div id="customer-balance-badge" class="badge bg-light text-dark border px-2 py-0.5 fs-8 d-none">
                                Balance: <span id="customer-balance-amount" class="fw-bold font-monospace text-primary">Rs. 0.00</span>
                            </div>
                        </div>
                        <input type="hidden" name="previous_balance" id="previous_balance_input" value="{{ old('previous_balance', 0) }}">
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-12 col-md-2">
                        <label for="invoice_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $today) }}" required class="form-control form-control-sm font-monospace">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. STREAMLINED ACTION BAR: LEFT (DROPDOWN SEARCH AUTO-ADD) & RIGHT (BARCODE SCAN) -->
        <div class="card border-0 shadow-sm {{ $billType === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }}">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    
                    <!-- LEFT SIDE: Catalog Dropdown Search (Auto-Add, No Add Button) -->
                    <div class="col-12 col-lg-7">
                        <label for="quick_product_select" class="form-label small fw-bold text-white text-uppercase mb-1 fs-7">
                            <i class="bi bi-search me-1"></i> Search &amp; Select {{ $billType === 'china' ? 'China' : 'Bee Comfort' }} Product
                        </label>
                        <div class="text-dark">
                            <select id="quick_product_select" class="searchable-select form-select form-select-sm">
                                <option value="">Search {{ $billType === 'china' ? 'China' : 'Bee Comfort' }} product ({{ count($products) }} items)...</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod['id'] }}" 
                                        data-id="{{ $prod['id'] }}"
                                        data-sku="{{ $prod['sku_code'] }}"
                                        data-title="{{ $prod['title'] }}"
                                        data-parent="{{ $prod['parent_category'] }}"
                                        data-price="{{ $prod['selling_price'] }}"
                                        data-stock="{{ $prod['stock_quantity'] }}">
                                        {{ $prod['title'] }} — [SKU: {{ $prod['sku_code'] }}] (Rs. {{ number_format($prod['selling_price'], 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- RIGHT SIDE: Barcode Scan Input -->
                    <div class="col-12 col-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="barcode_scanner_input" class="form-label small fw-bold text-white text-uppercase mb-0 fs-7">
                                <i class="bi bi-upc-scan me-1"></i> Scan {{ $billType === 'china' ? 'China' : 'Bee Comfort' }} SKU Barcode
                            </label>
                            <span class="badge bg-white {{ $billType === 'china' ? 'text-danger' : 'text-primary' }} fs-8 fw-bold">Active</span>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 {{ $billType === 'china' ? 'text-danger' : 'text-primary' }}">
                                <i class="bi bi-upc"></i>
                            </span>
                            <input type="text" id="barcode_scanner_input"
                                placeholder="Scan 7-digit SKU with barcode gun..." 
                                class="form-control border-0 font-monospace fw-bold text-dark"
                                autocomplete="off">
                        </div>
                    </div>

                </div>

                <!-- FAST BILLING SHORTCUTS BANNER & FEEDBACK -->
                <div class="mt-2.5 pt-2 border-top border-white-50 d-flex flex-wrap justify-content-between align-items-center small text-white">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold"><i class="bi bi-lightning-charge-fill text-warning me-0.5"></i> Fast Billing:</span>
                        <span>Auto-adds on select</span> &bull;
                        <span>Auto-focuses Qty</span> &bull;
                        <span>Press <kbd class="bg-white text-dark px-1.5 py-0.5 rounded shadow-sm fw-bold">Tab</kbd> for Next Item</span> &bull;
                        <span>Press <kbd class="bg-white text-dark px-1.5 py-0.5 rounded shadow-sm fw-bold">Enter</kbd> to Generate Bill</span>
                    </div>
                    <div id="scanner-feedback" class="small fw-bold d-none">
                        <i class="bi bi-check-circle-fill me-1"></i> <span id="scanner-feedback-text"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. INVOICE ITEMS TABLE -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">Billed Items Table</h6>
                <span id="items-badge" class="badge bg-light text-dark border px-2 py-1 fs-7 font-monospace">
                    0 Items Added
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="invoice-items-table">
                    @php
                        $canEditRate = ($billType === 'bee_comfort' && (auth()->user()?->isSuperAdmin() ?? false));
                    @endphp
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 40px;">#</th>
                            <th class="py-2 text-secondary text-uppercase fs-7">Product Title</th>
                            <th class="py-2 text-secondary text-uppercase fs-7" style="width: 110px;">SKU</th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 90px;">Stock</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: {{ $canEditRate ? '135px' : '110px' }};">
                                Rate (Rs.)
                                @if($canEditRate)
                                    <span class="badge bg-success-subtle text-success fs-9 ms-0.5" title="Editable by Super Admin"><i class="bi bi-pencil-fill"></i></span>
                                @endif
                            </th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 110px;">Quantity</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 130px;">Amount (Rs.)</th>
                            <th class="pe-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 50px;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body" class="border-top-0">
                        <tr id="empty-row">
                            <td colspan="8" class="text-center py-4 text-secondary">
                                <i class="bi bi-cart3 fs-3 text-secondary d-block mb-1"></i>
                                <span class="fw-medium">Invoice is currently empty.</span> Select a product from the left dropdown or scan barcode on the right.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 4. COMPACT FOOTER: REMARKS & INVOICE TOTAL -->
            <div class="card-footer bg-light border-top p-3">
                <div class="row g-3 align-items-center">
                    <!-- Remarks / Notes -->
                    <div class="col-12 col-md-6">
                        <label for="notes" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Notes / Remarks
                        </label>
                        <input type="text" name="notes" id="notes" placeholder="Optional notes for customer or invoice records..." class="form-control form-control-sm bg-white">
                    </div>

                    <!-- Total & Save Button -->
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between p-2.5 bg-white border rounded gap-2">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary small">Current Bill:</span>
                                    <span id="bill-subtotal-display" class="fw-bold font-monospace text-dark fs-7">Rs. 0.00</span>
                                </div>
                                <div id="previous-balance-row" class="d-flex align-items-center gap-2 d-none">
                                    <span class="text-danger small fw-semibold">+ Prev Balance:</span>
                                    <span id="previous-balance-display" class="fw-bold font-monospace text-danger fs-7">Rs. 0.00</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-0.5">
                                    <span class="text-secondary small fw-bold text-uppercase">Net Payable:</span>
                                    <span id="total-amount-display" class="h4 fw-black {{ $billType === 'china' ? 'text-danger' : 'text-primary' }} font-monospace mb-0">Rs. 0.00</span>
                                </div>
                            </div>
                            <button type="submit" id="btn-submit-invoice" disabled class="btn {{ $billType === 'china' ? 'btn-danger' : 'btn-primary' }} fw-bold px-4 py-2 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

<!-- QUICK ADD CUSTOMER MODAL -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark" id="newCustomerModalLabel">
                    <i class="bi bi-person-plus text-primary me-1.5"></i> Register Customer Account
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 d-flex flex-column gap-2.5">
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Company / Name <span class="text-danger">*</span></label>
                        <input type="text" id="modal_cust_name" required placeholder="Business or Company Name" class="form-control form-control-sm fw-bold">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Owner Name</label>
                        <input type="text" id="modal_cust_owner_name" placeholder="Owner or Proprietor" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Primary Phone</label>
                        <input type="text" id="modal_cust_phone" placeholder="e.g. 0300-1234567" class="form-control form-control-sm font-monospace">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Phone 2 (Optional)</label>
                        <input type="text" id="modal_cust_phone_2" placeholder="Secondary phone" class="form-control form-control-sm font-monospace">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Phone 3 (Optional)</label>
                        <input type="text" id="modal_cust_phone_3" placeholder="Phone 3" class="form-control form-control-sm font-monospace">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Phone 4 (Optional)</label>
                        <input type="text" id="modal_cust_phone_4" placeholder="Phone 4" class="form-control form-control-sm font-monospace">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">City / Town</label>
                        <input type="text" id="modal_cust_city" placeholder="e.g. Lahore, Karachi" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Credit / Recovery Term</label>
                        <select id="modal_cust_credit_days" class="form-select form-select-sm">
                            <option value="">None / Cash Basis</option>
                            <option value="7">7 Days (Weekly)</option>
                            <option value="15">15 Days (Bi-Weekly)</option>
                            <option value="30">30 Days (Monthly)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">NTN (Optional)</label>
                        <input type="text" id="modal_cust_ntn" placeholder="e.g. 1234567-8" class="form-control form-control-sm font-monospace">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">STRN (Optional)</label>
                        <input type="text" id="modal_cust_strn" placeholder="e.g. 12-34-5678-901-23" class="form-control form-control-sm font-monospace">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Email</label>
                        <input type="email" id="modal_cust_email" placeholder="customer@domain.com" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Physical Address</label>
                        <input type="text" id="modal_cust_address" placeholder="Physical shop or billing address" class="form-control form-control-sm">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top bg-light py-2 px-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="saveNewCustomerAjax()" class="btn btn-sm btn-primary fw-bold">Save Customer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const allProductsCatalog = {!! json_encode($products) !!};
    let invoiceRows = [];

    const billTypeSelect = document.getElementById('bill_type');
    const invoiceNoInput = document.getElementById('invoice_no');
    const scannerInput = document.getElementById('barcode_scanner_input');
    const quickSelect = document.getElementById('quick_product_select');
    const itemsBody = document.getElementById('invoice-items-body');
    const itemsBadge = document.getElementById('items-badge');
    const totalAmountDisplay = document.getElementById('total-amount-display');
    const btnSubmit = document.getElementById('btn-submit-invoice');
    const feedbackBox = document.getElementById('scanner-feedback');
    const feedbackText = document.getElementById('scanner-feedback-text');

    let customerModalInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        if (scannerInput) {
            scannerInput.focus();

            scannerInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    processScan(this.value.trim());
                }
            });

            scannerInput.addEventListener('input', function() {
                const val = this.value.trim();
                if (/^\d{7}$/.test(val)) {
                    processScan(val);
                }
            });
        }

        // Auto-add product when selected from dropdown without clicking Add button
        if (quickSelect) {
            function onProductSelected(prodId) {
                if (!prodId) return;
                const prod = allProductsCatalog.find(p => p.id == prodId);
                if (prod) {
                    addProductToInvoice(prod);
                    if (quickSelect.tomselect) {
                        quickSelect.tomselect.clear(true);
                    } else {
                        quickSelect.value = '';
                    }
                    showFeedback(`Product '${prod.title}' added to invoice.`);
                }
            }

            // TomSelect or Native Select hook
            setTimeout(() => {
                if (quickSelect.tomselect) {
                    quickSelect.tomselect.on('change', function(val) {
                        onProductSelected(val);
                    });
                } else {
                    quickSelect.addEventListener('change', function() {
                        onProductSelected(this.value);
                    });
                }
            }, 300);
        }
    });

    function processScan(sku) {
        if (!sku) return;

        const currentBillType = billTypeSelect ? billTypeSelect.value : 'bee_comfort';
        const requiredNorm = (currentBillType === 'china') ? 'china' : 'beecomfort';
        const requiredLabel = (currentBillType === 'china') ? 'China' : 'Bee Comfort';

        const prod = allProductsCatalog.find(p => p.sku_code == sku);

        if (prod) {
            const prodNorm = (prod.parent_category || 'Bee Comfort').toLowerCase().replace(/\s+/g, '');
            if (prodNorm !== requiredNorm) {
                alert(`Conflict Warning: Product '${prod.title}' belongs to '${prod.parent_category}' catalog, but this is a ${requiredLabel} Bill! Only ${requiredLabel} products can be added.`);
                scannerInput.select();
                return;
            }
            addProductToInvoice(prod);
            scannerInput.value = '';
            showFeedback(`Scanned [SKU: ${sku}]: '${prod.title}' added.`);
        } else {
            fetch(`/api/products/by-sku/${encodeURIComponent(sku)}?bill_type=${encodeURIComponent(currentBillType)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.found) {
                        if (data.mismatch) {
                            alert(`Category Conflict: Product '${data.title}' belongs to '${data.parent_category}' and cannot be added to a ${requiredLabel} Bill! Only ${requiredLabel} products are allowed.`);
                            scannerInput.select();
                            return;
                        }
                        addProductToInvoice({
                            id: data.id,
                            sku_code: data.sku_code,
                            title: data.title,
                            parent_category: data.parent_category,
                            selling_price: data.selling_price,
                            stock_quantity: data.stock_quantity,
                        });
                        scannerInput.value = '';
                        showFeedback(`Scanned [SKU: ${sku}]: '${data.title}' added.`);
                    } else {
                        alert(`No product found with barcode SKU '${sku}'.`);
                        scannerInput.select();
                    }
                })
                .catch(err => {
                    alert(`Error scanning barcode: ${err}`);
                    scannerInput.select();
                });
        }
    }

    function showFeedback(msg) {
        if (!feedbackBox || !feedbackText) return;
        feedbackText.textContent = msg;
        feedbackBox.classList.remove('d-none');
        setTimeout(() => { feedbackBox.classList.add('d-none'); }, 3000);
    }

    function addProductToInvoice(product) {
        let targetIndex = -1;
        let isExisting = false;

        const existingIdx = invoiceRows.findIndex(item => item.product_id == product.id);
        if (existingIdx > -1) {
            invoiceRows[existingIdx].quantity += 1;
            targetIndex = existingIdx;
            isExisting = true;
        } else {
            // First added item stays on top (FIFO order preserved)
            invoiceRows.push({
                product_id: product.id,
                title: product.title,
                sku: product.sku_code,
                price: parseFloat(product.selling_price),
                stock: parseInt(product.stock_quantity),
                quantity: 1,
            });
            targetIndex = invoiceRows.length - 1;
        }

        renderInvoiceTable(targetIndex, isExisting);
    }

    function removeRow(index) {
        invoiceRows.splice(index, 1);
        renderInvoiceTable();
    }

    function updateRowQty(index, newQty) {
        let q = parseInt(newQty) || 1;
        if (q < 1) q = 1;
        invoiceRows[index].quantity = q;
        renderInvoiceTable();
    }

    function updateRowPrice(index, newPrice) {
        let p = parseFloat(newPrice);
        if (isNaN(p) || p < 0) p = 0;
        invoiceRows[index].price = p;
        renderInvoiceTable();
    }

    // Handles keyboard navigation on quantity input:
    // Tab -> Focus back to product dropdown
    // Enter -> Save / Generate bill
    function handleQtyKeydown(e, index) {
        if (e.key === 'Tab' && !e.shiftKey) {
            e.preventDefault();
            // Focus back to quickSelect dropdown for next product
            if (quickSelect && quickSelect.tomselect) {
                quickSelect.tomselect.focus();
            } else if (quickSelect) {
                quickSelect.focus();
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            // Ensure blur updates quantity
            e.target.blur();
            // Submit form to generate bill
            const form = document.getElementById('sale-form');
            if (form && invoiceRows.length > 0) {
                if (form.reportValidity()) {
                    form.submit();
                }
            }
        }
    }

    const canEditRate = {{ $canEditRate ? 'true' : 'false' }};
    const custSelect = document.getElementById('customer_id');
    const showPrevBalCheckbox = document.getElementById('show_previous_balance');
    const prevBalInput = document.getElementById('previous_balance_input');
    const prevBalBadge = document.getElementById('customer-balance-badge');
    const prevBalAmount = document.getElementById('customer-balance-amount');
    const prevBalRow = document.getElementById('previous-balance-row');
    const prevBalDisplay = document.getElementById('previous-balance-display');
    const billSubtotalDisplay = document.getElementById('bill-subtotal-display');

    let currentPreviousBalance = 0.0;

    function fetchCustomerPreviousBalance(customerId) {
        if (!customerId) {
            currentPreviousBalance = 0.0;
            updateBalanceUI();
            return;
        }

        const opt = custSelect ? custSelect.querySelector(`option[value="${customerId}"]`) : null;
        if (opt && opt.dataset && opt.dataset.previousBalance !== undefined) {
            currentPreviousBalance = parseFloat(opt.dataset.previousBalance) || 0.0;
            updateBalanceUI();
        }

        // Live real-time fetch from server API
        fetch(`/api/customers/${encodeURIComponent(customerId)}/balance`)
            .then(r => r.json())
            .then(data => {
                if (data.found) {
                    currentPreviousBalance = parseFloat(data.previous_balance) || 0.0;
                    updateBalanceUI();
                }
            })
            .catch(err => {
                console.error('Balance fetch error:', err);
            });
    }

    function updateBalanceUI() {
        if (prevBalInput) prevBalInput.value = currentPreviousBalance.toFixed(2);
        if (prevBalAmount) prevBalAmount.textContent = 'Rs. ' + currentPreviousBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (prevBalDisplay) prevBalDisplay.textContent = 'Rs. ' + currentPreviousBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const hasCust = custSelect && custSelect.value;
        if (hasCust && prevBalBadge) {
            prevBalBadge.classList.remove('d-none');
        } else if (prevBalBadge) {
            prevBalBadge.classList.add('d-none');
        }

        const showPrev = showPrevBalCheckbox && showPrevBalCheckbox.checked;
        if (showPrev && prevBalRow) {
            prevBalRow.classList.remove('d-none');
        } else if (prevBalRow) {
            prevBalRow.classList.add('d-none');
        }

        // Recalculate totals
        let billTotal = 0.0;
        invoiceRows.forEach(row => {
            billTotal += (row.quantity * row.price);
        });
        if (billSubtotalDisplay) {
            billSubtotalDisplay.textContent = 'Rs. ' + billTotal.toFixed(2);
        }
        const grandPayable = showPrev ? (billTotal + currentPreviousBalance) : billTotal;
        totalAmountDisplay.textContent = 'Rs. ' + grandPayable.toFixed(2);
    }

    if (showPrevBalCheckbox) {
        showPrevBalCheckbox.addEventListener('change', updateBalanceUI);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            if (custSelect && custSelect.tomselect) {
                custSelect.tomselect.on('change', function(val) {
                    fetchCustomerPreviousBalance(val);
                });
            } else if (custSelect) {
                custSelect.addEventListener('change', function() {
                    fetchCustomerPreviousBalance(this.value);
                });
            }
            if (custSelect && custSelect.value) {
                fetchCustomerPreviousBalance(custSelect.value);
            }
        }, 300);
    });

    function renderInvoiceTable(highlightIndex = -1, isExisting = false) {
        if (invoiceRows.length === 0) {
            itemsBody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="8" class="text-center py-4 text-secondary">
                        <i class="bi bi-cart3 fs-3 text-secondary d-block mb-1"></i>
                        <span class="fw-medium">Invoice is currently empty.</span> Select a product from the left dropdown or scan barcode on the right.
                    </td>
                </tr>
            `;
            itemsBadge.textContent = '0 Items Added';
            if (billSubtotalDisplay) billSubtotalDisplay.textContent = 'Rs. 0.00';
            const showPrev = showPrevBalCheckbox && showPrevBalCheckbox.checked;
            totalAmountDisplay.textContent = 'Rs. ' + (showPrev ? currentPreviousBalance : 0.0).toFixed(2);
            btnSubmit.disabled = true;
            return;
        }

        let html = '';
        let total = 0.0;
        let totalItems = 0;

        invoiceRows.forEach((row, idx) => {
            const lineTotal = row.quantity * row.price;
            total += lineTotal;
            totalItems += row.quantity;
            const isTarget = (idx === highlightIndex);

            const rateHtml = canEditRate ? `
                <input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" id="rate-input-${idx}"
                    value="${row.price.toFixed(2)}" required
                    onchange="updateRowPrice(${idx}, this.value)"
                    class="form-control form-control-sm text-end fw-bold font-monospace py-1"
                    style="max-width: 95px; display: inline-block;">
            ` : `
                <span class="font-monospace small">Rs. ${row.price.toFixed(2)}</span>
                <input type="hidden" name="items[${idx}][unit_price]" value="${row.price.toFixed(2)}">
            `;

            html += `
                <tr id="row-${idx}" class="${isTarget ? 'row-highlight-pulse' : ''}">
                    <td class="ps-3 py-2 text-center text-secondary small font-monospace">${idx + 1}</td>
                    <td class="py-2">
                        <span class="fw-bold text-dark d-block fs-7">${row.title}</span>
                        <input type="hidden" name="items[${idx}][product_id]" value="${row.product_id}">
                    </td>
                    <td class="py-2">
                        <span class="badge bg-light text-dark font-monospace border fs-8">${row.sku}</span>
                    </td>
                    <td class="py-2 text-center">
                        <span class="badge ${row.stock <= 0 ? 'bg-danger text-white' : 'bg-light text-secondary border'} fs-8 font-monospace">
                            ${row.stock} pcs
                        </span>
                    </td>
                    <td class="py-2 text-end">
                        ${rateHtml}
                    </td>
                    <td class="py-2 text-center">
                        <input type="number" name="items[${idx}][quantity]" id="qty-input-${idx}"
                            value="${row.quantity}" min="1" required
                            onchange="updateRowQty(${idx}, this.value)"
                            onkeydown="handleQtyKeydown(event, ${idx})"
                            class="form-control form-control-sm text-center fw-bold font-monospace mx-auto py-1 ${isTarget ? 'qty-animated text-success' : ''}"
                            style="max-width: 80px;">
                    </td>
                    <td class="py-2 text-end font-monospace fw-bold text-dark fs-7">
                        Rs. ${lineTotal.toFixed(2)}
                    </td>
                    <td class="pe-3 py-2 text-center">
                        <button type="button" onclick="removeRow(${idx})" class="btn btn-outline-danger btn-sm p-0 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" title="Remove Item">
                            <i class="bi bi-x fs-6"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        itemsBody.innerHTML = html;
        itemsBadge.textContent = `${invoiceRows.length} Line(s) (${totalItems} pcs)`;
        if (billSubtotalDisplay) billSubtotalDisplay.textContent = 'Rs. ' + total.toFixed(2);
        const showPrev = showPrevBalCheckbox && showPrevBalCheckbox.checked;
        const grandPayable = showPrev ? (total + currentPreviousBalance) : total;
        totalAmountDisplay.textContent = 'Rs. ' + grandPayable.toFixed(2);
        btnSubmit.disabled = false;

        // Auto-focus quantity input of the added/updated product and select text so user can immediately type quantity
        if (highlightIndex >= 0) {
            setTimeout(() => {
                const qtyInput = document.getElementById(`qty-input-${highlightIndex}`);
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            }, 60);
        }
    }

    function openNewCustomerModal() {
        if (!customerModalInstance) {
            customerModalInstance = new bootstrap.Modal(document.getElementById('newCustomerModal'));
        }
        customerModalInstance.show();
    }

    function saveNewCustomerAjax() {
        const name = document.getElementById('modal_cust_name').value.trim();
        const owner = document.getElementById('modal_cust_owner_name').value.trim();
        const p1 = document.getElementById('modal_cust_phone').value.trim();
        const p2 = document.getElementById('modal_cust_phone_2').value.trim();
        const p3 = document.getElementById('modal_cust_phone_3').value.trim();
        const p4 = document.getElementById('modal_cust_phone_4').value.trim();
        const city = document.getElementById('modal_cust_city').value.trim();
        const email = document.getElementById('modal_cust_email').value.trim();
        const addr = document.getElementById('modal_cust_address').value.trim();
        const ntnEl = document.getElementById('modal_cust_ntn');
        const strnEl = document.getElementById('modal_cust_strn');
        const ntn = ntnEl ? ntnEl.value.trim() : null;
        const strn = strnEl ? strnEl.value.trim() : null;
        const creditDaysEl = document.getElementById('modal_cust_credit_days');
        const creditDays = creditDaysEl ? creditDaysEl.value : null;

        if (!name) {
            alert('Customer name is required.');
            return;
        }

        fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                owner_name: owner,
                phone: p1,
                phone_2: p2,
                phone_3: p3,
                phone_4: p4,
                city: city,
                email: email,
                address: addr,
                ntn: ntn || null,
                strn: strn || null,
                credit_days: creditDays ? parseInt(creditDays) : null,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.customer) {
                const cust = data.customer;
                const custText = `${cust.name} — Owner: ${cust.owner_name || '—'} • City: ${cust.city || '—'}`;
                if (custSelect && custSelect.tomselect) {
                    custSelect.tomselect.addOption({
                        value: cust.id,
                        text: custText
                    });
                    custSelect.tomselect.setValue(cust.id);
                } else if (custSelect) {
                    const opt = document.createElement('option');
                    opt.value = cust.id;
                    opt.textContent = custText;
                    opt.dataset.previousBalance = 0;
                    opt.selected = true;
                    custSelect.appendChild(opt);
                }
                fetchCustomerPreviousBalance(cust.id);
                if (customerModalInstance) customerModalInstance.hide();
                showFeedback(`Customer '${cust.name}' registered & selected.`);
            } else {
                alert('Customer registered successfully. Please refresh or select from dropdown.');
                if (customerModalInstance) customerModalInstance.hide();
            }
        })
        .catch(err => {
            alert('Customer registered. Closing dialog.');
            if (customerModalInstance) customerModalInstance.hide();
        });
    }
</script>
@endpush
