@extends('layouts.app')

@section('title', $billType === 'china' ? 'New China Sales Invoice' : 'New Bee Comfort Sales Invoice')

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
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone ?: ($customer->city ?: 'No Contact') }})
                                </option>
                            @endforeach
                        </select>
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

        <!-- 2. STREAMLINED SCANNER & CATALOG BAR -->
        <div class="card border-0 shadow-sm {{ $billType === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }}">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <!-- Barcode Scan Input -->
                    <div class="col-12 col-lg-6">
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
                            <input type="text" id="barcode_scanner_input" autofocus
                                placeholder="Scan 7-digit SKU with barcode gun..." 
                                class="form-control border-0 font-monospace fw-bold text-dark"
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Catalog Dropdown -->
                    <div class="col-12 col-lg-6">
                        <label for="quick_product_select" class="form-label small fw-bold text-white text-uppercase mb-1 fs-7">
                            <i class="bi bi-search me-1"></i> Or Select from {{ $billType === 'china' ? 'China' : 'Bee Comfort' }} Catalog
                        </label>
                        <div class="d-flex gap-1.5">
                            <div class="flex-grow-1 text-dark">
                                <select id="quick_product_select" class="searchable-select form-select form-select-sm">
                                    <option value="">Select {{ $billType === 'china' ? 'China' : 'Bee Comfort' }} product ({{ count($products) }} items)...</option>
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
                            <button type="button" id="btn-add-item" class="btn btn-light btn-sm fw-bold {{ $billType === 'china' ? 'text-danger' : 'text-primary' }} shadow-sm flex-shrink-0 px-3">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>

                <div id="scanner-feedback" class="mt-2 text-white small d-none">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="scanner-feedback-text"></span>
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
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 40px;">#</th>
                            <th class="py-2 text-secondary text-uppercase fs-7">Product Description (Unified Title)</th>
                            <th class="py-2 text-secondary text-uppercase fs-7" style="width: 110px;">SKU</th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 90px;">Stock</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 120px;">Rate (Rs.)</th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 110px;">Quantity</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 130px;">Amount (Rs.)</th>
                            <th class="pe-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 50px;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body" class="border-top-0">
                        <tr id="empty-row">
                            <td colspan="8" class="text-center py-4 text-secondary">
                                <i class="bi bi-cart3 fs-3 text-secondary d-block mb-1"></i>
                                <span class="fw-medium">Invoice is currently empty.</span> Scan a barcode in the scan bar or select from the dropdown above.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 4. COMPACT FOOTER: REMARKS & INVOICE TOTAL -->
            <div class="card-footer bg-light border-top p-3">
                <div class="row g-3 align-items-center">
                    <!-- Remarks / Notes -->
                    <div class="col-12 col-md-7">
                        <label for="notes" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Notes / Remarks
                        </label>
                        <input type="text" name="notes" id="notes" placeholder="Optional notes for customer or invoice records..." class="form-control form-control-sm bg-white">
                    </div>

                    <!-- Total & Save Button -->
                    <div class="col-12 col-md-5">
                        <div class="d-flex align-items-center justify-content-between p-2.5 bg-white border rounded">
                            <div>
                                <span class="text-secondary small d-block">Total Payable:</span>
                                <span id="total-amount-display" class="h4 fw-black {{ $billType === 'china' ? 'text-danger' : 'text-primary' }} font-monospace mb-0">Rs. 0.00</span>
                            </div>
                            <button type="submit" id="btn-submit-invoice" disabled class="btn {{ $billType === 'china' ? 'btn-danger' : 'btn-primary' }} fw-bold px-4 shadow-sm">
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
                        <input type="text" id="modal_cust_owner_name" placeholder="Owner Name" class="form-control form-control-sm">
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Contact Phone Numbers (1 - 4)</label>
                    <div class="row g-1.5">
                        <div class="col-6">
                            <input type="text" id="modal_cust_phone" placeholder="Phone 1 (Primary)" class="form-control font-monospace form-control-sm">
                        </div>
                        <div class="col-6">
                            <input type="text" id="modal_cust_phone_2" placeholder="Phone 2" class="form-control font-monospace form-control-sm">
                        </div>
                        <div class="col-6">
                            <input type="text" id="modal_cust_phone_3" placeholder="Phone 3" class="form-control font-monospace form-control-sm">
                        </div>
                        <div class="col-6">
                            <input type="text" id="modal_cust_phone_4" placeholder="Phone 4" class="form-control font-monospace form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">City</label>
                        <input type="text" id="modal_cust_city" placeholder="e.g. Lahore, Faisalabad" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Email</label>
                        <input type="email" id="modal_cust_email" placeholder="customer@domain.com" class="form-control form-control-sm">
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Physical Address</label>
                    <input type="text" id="modal_cust_address" placeholder="Physical shop or billing address" class="form-control form-control-sm">
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
    const btnAddItem = document.getElementById('btn-add-item');
    const itemsBody = document.getElementById('invoice-items-body');
    const itemsBadge = document.getElementById('items-badge');
    const totalAmountDisplay = document.getElementById('total-amount-display');
    const btnSubmit = document.getElementById('btn-submit-invoice');
    const feedbackBox = document.getElementById('scanner-feedback');
    const feedbackText = document.getElementById('scanner-feedback-text');

    let customerModalInstance = null;

    function filterCatalogOptions() {
        const selectedType = billTypeSelect ? billTypeSelect.value : 'bee_comfort';
        const requiredParent = (selectedType === 'china') ? 'china' : 'beecomfort';

        if (quickSelect && quickSelect.tomselect) {
            quickSelect.tomselect.clear();
            quickSelect.tomselect.clearOptions();
            allProductsCatalog.forEach(p => {
                const pNorm = (p.parent_category || 'Bee Comfort').toLowerCase().replace(/\s+/g, '');
                if (pNorm === requiredParent) {
                    quickSelect.tomselect.addOption({
                        value: p.id,
                        text: `${p.title} — [SKU: ${p.sku_code}] (Rs. ${parseFloat(p.selling_price).toFixed(2)})`
                    });
                }
            });
            quickSelect.tomselect.refreshOptions(false);
        }
    }

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

        if (btnAddItem && quickSelect) {
            btnAddItem.addEventListener('click', function() {
                let prodId = quickSelect.tomselect ? quickSelect.tomselect.getValue() : quickSelect.value;
                if (prodId) {
                    const prod = allProductsCatalog.find(p => p.id == prodId);
                    if (prod) {
                        addProductToInvoice(prod);
                        if (quickSelect.tomselect) quickSelect.tomselect.clear();
                        showFeedback(`Product '${prod.title}' added to invoice.`);
                        if (scannerInput) scannerInput.focus();
                    }
                }
            });
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
            scannerInput.focus();
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
                        scannerInput.focus();
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
        feedbackText.textContent = msg;
        feedbackBox.classList.remove('d-none');
        setTimeout(() => { feedbackBox.classList.add('d-none'); }, 3000);
    }

    function addProductToInvoice(product) {
        const existing = invoiceRows.find(item => item.product_id == product.id);
        if (existing) {
            existing.quantity += 1;
        } else {
            invoiceRows.push({
                product_id: product.id,
                title: product.title,
                sku: product.sku_code,
                price: parseFloat(product.selling_price),
                stock: parseInt(product.stock_quantity),
                quantity: 1,
            });
        }
        renderInvoiceTable();
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

    function renderInvoiceTable() {
        if (invoiceRows.length === 0) {
            itemsBody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="8" class="text-center py-4 text-secondary">
                        <i class="bi bi-cart3 fs-3 text-secondary d-block mb-1"></i>
                        <span class="fw-medium">Invoice is currently empty.</span> Scan a barcode in the scan bar or select from the dropdown above.
                    </td>
                </tr>
            `;
            itemsBadge.textContent = '0 Items Added';
            totalAmountDisplay.textContent = 'Rs. 0.00';
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

            html += `
                <tr>
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
                    <td class="py-2 text-end font-monospace small">
                        Rs. ${row.price.toFixed(2)}
                    </td>
                    <td class="py-2 text-center">
                        <input type="number" name="items[${idx}][quantity]" value="${row.quantity}" min="1" required
                            onchange="updateRowQty(${idx}, this.value)"
                            class="form-control form-control-sm text-center fw-bold font-monospace mx-auto py-1" style="max-width: 75px;">
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
        totalAmountDisplay.textContent = 'Rs. ' + total.toFixed(2);
        btnSubmit.disabled = false;
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
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.customer) {
                const cust = data.customer;
                const custSelect = document.getElementById('customer_id');
                if (custSelect && custSelect.tomselect) {
                    custSelect.tomselect.addOption({
                        value: cust.id,
                        text: `${cust.name} (${cust.phone || cust.city || 'Active'})`
                    });
                    custSelect.tomselect.setValue(cust.id);
                } else if (custSelect) {
                    const opt = document.createElement('option');
                    opt.value = cust.id;
                    opt.textContent = `${cust.name} (${cust.phone || cust.city || 'Active'})`;
                    opt.selected = true;
                    custSelect.appendChild(opt);
                }
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
