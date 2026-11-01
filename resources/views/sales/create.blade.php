@extends('layouts.app')

@section('title', 'New Sales Invoice')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Invoice</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">New Sales Invoice</h1>
            <p class="text-secondary small mb-0">Scan barcodes or select products to generate customer bill. Totals in Rupees (Rs.).</p>
        </div>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('sales.store') }}" id="sale-form" class="d-flex flex-column gap-4">
        @csrf

        <!-- INVOICE METADATA CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Invoice Number -->
                    <div class="col-12 col-md-4">
                        <label for="invoice_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="invoice_no" id="invoice_no" value="{{ old('invoice_no', $nextInvoiceNo) }}" required
                            class="form-control font-monospace fw-bold fs-6">
                    </div>

                    <!-- Customer Selection -->
                    <div class="col-12 col-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="customer_id" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                Customer Account <span class="text-danger">*</span>
                            </label>
                            <button type="button" onclick="openNewCustomerModal()" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-bold">
                                <i class="bi bi-person-plus me-1"></i> + New Customer
                            </button>
                        </div>
                        <select name="customer_id" id="customer_id" required class="searchable-select form-select">
                            <option value="">Select Customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone ?: ($customer->city ?: 'No Contact') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-12 col-md-3">
                        <label for="invoice_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $today) }}" required class="form-control font-monospace">
                    </div>
                </div>
            </div>
        </div>

        <!-- BARCODE SCAN BAR & PRODUCT SELECTION -->
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body p-4">
                <div class="row g-3 align-items-center">
                    <!-- 1. Dedicated Barcode Scan Bar -->
                    <div class="col-12 col-lg-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="barcode_scanner_input" class="form-label small fw-bold text-white text-uppercase mb-0">
                                <i class="bi bi-upc-scan me-1"></i> Scan Barcode (7-Digit SKU)
                            </label>
                            <span class="badge bg-white text-primary fw-bold">Scan Bar Active</span>
                        </div>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0 text-primary">
                                <i class="bi bi-upc"></i>
                            </span>
                            <input type="text" id="barcode_scanner_input" autofocus
                                placeholder="Scan barcode with scanner or type SKU..." 
                                class="form-control border-0 font-monospace fw-bold text-dark"
                                autocomplete="off">
                        </div>
                        <div class="form-text text-white-50 small mt-1">
                            Scan or enter SKU to automatically add product to table
                        </div>
                    </div>

                    <!-- 2. Catalog Single-Select Dropdown -->
                    <div class="col-12 col-lg-6">
                        <label for="quick_product_select" class="form-label small fw-bold text-white text-uppercase mb-1">
                            <i class="bi bi-search me-1"></i> Or Select from Catalog
                        </label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1 text-dark">
                                <select id="quick_product_select" class="searchable-select form-select form-select-lg">
                                    <option value="">Select registered product from catalog...</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod['id'] }}" 
                                            data-id="{{ $prod['id'] }}"
                                            data-sku="{{ $prod['sku_code'] }}"
                                            data-title="{{ $prod['title'] }}"
                                            data-price="{{ $prod['selling_price'] }}"
                                            data-stock="{{ $prod['stock_quantity'] }}">
                                            {{ $prod['title'] }} — [SKU: {{ $prod['sku_code'] }}] (Rs. {{ number_format($prod['selling_price'], 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" id="btn-add-item" class="btn btn-light btn-lg fw-bold text-primary shadow-sm flex-shrink-0">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        </div>
                        <div class="form-text text-white-50 small mt-1">
                            Strict catalog single-select (choose 1 product)
                        </div>
                    </div>
                </div>

                <div id="scanner-feedback" class="mt-2 text-white small d-none">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="scanner-feedback-text"></span>
                </div>
            </div>
        </div>

        <!-- ITEMS TABLE CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Invoice Items</h5>
                <span id="items-badge" class="badge bg-light text-dark border px-2 py-1">
                    0 Items Added
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="invoice-items-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-center" style="width: 50px;">#</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Product Description (Unified Title)</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Stock</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Rate (Rs.)</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 130px;">Quantity</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Amount (Rs.)</th>
                            <th class="pe-4 py-3 text-center text-secondary text-uppercase fs-7" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body" class="border-top-0">
                        <tr id="empty-row">
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="bi bi-cart3 display-6 text-secondary d-block mb-2"></i>
                                <span class="fw-medium">Invoice is currently empty.</span> Scan a barcode in the scan bar or select from the dropdown above.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TOTALS & ACTION SECTION -->
            <div class="card-footer bg-light border-top p-4">
                <div class="row g-4 items-start">
                    <!-- Notes -->
                    <div class="col-12 col-lg-7">
                        <label for="notes" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Notes / Remarks
                        </label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Optional notes for customer or invoice records..." class="form-control bg-white"></textarea>
                    </div>

                    <!-- Totals (Discount removed) -->
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-4 bg-white d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Subtotal:</span>
                                <span id="subtotal-display" class="fw-bold font-monospace text-dark fs-6">Rs. 0.00</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <span class="h5 fw-bold text-dark mb-0">Total Amount:</span>
                                <span id="total-amount-display" class="h3 fw-black text-primary font-monospace mb-0">Rs. 0.00</span>
                            </div>

                            <button type="submit" id="btn-submit-invoice" disabled class="btn btn-primary btn-lg fw-bold w-100 shadow-sm mt-2">
                                <i class="bi bi-check2-circle me-1"></i> Save Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

<!-- QUICK ADD CUSTOMER MODAL (Fields: Name, Email, City, Phone, Address) -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="newCustomerModalLabel">
                    <i class="bi bi-person-plus text-primary me-2"></i> Register Customer Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" id="modal_cust_name" required placeholder="Full Name or Business Name" class="form-control fw-bold">
                </div>
                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Email</label>
                    <input type="email" id="modal_cust_email" placeholder="customer@domain.com" class="form-control">
                </div>
                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">City</label>
                    <input type="text" id="modal_cust_city" placeholder="e.g. Lahore, Karachi, Rawalpindi" class="form-control">
                </div>
                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Phone Number</label>
                    <input type="text" id="modal_cust_phone" placeholder="+92 300 0000000" class="form-control font-monospace">
                </div>
                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Address</label>
                    <input type="text" id="modal_cust_address" placeholder="Physical shop or billing address" class="form-control">
                </div>
            </div>

            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="saveNewCustomerAjax()" class="btn btn-primary fw-bold">Save Customer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const allProductsCatalog = {!! json_encode($products) !!};
    let invoiceRows = [];

    const scannerInput = document.getElementById('barcode_scanner_input');
    const quickSelect = document.getElementById('quick_product_select');
    const btnAddItem = document.getElementById('btn-add-item');
    const itemsBody = document.getElementById('invoice-items-body');
    const itemsBadge = document.getElementById('items-badge');
    const subtotalDisplay = document.getElementById('subtotal-display');
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
    });

    function processScan(sku) {
        if (!sku) return;

        const prod = allProductsCatalog.find(p => p.sku_code == sku);
        if (prod) {
            addProductToInvoice(prod);
            scannerInput.value = '';
            scannerInput.focus();
            showFeedback(`Scanned [SKU: ${sku}]: '${prod.title}' added.`);
        } else {
            fetch(`/api/products/by-sku/${encodeURIComponent(sku)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.found) {
                        addProductToInvoice({
                            id: data.id,
                            sku_code: data.sku_code,
                            title: data.title,
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
        renderTable();
    }

    function removeRow(idx) {
        invoiceRows.splice(idx, 1);
        renderTable();
    }

    function updateQty(idx, val) {
        let q = parseInt(val) || 1;
        if (q < 1) q = 1;
        invoiceRows[idx].quantity = q;
        renderTable();
    }

    function renderTable() {
        if (invoiceRows.length === 0) {
            itemsBody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="8" class="text-center py-5 text-secondary">
                        <i class="bi bi-cart3 display-6 text-secondary d-block mb-2"></i>
                        <span class="fw-medium">Invoice is currently empty.</span> Scan a barcode in the scan bar or select from the dropdown above.
                    </td>
                </tr>
            `;
            itemsBadge.textContent = '0 Items Added';
            btnSubmit.disabled = true;
            calculateTotals();
            return;
        }

        btnSubmit.disabled = false;
        itemsBadge.textContent = `${invoiceRows.length} Items Added`;

        let html = '';
        invoiceRows.forEach((item, idx) => {
            const lineTotal = (item.quantity * item.price).toFixed(2);

            html += `
            <tr>
                <td class="ps-4 py-3 text-center text-secondary">${idx + 1}</td>
                <td class="py-3">
                    <span class="fw-bold text-dark d-block fs-6">${item.title}</span>
                    <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                </td>
                <td class="py-3">
                    <span class="badge bg-light text-dark font-monospace border px-2 py-1">${item.sku}</span>
                </td>
                <td class="py-3 text-center">
                    <span class="badge ${item.stock > 0 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">
                        ${item.stock} in stock
                    </span>
                </td>
                <td class="py-3 text-end font-monospace fw-bold text-secondary">
                    Rs. ${item.price.toFixed(2)}
                </td>
                <td class="py-3 text-center">
                    <input type="number" name="items[${idx}][quantity]" value="${item.quantity}" min="1" required
                        onchange="updateQty(${idx}, this.value)"
                        class="form-control form-control-sm text-center fw-bold font-monospace mx-auto" style="max-width: 80px;">
                </td>
                <td class="py-3 text-end font-monospace fw-bold text-dark fs-6">
                    Rs. ${lineTotal}
                </td>
                <td class="pe-4 py-3 text-center">
                    <button type="button" onclick="removeRow(${idx})" class="btn btn-sm btn-outline-danger" title="Remove Item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            `;
        });

        itemsBody.innerHTML = html;
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        invoiceRows.forEach(item => {
            subtotal += (item.quantity * item.price);
        });

        subtotalDisplay.textContent = 'Rs. ' + subtotal.toFixed(2);
        totalAmountDisplay.textContent = 'Rs. ' + subtotal.toFixed(2);
    }

    function openNewCustomerModal() {
        if (!customerModalInstance) {
            customerModalInstance = new bootstrap.Modal(document.getElementById('newCustomerModal'));
        }
        customerModalInstance.show();
    }

    function closeNewCustomerModal() {
        if (customerModalInstance) customerModalInstance.hide();
    }

    function saveNewCustomerAjax() {
        const name = document.getElementById('modal_cust_name').value.trim();
        const email = document.getElementById('modal_cust_email').value.trim();
        const city = document.getElementById('modal_cust_city').value.trim();
        const phone = document.getElementById('modal_cust_phone').value.trim();
        const address = document.getElementById('modal_cust_address').value.trim();

        if (!name) {
            alert('Customer name is required.');
            return;
        }

        fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name, email, city, phone, address })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const customerSelect = document.getElementById('customer_id');
                if (customerSelect.tomselect) {
                    customerSelect.tomselect.addOption({
                        value: data.customer.id,
                        text: `${data.customer.name} (${data.customer.phone || data.customer.city || 'No Phone'})`
                    });
                    customerSelect.tomselect.setValue(data.customer.id);
                }
                closeNewCustomerModal();
            }
        });
    }
</script>
@endpush
