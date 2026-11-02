@extends('layouts.app')

@section('title', $returnType === 'china' ? 'Process China Bill Return' : 'Process Bee Comfort Bill Return')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Bill Returns</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $returnType === 'china' ? 'New China Return' : 'New Bee Comfort Return' }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold text-dark mb-0">
                    {{ $returnType === 'china' ? 'Process China Bill Return' : 'Process Bee Comfort Bill Return' }}
                </h1>
                <span class="badge {{ $returnType === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }} px-2.5 py-1 text-uppercase fw-bold fs-7">
                    <i class="bi bi-lock-fill me-1"></i> {{ $returnType === 'china' ? 'China Category Locked' : 'Bee Comfort Category Locked' }}
                </span>
            </div>
            <p class="text-secondary small mb-0">
                {{ $returnType === 'china' ? 'Only China sales invoices are listed. Merchandise returns are locked to prevent cross-bill conflicts.' : 'Only Bee Comfort sales invoices are listed. Merchandise returns are locked to prevent cross-bill conflicts.' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(!isset($selectedSale))
                @if($returnType === 'china')
                    <a href="{{ route('returns.create', ['return_type' => 'bee_comfort']) }}" class="btn btn-outline-primary btn-sm fw-bold">
                        <i class="bi bi-arrow-left-right me-1"></i> Switch to Bee Comfort Return
                    </a>
                @else
                    <a href="{{ route('returns.create', ['return_type' => 'china']) }}" class="btn btn-outline-danger btn-sm fw-bold">
                        <i class="bi bi-arrow-left-right me-1"></i> Switch to China Return
                    </a>
                @endif
            @endif
            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Returns
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('returns.store') }}" id="return-form" class="d-flex flex-column gap-4">
        @csrf

        <!-- INVOICE SELECTION CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                @if(isset($selectedSale))
                    <!-- Locked Mode: Return initiated directly from Sales Invoice -->
                    <input type="hidden" name="return_type" value="{{ $returnType }}">
                    <input type="hidden" name="sale_id" value="{{ $selectedSale->id }}">

                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-3">
                            <span class="form-label small fw-bold text-secondary text-uppercase d-block mb-1">Return Category</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $returnType === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }} px-3 py-2 fs-7">
                                    <i class="bi bi-tag-fill me-1"></i> {{ $returnType === 'china' ? 'China Return' : 'Bee Comfort Return' }}
                                </span>
                                <i class="bi bi-lock-fill text-muted" title="Locked to Original Invoice"></i>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <span class="form-label small fw-bold text-secondary text-uppercase d-block mb-1">Original Invoice (Locked)</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold font-monospace text-primary fs-6">{{ $selectedSale->invoice_no }}</span>
                                <span class="badge bg-light text-secondary border font-monospace">Rs. {{ number_format($selectedSale->total_amount, 2) }}</span>
                                <i class="bi bi-lock-fill text-muted" title="Locked to Original Invoice"></i>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <span class="form-label small fw-bold text-secondary text-uppercase d-block mb-1">Billed Customer (Locked)</span>
                            <div class="fw-bold text-dark fs-6">{{ $selectedSale->customer->name }}</div>
                            @if($selectedSale->customer->owner_name)
                                <span class="text-secondary small d-block">Owner: <strong class="text-dark">{{ $selectedSale->customer->owner_name }}</strong></span>
                            @endif
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="return_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">Return No</label>
                                    <input type="text" name="return_no" id="return_no" value="{{ old('return_no', $nextReturnNo) }}" required class="form-control font-monospace fw-bold bg-light form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label for="return_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">Date</label>
                                    <input type="date" name="return_date" id="return_date" value="{{ old('return_date', $today) }}" required class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Manual Mode: Select from available invoices for this return_type -->
                    <div class="row g-3">
                        <!-- 0. Return Category (Locked to avoid conflict) -->
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Return Category (Locked)
                            </label>
                            <div class="d-flex align-items-center justify-content-between p-2 {{ $returnType === 'china' ? 'bg-danger-subtle border border-danger-subtle' : 'bg-primary-subtle border border-primary-subtle' }} rounded">
                                <span class="badge {{ $returnType === 'china' ? 'bg-danger text-white' : 'bg-primary text-white' }} fs-7 text-uppercase fw-bold">
                                    {{ $returnType === 'china' ? 'China Return' : 'Bee Comfort Return' }}
                                </span>
                                <i class="bi bi-lock-fill {{ $returnType === 'china' ? 'text-danger' : 'text-primary' }}" title="Category locked to prevent cross-bill conflict"></i>
                            </div>
                            <input type="hidden" name="return_type" id="return_type" value="{{ $returnType }}">
                        </div>

                        <!-- 1. Original Invoice (Filtered to this Return Category) -->
                        <div class="col-12 col-md-4">
                            <label for="sale_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Original {{ $returnType === 'china' ? 'China' : 'Bee Comfort' }} Invoice <span class="text-danger">*</span>
                            </label>
                            <select name="sale_id" id="sale_id" required class="searchable-select form-select">
                                <option value="">Select {{ $returnType === 'china' ? 'China' : 'Bee Comfort' }} Invoice ({{ count($sales) }} available)...</option>
                                @foreach($sales as $sale)
                                    <option value="{{ $sale->id }}" {{ (isset($selectedSaleId) && $selectedSaleId == $sale->id) ? 'selected' : ($loop->first ? 'selected' : '') }}>
                                        {{ $sale->invoice_no }} — {{ $sale->customer->name }} (Rs. {{ number_format($sale->total_amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Return Number -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="return_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Return Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="return_no" id="return_no" value="{{ old('return_no', $nextReturnNo) }}" required class="form-control font-monospace fw-bold bg-light">
                        </div>

                        <!-- 3. Return Date -->
                        <div class="col-12 col-sm-6 col-md-2">
                            <label for="return_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Return Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="return_date" id="return_date" value="{{ old('return_date', $today) }}" required class="form-control fw-semibold">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- SCAN RETURNED PRODUCT BARCODE -->
        <div class="card border-0 shadow-sm bg-warning-subtle border border-warning-subtle">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-uppercase fs-7 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-upc-scan fs-5 text-warning-emphasis"></i> Scan Returned Product (SKU Barcode)
                    </span>
                    <span class="badge bg-warning text-dark fw-bold">Return Scanner Active</span>
                </div>

                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-0 text-warning-emphasis"><i class="bi bi-upc"></i></span>
                            <input type="text" id="return_scanner_input" placeholder="Scan or enter returned 7-digit SKU..." class="form-control border-0 font-monospace fw-bold text-dark">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-secondary small">
                            Scanning an SKU automatically selects the line item and checks eligible quantity against the invoice.
                        </span>
                    </div>
                </div>

                <div id="return-scanner-feedback" class="mt-2 text-warning-emphasis small d-none">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="return-feedback-text"></span>
                </div>
            </div>
        </div>

        <!-- ITEMS RETURN TABLE (Requirement #5) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Merchandise on Selected Invoice</h5>
                <span id="invoice-items-count-badge" class="badge bg-light text-dark border px-2 py-1">
                    Loading items...
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="return-items-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-center" style="width: 70px;">Return?</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Product Description (Unified Title)</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Sold</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Already Returned</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Eligible</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Rate (Rs.)</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 120px;">Return Qty</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Refund (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body" class="border-top-0">
                        <tr>
                            <td colspan="9" class="text-center py-5 text-secondary">
                                Select an invoice to load merchandise items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- REASON & TOTALS -->
            <div class="card-footer bg-light border-top p-4">
                <div class="row g-4 items-start">
                    <div class="col-12 col-lg-7">
                        <label for="reason" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Reason for Return
                        </label>
                        <textarea name="reason" id="reason" rows="3" placeholder="Optional explanation or customer return feedback..." class="form-control bg-white"></textarea>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-4 bg-white d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Units Returned:</span>
                                <span id="total-qty-return-display" class="fw-bold font-monospace text-dark fs-6">0 units</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <span class="h5 fw-bold text-dark mb-0">Total Refund:</span>
                                <span id="total-return-amount-display" class="h3 fw-black text-danger font-monospace mb-0">Rs. 0.00</span>
                            </div>

                            <button type="submit" id="btn-submit-return" disabled class="btn btn-warning btn-lg fw-bold w-100 shadow-sm mt-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Confirm Return & Restore Stock
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
    let currentInvoiceItems = [];

    const saleSelect = document.getElementById('sale_id');
    const returnScannerInput = document.getElementById('return_scanner_input');
    const returnItemsBody = document.getElementById('return-items-body');
    const countBadge = document.getElementById('invoice-items-count-badge');
    const totalQtyDisplay = document.getElementById('total-qty-return-display');
    const totalReturnDisplay = document.getElementById('total-return-amount-display');
    const btnSubmit = document.getElementById('btn-submit-return');
    const feedbackBox = document.getElementById('return-scanner-feedback');
    const feedbackText = document.getElementById('return-feedback-text');

    document.addEventListener('DOMContentLoaded', function () {
        function loadItems() {
            let saleId = null;
            if (saleSelect) {
                saleId = saleSelect.tomselect ? saleSelect.tomselect.getValue() : saleSelect.value;
            } else {
                const hiddenSale = document.querySelector('input[name="sale_id"]');
                if (hiddenSale) saleId = hiddenSale.value;
            }

            if (!saleId) {
                returnItemsBody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-secondary">Please select an invoice first.</td></tr>`;
                countBadge.textContent = 'No Invoice Selected';
                return;
            }

            countBadge.textContent = 'Loading items...';

            fetch(`/api/sales/${saleId}/items`)
                .then(r => r.json())
                .then(data => {
                    if (data.found) {
                        currentInvoiceItems = data.items.map(it => ({
                            ...it,
                            selected: false,
                            return_qty: 1,
                        }));
                        renderReturnTable();
                    }
                })
                .catch(err => alert('Error loading items: ' + err));
        }

        setTimeout(() => {
            if (saleSelect && saleSelect.tomselect) {
                saleSelect.tomselect.on('change', loadItems);
            } else if (saleSelect) {
                saleSelect.addEventListener('change', loadItems);
            }
            loadItems();
        }, 300);

        returnScannerInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                matchSku(this.value.trim());
            }
        });

        returnScannerInput.addEventListener('input', function () {
            const val = this.value.trim();
            if (/^\d{7}$/.test(val)) matchSku(val);
        });
    });

    function matchSku(sku) {
        if (!sku) return;
        const item = currentInvoiceItems.find(it => it.sku_code == sku);
        if (item) {
            if (item.eligible_quantity <= 0) {
                alert(`All units for '${item.product_title}' have already been returned.`);
                returnScannerInput.value = '';
                returnScannerInput.focus();
                return;
            }
            if (item.selected) {
                if (item.return_qty < item.eligible_quantity) {
                    item.return_qty += 1;
                } else {
                    alert(`Maximum eligible return quantity reached for '${item.product_title}' (${item.eligible_quantity} units).`);
                }
            } else {
                item.selected = true;
                item.return_qty = 1;
            }
            renderReturnTable();
            returnScannerInput.value = '';
            returnScannerInput.focus();

            feedbackText.textContent = `Matched '${item.product_title}' (Return Qty: ${item.return_qty}).`;
            feedbackBox.classList.remove('d-none');
            setTimeout(() => { feedbackBox.classList.add('d-none'); }, 3000);
        } else {
            alert(`SKU '${sku}' was not found on this invoice.`);
            returnScannerInput.value = '';
            returnScannerInput.focus();
        }
    }

    function toggleSelect(idx) {
        currentInvoiceItems[idx].selected = !currentInvoiceItems[idx].selected;
        renderReturnTable();
    }

    function updateReturnQty(idx, val) {
        let q = parseInt(val) || 1;
        const item = currentInvoiceItems[idx];
        if (q < 1) q = 1;
        if (q > item.eligible_quantity) {
            alert(`Maximum returnable quantity is ${item.eligible_quantity}.`);
            q = item.eligible_quantity;
        }
        item.return_qty = q;
        renderReturnTable();
    }

    function renderReturnTable() {
        if (currentInvoiceItems.length === 0) {
            returnItemsBody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-secondary">No items on this invoice.</td></tr>`;
            countBadge.textContent = '0 Items';
            btnSubmit.disabled = true;
            calcTotals();
            return;
        }

        countBadge.textContent = `${currentInvoiceItems.length} Items on Invoice`;

        let html = '';
        let hasSelected = false;

        currentInvoiceItems.forEach((item, idx) => {
            const isEligible = item.eligible_quantity > 0;
            const isChecked = item.selected && isEligible;
            if (isChecked) hasSelected = true;

            const lineRefund = isChecked ? (item.return_qty * item.unit_price).toFixed(2) : '0.00';

            html += `
            <tr class="${isChecked ? 'table-warning' : ''}">
                <td class="ps-4 py-3 text-center align-middle">
                    <input type="checkbox" ${isChecked ? 'checked' : ''} ${!isEligible ? 'disabled' : ''}
                        onchange="toggleSelect(${idx})"
                        class="form-check-input" style="width: 24px; height: 24px; cursor: pointer;">
                    ${isChecked ? `<input type="hidden" name="items[${idx}][sale_detail_id]" value="${item.sale_detail_id}">` : ''}
                </td>
                <td class="py-3">
                    <span class="fw-bold text-dark d-block fs-6">${item.product_title}</span>
                </td>
                <td class="py-3">
                    <span class="badge bg-light text-dark font-monospace border px-2 py-1">${item.sku_code}</span>
                </td>
                <td class="py-3 text-center font-bold text-dark">${item.quantity_sold}</td>
                <td class="py-3 text-center text-secondary">${item.returned_quantity}</td>
                <td class="py-3 text-center">
                    ${isEligible 
                        ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">${item.eligible_quantity} units</span>`
                        : `<span class="badge bg-light text-secondary border small">Fully Returned</span>`
                    }
                </td>
                <td class="py-3 text-end font-monospace fw-bold text-secondary">Rs. ${item.unit_price.toFixed(2)}</td>
                <td class="py-3 text-center">
                    ${isChecked 
                        ? `<input type="number" name="items[${idx}][quantity]" value="${item.return_qty}" min="1" max="${item.eligible_quantity}" required
                            onchange="updateReturnQty(${idx}, this.value)"
                            class="form-control form-control-sm text-center fw-bold font-monospace mx-auto" style="max-width: 80px;">`
                        : `<span class="text-secondary">—</span>`
                    }
                </td>
                <td class="pe-4 py-3 text-end font-monospace fw-bold fs-6 ${isChecked ? 'text-danger' : 'text-secondary'}">
                    Rs. ${lineRefund}
                </td>
            </tr>
            `;
        });

        returnItemsBody.innerHTML = html;
        btnSubmit.disabled = !hasSelected;
        calcTotals();
    }

    function calcTotals() {
        let totalRefund = 0;
        let totalUnits = 0;

        currentInvoiceItems.forEach(item => {
            if (item.selected && item.eligible_quantity > 0) {
                totalUnits += item.return_qty;
                totalRefund += (item.return_qty * item.unit_price);
            }
        });

        totalQtyDisplay.textContent = `${totalUnits} unit(s)`;
        totalReturnDisplay.textContent = 'Rs. ' + totalRefund.toFixed(2);
    }
</script>
@endpush
