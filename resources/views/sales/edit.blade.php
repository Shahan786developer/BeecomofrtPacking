@extends('layouts.app')

@section('title', 'Edit Invoice #' . $sale->invoice_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit #{{ $sale->invoice_no }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Edit Sales Invoice #{{ $sale->invoice_no }}</h1>
            <p class="text-secondary small mb-0">Modify items, quantities, or customer details. Inventory stock is automatically reconciled.</p>
        </div>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('sales.update', $sale) }}" id="sale-form" class="d-flex flex-column gap-4">
        @csrf
        @method('PUT')

        <!-- INVOICE METADATA CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Invoice Number (Read only on edit) -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice Number
                        </label>
                        <input type="text" value="{{ $sale->invoice_no }}" readonly
                            class="form-control font-monospace fw-bold fs-6 bg-light cursor-not-allowed">
                    </div>

                    <!-- Customer Selection -->
                    <div class="col-12 col-md-5">
                        <label for="customer_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Customer Account <span class="text-danger">*</span>
                        </label>
                        <select name="customer_id" id="customer_id" required class="searchable-select form-select">
                            <option value="">Select Customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone ?: 'No Phone' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-12 col-md-3">
                        <label for="invoice_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $sale->invoice_date->format('Y-m-d')) }}" required class="form-control font-monospace">
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCT SELECTION CARD (Strict Catalog Dropdown) -->
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-uppercase fs-7 text-white-50">
                        <i class="bi bi-upc me-1"></i> Add Product from Catalog
                    </span>
                    <span class="badge bg-white text-primary fw-bold">Catalog Single-Select</span>
                </div>

                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-10 text-dark">
                        <select id="quick_product_select" class="searchable-select form-select form-select-lg">
                            <option value="">Select product to add...</option>
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

                    <div class="col-12 col-md-2">
                        <button type="button" id="btn-add-item" class="btn btn-light btn-lg w-100 fw-bold text-primary shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ITEMS TABLE CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Invoice Items</h5>
                <span id="items-badge" class="badge bg-light text-dark border px-2 py-1">
                    {{ $sale->details->count() }} Items
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="invoice-items-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-center" style="width: 50px;">#</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Product Description</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Rate (Rs.)</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 130px;">Quantity</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Amount (Rs.)</th>
                            <th class="pe-4 py-3 text-center text-secondary text-uppercase fs-7" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body" class="border-top-0">
                        <!-- Populated by JavaScript -->
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
                        <textarea name="notes" id="notes" rows="3" placeholder="Optional notes for customer or invoice records..." class="form-control bg-white">{{ old('notes', $sale->notes) }}</textarea>
                    </div>

                    <!-- Totals -->
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-4 bg-white d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Subtotal:</span>
                                <span id="subtotal-display" class="fw-bold font-monospace text-dark fs-6">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <span class="h5 fw-bold text-dark mb-0">Total Amount:</span>
                                <span id="total-amount-display" class="h3 fw-black text-primary font-monospace mb-0">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                            </div>

                            <button type="submit" id="btn-submit-invoice" class="btn btn-primary btn-lg fw-bold w-100 shadow-sm mt-2">
                                <i class="bi bi-check2-circle me-1"></i> Update & Save Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    const allProductsCatalog = {!! json_encode($products) !!};
    let invoiceRows = [
        @foreach($sale->details as $d)
        {
            product_id: {{ $d->product_id }},
            title: "{{ addslashes($d->product_title) }}",
            sku: "{{ $d->sku_code }}",
            price: {{ (float) $d->unit_price }},
            quantity: {{ (int) $d->quantity }},
        },
        @endforeach
    ];

    const quickSelect = document.getElementById('quick_product_select');
    const btnAddItem = document.getElementById('btn-add-item');
    const itemsBody = document.getElementById('invoice-items-body');
    const itemsBadge = document.getElementById('items-badge');
    const subtotalDisplay = document.getElementById('subtotal-display');
    const totalAmountDisplay = document.getElementById('total-amount-display');
    const btnSubmit = document.getElementById('btn-submit-invoice');

    document.addEventListener('DOMContentLoaded', function () {
        renderTable();

        btnAddItem.addEventListener('click', function() {
            let prodId = quickSelect.tomselect ? quickSelect.tomselect.getValue() : quickSelect.value;
            if (prodId) {
                const prod = allProductsCatalog.find(p => p.id == prodId);
                if (prod) {
                    addProductToInvoice(prod);
                    if (quickSelect.tomselect) quickSelect.tomselect.clear();
                }
            }
        });
    });

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
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-cart3 display-6 text-secondary d-block mb-2"></i>
                        <span class="fw-medium">Invoice is currently empty.</span> Add products from above.
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
</script>
@endpush
