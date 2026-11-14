@extends('layouts.app')

@section('title', 'New Customer Bill Return')

@push('styles')
<style>
    @keyframes rowHighlightPulse {
        0% { background-color: #fef08a; }
        50% { background-color: #fed7aa; }
        100% { background-color: transparent; }
    }
    .row-highlight-pulse {
        animation: rowHighlightPulse 1.2s ease-in-out;
    }

    @keyframes qtyPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.22); background-color: #fef3c7; border-color: #d97706; }
        100% { transform: scale(1); }
    }
    .qty-animated {
        animation: qtyPulse 0.8s ease-in-out;
        border-color: #d97706 !important;
        font-weight: 900 !important;
    }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Bill Returns</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Bill Return</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="h4 fw-bold text-dark mb-0">
                @if(isset($returnType) && $returnType === 'china')
                    Process China Bill Return
                @elseif(isset($returnType) && $returnType === 'bee_comfort')
                    Process Bee Comfort Bill Return
                @else
                    New Customer Bill Return
                @endif
            </h1>
            <span class="badge bg-warning text-dark px-2.5 py-1 text-uppercase fw-bold fs-7">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Direct Return Intake
            </span>
            <span class="text-secondary small">
                ({{ count($products) }} products available)
            </span>
        </div>
        <div>
            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Returns List
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('returns.store') }}" id="return-form" class="d-flex flex-column gap-3">
        @csrf
        <input type="hidden" name="return_type" value="{{ $returnType ?? '' }}">
        @if(isset($selectedSale) && $selectedSale)
            <input type="hidden" name="sale_id" value="{{ $selectedSale->id }}">
            <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                <i class="bi bi-link-45deg fs-5"></i>
                <div>
                    <strong>Locked to Original Invoice:</strong> {{ $selectedSale->invoice_no }} &bull; Customer: {{ $selectedSale->customer->name }}
                </div>
            </div>
        @endif

        <!-- 1. METADATA CARD (Customer, Return No, Date) -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    
                    <!-- Customer Selection -->
                    <div class="col-12 col-md-5">
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
                                <option value="{{ $customer->id }}" {{ (old('customer_id', $selectedCustomerId) == $customer->id) ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone ?: ($customer->city ?: 'No Contact') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Return Number -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="return_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Return Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="return_no" id="return_no" value="{{ old('return_no', $nextReturnNo) }}" required
                            class="form-control form-control-sm font-monospace fw-bold">
                    </div>

                    <!-- Return Date -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="return_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Return Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="return_date" id="return_date" value="{{ old('return_date', $today) }}" required class="form-control form-control-sm font-monospace">
                    </div>

                </div>
            </div>
        </div>

        <!-- 2. STREAMLINED ACTION BAR: LEFT (DROPDOWN SEARCH AUTO-ADD) & RIGHT (BARCODE SCAN) -->
        <div class="card border-0 shadow-sm bg-warning text-dark border-0">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    
                    <!-- LEFT SIDE: Catalog Dropdown Search (Auto-Add, No Add Button) -->
                    <div class="col-12 col-lg-7">
                        <label for="quick_product_select" class="form-label small fw-bold text-dark text-uppercase mb-1 fs-7">
                            <i class="bi bi-search me-1"></i> Search &amp; Select Product to Return
                        </label>
                        <div class="text-dark">
                            <select id="quick_product_select" class="searchable-select form-select form-select-sm">
                                <option value="">Search product from all catalog items ({{ count($products) }} items)...</option>
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
                            <label for="barcode_scanner_input" class="form-label small fw-bold text-dark text-uppercase mb-0 fs-7">
                                <i class="bi bi-upc-scan me-1"></i> Scan Returned SKU Barcode
                            </label>
                            <span class="badge bg-dark text-white fs-8 fw-bold">Return Scanner</span>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 text-dark">
                                <i class="bi bi-upc"></i>
                            </span>
                            <input type="text" id="barcode_scanner_input"
                                placeholder="Scan 7-digit SKU with barcode gun..." 
                                class="form-control border-0 font-monospace fw-bold text-dark"
                                autocomplete="off">
                        </div>
                    </div>

                </div>

                <!-- FAST RETURN BILLING SHORTCUTS BANNER & FEEDBACK -->
                <div class="mt-2.5 pt-2 border-top border-dark-subtle d-flex flex-wrap justify-content-between align-items-center small text-dark">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold"><i class="bi bi-lightning-charge-fill text-danger me-0.5"></i> Fast Return Billing:</span>
                        <span>Auto-adds on select</span> &bull;
                        <span>Auto-focuses Qty</span> &bull;
                        <span>Press <kbd class="bg-dark text-white px-1.5 py-0.5 rounded shadow-sm fw-bold">Tab</kbd> for Next Item</span> &bull;
                        <span>Press <kbd class="bg-dark text-white px-1.5 py-0.5 rounded shadow-sm fw-bold">Enter</kbd> to Save Return Bill</span>
                    </div>
                    <div id="scanner-feedback" class="small fw-bold d-none">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i> <span id="scanner-feedback-text"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. RETURNED ITEMS TABLE -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">Returned Merchandise Table</h6>
                <span id="items-badge" class="badge bg-light text-dark border px-2 py-1 fs-7 font-monospace">
                    0 Items Added
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="return-items-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 40px;">#</th>
                            <th class="py-2 text-secondary text-uppercase fs-7">Product Description (Unified Title)</th>
                            <th class="py-2 text-secondary text-uppercase fs-7" style="width: 110px;">SKU</th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 90px;">Stock</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 120px;">Rate (Rs.)</th>
                            <th class="py-2 text-center text-secondary text-uppercase fs-7" style="width: 120px;">Return Qty</th>
                            <th class="py-2 text-end text-secondary text-uppercase fs-7" style="width: 130px;">Refund (Rs.)</th>
                            <th class="pe-3 py-2 text-center text-secondary text-uppercase fs-7" style="width: 50px;">Del</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body" class="border-top-0">
                        <tr id="empty-row">
                            <td colspan="8" class="text-center py-4 text-secondary">
                                <i class="bi bi-arrow-counterclockwise fs-3 text-secondary d-block mb-1"></i>
                                <span class="fw-medium">No returned products added yet.</span> Select from the left dropdown or scan SKU on the right.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 4. COMPACT FOOTER: REMARKS & RETURN TOTAL -->
            <div class="card-footer bg-light border-top p-3">
                <div class="row g-3 align-items-center">
                    <!-- Reason / Remarks -->
                    <div class="col-12 col-md-7">
                        <label for="reason" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Reason / Return Remarks
                        </label>
                        <input type="text" name="reason" id="reason" placeholder="e.g. Defective packaging, excess units, exchange..." class="form-control form-control-sm bg-white">
                    </div>

                    <!-- Total & Save Button -->
                    <div class="col-12 col-md-5">
                        <div class="d-flex align-items-center justify-content-between p-2.5 bg-white border rounded">
                            <div>
                                <span class="text-secondary small d-block">Total Refund Amount:</span>
                                <span id="total-amount-display" class="h4 fw-black text-danger font-monospace mb-0">Rs. 0.00</span>
                            </div>
                            <button type="submit" id="btn-submit-return" disabled class="btn btn-warning fw-bold px-4 shadow-sm text-dark">
                                <i class="bi bi-check2-circle me-1"></i> Save Return Bill
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
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">City / Town</label>
                        <input type="text" id="modal_cust_city" placeholder="e.g. Lahore, Faisalabad" class="form-control form-control-sm">
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
    let returnRows = [];

    const customerSelect = document.getElementById('customer_id');
    const scannerInput = document.getElementById('barcode_scanner_input');
    const quickSelect = document.getElementById('quick_product_select');
    const itemsBody = document.getElementById('return-items-body');
    const itemsBadge = document.getElementById('items-badge');
    const totalAmountDisplay = document.getElementById('total-amount-display');
    const btnSubmit = document.getElementById('btn-submit-return');
    const feedbackBox = document.getElementById('scanner-feedback');
    const feedbackText = document.getElementById('scanner-feedback-text');

    let customerModalInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
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
                    addProductToReturn(prod);
                    if (quickSelect.tomselect) {
                        quickSelect.tomselect.clear(true);
                    } else {
                        quickSelect.value = '';
                    }
                    showFeedback(`Product '${prod.title}' added to return.`);
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
            addProductToReturn(prod);
            scannerInput.value = '';
            showFeedback(`Scanned [SKU: ${sku}]: '${prod.title}' added.`);
        } else {
            fetch(`/api/products/by-sku/${encodeURIComponent(sku)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.found) {
                        addProductToReturn({
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

    function addProductToReturn(product) {
        let targetIndex = -1;
        let isExisting = false;

        const existingIdx = returnRows.findIndex(item => item.product_id == product.id);
        if (existingIdx > -1) {
            returnRows[existingIdx].quantity += 1;
            targetIndex = existingIdx;
            isExisting = true;
        } else {
            returnRows.push({
                product_id: product.id,
                title: product.title,
                sku: product.sku_code,
                price: parseFloat(product.selling_price),
                stock: parseInt(product.stock_quantity),
                quantity: 1,
            });
            targetIndex = returnRows.length - 1;
        }

        renderReturnTable(targetIndex, isExisting);
    }

    function removeRow(index) {
        returnRows.splice(index, 1);
        renderReturnTable();
    }

    function updateRowQty(index, newQty) {
        let q = parseInt(newQty) || 1;
        if (q < 1) q = 1;
        returnRows[index].quantity = q;
        renderReturnTable();
    }

    function updateRowRate(index, newRate) {
        let r = parseFloat(newRate) || 0;
        returnRows[index].price = r;
        renderReturnTable();
    }

    // Handles keyboard navigation on quantity input:
    // Tab -> Focus back to product dropdown
    // Enter -> Save / Generate return bill
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
            const form = document.getElementById('return-form');
            if (form && returnRows.length > 0) {
                if (form.reportValidity()) {
                    form.submit();
                }
            }
        }
    }

    function renderReturnTable(highlightIndex = -1, isExisting = false) {
        if (returnRows.length === 0) {
            itemsBody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="8" class="text-center py-4 text-secondary">
                        <i class="bi bi-arrow-counterclockwise fs-3 text-secondary d-block mb-1"></i>
                        <span class="fw-medium">No returned products added yet.</span> Select from the left dropdown or scan SKU on the right.
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

        returnRows.forEach((row, idx) => {
            const lineTotal = row.quantity * row.price;
            total += lineTotal;
            totalItems += row.quantity;
            const isTarget = (idx === highlightIndex);

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
                        <span class="badge bg-light text-secondary border fs-8 font-monospace">
                            ${row.stock} pcs
                        </span>
                    </td>
                    <td class="py-2 text-end font-monospace small">
                        <input type="number" step="0.01" name="items[${idx}][unit_price]" value="${row.price.toFixed(2)}"
                            onchange="updateRowRate(${idx}, this.value)"
                            class="form-control form-control-sm text-end font-monospace d-inline-block py-1" style="max-width: 90px;">
                    </td>
                    <td class="py-2 text-center">
                        <input type="number" name="items[${idx}][quantity]" id="qty-input-${idx}"
                            value="${row.quantity}" min="1" required
                            onchange="updateRowQty(${idx}, this.value)"
                            onkeydown="handleQtyKeydown(event, ${idx})"
                            class="form-control form-control-sm text-center fw-bold font-monospace mx-auto py-1 ${isTarget ? 'qty-animated text-danger' : ''}"
                            style="max-width: 80px;">
                    </td>
                    <td class="py-2 text-end font-monospace fw-bold text-danger fs-7">
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
        itemsBadge.textContent = `${returnRows.length} Line(s) (${totalItems} pcs)`;
        totalAmountDisplay.textContent = 'Rs. ' + total.toFixed(2);
        btnSubmit.disabled = false;

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
        const city = document.getElementById('modal_cust_city').value.trim();
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
                city: city,
                address: addr,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.customer) {
                const cust = data.customer;
                if (customerSelect && customerSelect.tomselect) {
                    customerSelect.tomselect.addOption({
                        value: cust.id,
                        text: `${cust.name} (${cust.phone || cust.city || 'Active'})`
                    });
                    customerSelect.tomselect.setValue(cust.id);
                } else if (customerSelect) {
                    const opt = document.createElement('option');
                    opt.value = cust.id;
                    opt.textContent = `${cust.name} (${cust.phone || cust.city || 'Active'})`;
                    opt.selected = true;
                    customerSelect.appendChild(opt);
                }
                if (customerModalInstance) customerModalInstance.hide();
                showFeedback(`Customer '${cust.name}' registered & selected.`);
            } else {
                alert('Customer registered successfully.');
                if (customerModalInstance) customerModalInstance.hide();
            }
        })
        .catch(err => {
            alert('Customer registered.');
            if (customerModalInstance) customerModalInstance.hide();
        });
    }
</script>
@endpush
