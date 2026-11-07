@extends('layouts.app')

@section('title', 'Edit Invoice #' . $sale->invoice_no)

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
    <li class="breadcrumb-item active" aria-current="page">Edit #{{ $sale->invoice_no }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="h4 fw-bold text-dark mb-0">Edit Sales Invoice #{{ $sale->invoice_no }}</h1>
            <span class="badge {{ $sale->bill_type === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }} px-2.5 py-1 text-uppercase fw-bold fs-7">
                <i class="bi bi-tag-fill me-1"></i> {{ $sale->bill_type === 'china' ? 'China Bill' : 'Bee Comfort Bill' }}
            </span>
        </div>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('sales.update', $sale) }}" id="sale-form" class="d-flex flex-column gap-3">
        @csrf
        @method('PUT')

        <!-- INVOICE METADATA CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <!-- Invoice Number (Read only on edit) -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Invoice Number
                        </label>
                        <input type="text" value="{{ $sale->invoice_no }}" readonly
                            class="form-control form-control-sm font-monospace fw-bold fs-6 bg-light cursor-not-allowed">
                    </div>

                    <!-- Customer Selection -->
                    <div class="col-12 col-md-5">
                        <label for="customer_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Customer Account <span class="text-danger">*</span>
                        </label>
                        <select name="customer_id" id="customer_id" required class="searchable-select form-select form-select-sm">
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
                        <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $sale->invoice_date->format('Y-m-d')) }}" required class="form-control form-control-sm font-monospace">
                    </div>
                </div>
            </div>
        </div>

        <!-- STREAMLINED ACTION BAR: LEFT (DROPDOWN SEARCH AUTO-ADD) & RIGHT (BARCODE SCAN) -->
        <div class="card border-0 shadow-sm {{ $sale->bill_type === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }}">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    
                    <!-- LEFT SIDE: Catalog Dropdown Search (Auto-Add, No Add Button) -->
                    <div class="col-12 col-lg-7">
                        <label for="quick_product_select" class="form-label small fw-bold text-white text-uppercase mb-1 fs-7">
                            <i class="bi bi-search me-1"></i> Search &amp; Select {{ $sale->bill_type === 'china' ? 'China' : 'Bee Comfort' }} Product
                        </label>
                        <div class="text-dark">
                            <select id="quick_product_select" class="searchable-select form-select form-select-sm">
                                <option value="">Search {{ $sale->bill_type === 'china' ? 'China' : 'Bee Comfort' }} product ({{ count($products) }} items)...</option>
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
                    </div>

                    <!-- RIGHT SIDE: Barcode Scan Input -->
                    <div class="col-12 col-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="barcode_scanner_input" class="form-label small fw-bold text-white text-uppercase mb-0 fs-7">
                                <i class="bi bi-upc-scan me-1"></i> Scan {{ $sale->bill_type === 'china' ? 'China' : 'Bee Comfort' }} SKU Barcode
                            </label>
                            <span class="badge bg-white {{ $sale->bill_type === 'china' ? 'text-danger' : 'text-primary' }} fs-8 fw-bold">Active</span>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 {{ $sale->bill_type === 'china' ? 'text-danger' : 'text-primary' }}">
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
                        <span>Press <kbd class="bg-white text-dark px-1.5 py-0.5 rounded shadow-sm fw-bold">Enter</kbd> to Update Bill</span>
                    </div>
                    <div id="scanner-feedback" class="small fw-bold d-none">
                        <i class="bi bi-check-circle-fill me-1"></i> <span id="scanner-feedback-text"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- ITEMS TABLE CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">Billed Items Table</h6>
                <span id="items-badge" class="badge bg-light text-dark border px-2 py-1 fs-7 font-monospace">
                    {{ $sale->details->count() }} Items
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="invoice-items-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 40px;">#</th>
                            <th class="py-2 text-secondary text-uppercase fs-7">Product Description</th>
                            <th class="py-2 text-secondary text-uppercase fs-7" style="width: 110px;">SKU</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 120px;">Rate (Rs.)</th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 110px;">Quantity</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 130px;">Amount (Rs.)</th>
                            <th class="pe-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 50px;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body" class="border-top-0">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- TOTALS & ACTION SECTION -->
            <div class="card-footer bg-light border-top p-3">
                <div class="row g-3 align-items-center">
                    <!-- Notes -->
                    <div class="col-12 col-md-7">
                        <label for="notes" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Notes / Remarks
                        </label>
                        <input type="text" name="notes" id="notes" value="{{ old('notes', $sale->notes) }}" placeholder="Optional notes for customer or invoice records..." class="form-control form-control-sm bg-white">
                    </div>

                    <!-- Totals & Update Button -->
                    <div class="col-12 col-md-5">
                        <div class="d-flex align-items-center justify-content-between p-2.5 bg-white border rounded">
                            <div>
                                <span class="text-secondary small d-block">Total Payable:</span>
                                <span id="total-amount-display" class="h4 fw-black {{ $sale->bill_type === 'china' ? 'text-danger' : 'text-primary' }} font-monospace mb-0">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                            </div>
                            <button type="submit" id="btn-submit-invoice" class="btn {{ $sale->bill_type === 'china' ? 'btn-danger' : 'btn-primary' }} fw-bold px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Update &amp; Save
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
    const scannerInput = document.getElementById('barcode_scanner_input');
    const itemsBody = document.getElementById('invoice-items-body');
    const itemsBadge = document.getElementById('items-badge');
    const totalAmountDisplay = document.getElementById('total-amount-display');
    const btnSubmit = document.getElementById('btn-submit-invoice');
    const feedbackBox = document.getElementById('scanner-feedback');
    const feedbackText = document.getElementById('scanner-feedback-text');

    document.addEventListener('DOMContentLoaded', function () {
        renderTable();

        if (scannerInput) {
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
        const prod = allProductsCatalog.find(p => p.sku_code == sku);

        if (prod) {
            addProductToInvoice(prod);
            scannerInput.value = '';
            showFeedback(`Scanned [SKU: ${sku}]: '${prod.title}' added.`);
        } else {
            alert(`No product found with barcode SKU '${sku}' in this invoice catalog.`);
            scannerInput.select();
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
                quantity: 1,
            });
            targetIndex = invoiceRows.length - 1;
        }

        renderTable(targetIndex, isExisting);
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

    // Handles keyboard navigation on quantity input:
    // Tab -> Focus back to product dropdown
    // Enter -> Save / Update bill
    function handleQtyKeydown(e, index) {
        if (e.key === 'Tab' && !e.shiftKey) {
            e.preventDefault();
            if (quickSelect && quickSelect.tomselect) {
                quickSelect.tomselect.focus();
            } else if (quickSelect) {
                quickSelect.focus();
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            e.target.blur();
            const form = document.getElementById('sale-form');
            if (form && invoiceRows.length > 0) {
                if (form.reportValidity()) {
                    form.submit();
                }
            }
        }
    }

    function renderTable(highlightIndex = -1, isExisting = false) {
        if (invoiceRows.length === 0) {
            itemsBody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="7" class="text-center py-4 text-secondary">
                        <i class="bi bi-cart3 fs-3 text-secondary d-block mb-1"></i>
                        <span class="fw-medium">Invoice is currently empty.</span> Select a product from the left dropdown or scan barcode on the right.
                    </td>
                </tr>
            `;
            itemsBadge.textContent = '0 Items Added';
            btnSubmit.disabled = true;
            totalAmountDisplay.textContent = 'Rs. 0.00';
            return;
        }

        btnSubmit.disabled = false;
        itemsBadge.textContent = `${invoiceRows.length} Items Added`;

        let html = '';
        let total = 0.0;

        invoiceRows.forEach((item, idx) => {
            const lineTotal = item.quantity * item.price;
            total += lineTotal;
            const isTarget = (idx === highlightIndex);

            html += `
                <tr id="row-${idx}" class="${isTarget ? 'row-highlight-pulse' : ''}">
                    <td class="ps-3 py-2 text-center text-secondary small font-monospace">${idx + 1}</td>
                    <td class="py-2">
                        <span class="fw-bold text-dark d-block fs-7">${item.title}</span>
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                    </td>
                    <td class="py-2">
                        <span class="badge bg-light text-dark font-monospace border fs-8">${item.sku}</span>
                    </td>
                    <td class="py-2 text-end font-monospace small">
                        Rs. ${item.price.toFixed(2)}
                    </td>
                    <td class="py-2 text-center">
                        <input type="number" name="items[${idx}][quantity]" id="qty-input-${idx}"
                            value="${item.quantity}" min="1" required
                            onchange="updateQty(${idx}, this.value)"
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
        totalAmountDisplay.textContent = 'Rs. ' + total.toFixed(2);

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
</script>
@endpush
