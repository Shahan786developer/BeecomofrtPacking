@extends('layouts.app')

@section('title', 'New Sales Invoice')

@section('breadcrumb')
    <a href="{{ route('sales.index') }}" class="hover:text-amber-600">Sales Invoices</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">New Invoice</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">New Sales Invoice</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Scan product barcodes, auto-populate rates, and issue customer bills.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Invoices
        </a>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('sales.store') }}" id="sales-form" class="space-y-6">
        @csrf

        <!-- INVOICE METADATA CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <!-- 1. Customer Selection -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="customer_id" class="block text-xs font-bold text-slate-700">
                            Customer Account <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" onclick="openNewCustomerModal()" class="text-[11px] font-bold text-amber-600 hover:underline">
                            + Add New
                        </button>
                    </div>
                    <select name="customer_id" id="customer_id" required class="searchable-select w-full" placeholder="Search customer...">
                        <option value="">Select Customer...</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $cust->name }} {{ $cust->phone ? "({$cust->phone})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Invoice Number -->
                <div>
                    <label for="invoice_no" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Invoice Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="invoice_no" id="invoice_no" value="{{ old('invoice_no', $nextInvoiceNo) }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 font-mono font-bold text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-amber-400">
                </div>

                <!-- 3. Invoice Date -->
                <div>
                    <label for="invoice_date" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Invoice Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $today) }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-amber-400">
                </div>

            </div>
        </div>

        <!-- PRODUCT BARCODE SCANNER (Requirement #4) -->
        <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-sm border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-barcode"></i> Product Barcode Scanner
                </span>
                <span class="text-[11px] text-slate-400">Scan 7-digit SKU or pick below</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <!-- Barcode Input -->
                <div class="md:col-span-5 relative">
                    <input type="text" id="barcode_scanner_input" autofocus placeholder="Scan 7-digit SKU (e.g. 1000001)..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 font-mono font-bold text-sm tracking-wider focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400">
                    <i class="fa-solid fa-barcode absolute left-3.5 top-3 text-amber-400"></i>
                </div>

                <!-- Dropdown selector -->
                <div class="md:col-span-5 text-slate-900">
                    <select id="quick_product_select" class="searchable-select w-full" placeholder="Or search product title...">
                        <option value="">Or choose from catalog...</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod['id'] }}" 
                                data-id="{{ $prod['id'] }}"
                                data-sku="{{ $prod['sku_code'] }}"
                                data-title="{{ $prod['title'] }}"
                                data-price="{{ $prod['selling_price'] }}"
                                data-stock="{{ $prod['stock_quantity'] }}">
                                {{ $prod['title'] }} — [SKU: {{ $prod['sku_code'] }}] (${{ number_format($prod['selling_price'], 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <button type="button" id="btn-add-item" class="w-full py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs transition-colors flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> Add Item
                    </button>
                </div>
            </div>

            <div id="scanner-feedback" class="text-xs text-amber-400 font-medium hidden">
                <i class="fa-solid fa-check-circle mr-1"></i> <span id="scanner-feedback-text"></span>
            </div>
        </div>

        <!-- ITEMS TABLE (Requirement #4) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 px-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">Invoice Items</h3>
                <span id="items-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    0 Items Added
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="invoice-items-table">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px]">
                        <tr>
                            <th class="p-4 pl-6 w-12 text-center">#</th>
                            <th class="p-4">Product Title</th>
                            <th class="p-4">SKU</th>
                            <th class="p-4 text-center">Stock</th>
                            <th class="p-4 text-right">Rate ($)</th>
                            <th class="p-4 text-center w-28">Quantity</th>
                            <th class="p-4 text-right">Amount ($)</th>
                            <th class="p-4 pr-6 text-center w-12">Action</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body" class="divide-y divide-slate-100">
                        <tr id="empty-row">
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                Invoice is currently empty. Scan a barcode above or select a product.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TOTALS & ACTION -->
            <div class="p-6 bg-slate-50 border-t border-slate-200">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Notes -->
                    <div class="lg:col-span-7 space-y-2">
                        <label for="notes" class="block text-xs font-bold text-slate-700">
                            Notes / Remarks
                        </label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Optional notes for this invoice..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white"></textarea>
                    </div>

                    <!-- Totals -->
                    <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>Subtotal:</span>
                            <span id="subtotal-display" class="font-bold text-slate-900">$0.00</span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-600 gap-4">
                            <span>Discount ($):</span>
                            <input type="number" step="0.01" min="0" name="discount" id="discount_input" value="0.00"
                                class="w-28 px-2.5 py-1 text-right rounded-lg border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-amber-400">
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900">Total Amount:</span>
                            <span id="total-amount-display" class="text-2xl font-black text-slate-950 font-mono">$0.00</span>
                        </div>

                        <button type="submit" id="btn-submit-invoice" disabled
                            class="w-full py-3 rounded-xl bg-amber-400 hover:bg-amber-300 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-xs shadow-xs transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i> Complete & Save Invoice
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </form>

</div>

<!-- QUICK ADD CUSTOMER MODAL -->
<div id="new-customer-modal" class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Add Customer</h3>
            <button type="button" onclick="closeNewCustomerModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                <input type="text" id="modal_cust_name" required placeholder="Full Name or Business Name"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                <input type="text" id="modal_cust_phone" placeholder="+92 300 0000000"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                <input type="email" id="modal_cust_email" placeholder="customer@domain.com"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Address</label>
                <input type="text" id="modal_cust_address" placeholder="City or location"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeNewCustomerModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-600">Cancel</button>
                <button type="button" onclick="saveNewCustomerAjax()" class="px-5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 font-bold text-xs text-slate-950">Save</button>
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
    const discountInput = document.getElementById('discount_input');
    const totalAmountDisplay = document.getElementById('total-amount-display');
    const btnSubmit = document.getElementById('btn-submit-invoice');
    const feedbackBox = document.getElementById('scanner-feedback');
    const feedbackText = document.getElementById('scanner-feedback-text');

    document.addEventListener('DOMContentLoaded', function () {
        scannerInput.focus();

        scannerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScannedSku(this.value.trim());
            }
        });

        scannerInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (/^\d{7}$/.test(val)) {
                processScannedSku(val);
            }
        });

        btnAddItem.addEventListener('click', function() {
            let prodId = quickSelect.tomselect ? quickSelect.tomselect.getValue() : quickSelect.value;
            if (prodId) {
                const prod = allProductsCatalog.find(p => p.id == prodId);
                if (prod) {
                    addProductToInvoice(prod);
                    if (quickSelect.tomselect) quickSelect.tomselect.clear();
                }
            } else {
                scannerInput.focus();
            }
        });

        discountInput.addEventListener('input', calculateTotals);
    });

    function processScannedSku(sku) {
        if (!sku) return;

        const prod = allProductsCatalog.find(p => p.sku_code == sku);
        if (prod) {
            addProductToInvoice(prod);
            scannerInput.value = '';
            showFeedback(`Product '${prod.title}' added.`);
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
                        showFeedback(`Product '${data.title}' added.`);
                    } else {
                        alert(`No product found with SKU '${sku}'.`);
                        scannerInput.select();
                    }
                })
                .catch(err => alert(`Error: ${err}`));
        }
    }

    function showFeedback(msg) {
        feedbackText.textContent = msg;
        feedbackBox.classList.remove('hidden');
        setTimeout(() => { feedbackBox.classList.add('hidden'); }, 3000);
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
                    <td colspan="8" class="p-8 text-center text-slate-400">
                        Invoice is currently empty. Scan a barcode above or select a product.
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
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 pl-6 text-center text-slate-400 font-bold">${idx + 1}</td>
                <!-- Unified Title (Requirement #4: read-only) -->
                <td class="p-4 font-bold text-slate-900 text-sm">
                    ${item.title}
                    <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                </td>
                <td class="p-4 font-mono font-bold text-slate-700">
                    <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">${item.sku}</span>
                </td>
                <td class="p-4 text-center">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold ${item.stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}">
                        ${item.stock} in stock
                    </span>
                </td>
                <!-- Rate is read-only -->
                <td class="p-4 text-right font-bold text-slate-800">
                    $${item.price.toFixed(2)}
                </td>
                <td class="p-4 text-center">
                    <input type="number" name="items[${idx}][quantity]" value="${item.quantity}" min="1" required
                        onchange="updateQty(${idx}, this.value)"
                        class="w-20 px-2 py-1 text-center font-bold rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-400">
                </td>
                <td class="p-4 text-right font-black text-slate-900 text-sm">
                    $${lineTotal}
                </td>
                <td class="p-4 pr-6 text-center">
                    <button type="button" onclick="removeRow(${idx})" class="text-rose-500 hover:text-rose-700 p-1" title="Remove">
                        <i class="fa-solid fa-trash-can"></i>
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

        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(0, subtotal - discount);

        subtotalDisplay.textContent = '$' + subtotal.toFixed(2);
        totalAmountDisplay.textContent = '$' + total.toFixed(2);
    }

    function openNewCustomerModal() {
        document.getElementById('new-customer-modal').classList.remove('hidden');
    }
    function closeNewCustomerModal() {
        document.getElementById('new-customer-modal').classList.add('hidden');
    }
    function saveNewCustomerAjax() {
        const name = document.getElementById('modal_cust_name').value.trim();
        const phone = document.getElementById('modal_cust_phone').value.trim();
        const email = document.getElementById('modal_cust_email').value.trim();
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
            body: JSON.stringify({ name, phone, email, address })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const customerSelect = document.getElementById('customer_id');
                if (customerSelect.tomselect) {
                    customerSelect.tomselect.addOption({
                        value: data.customer.id,
                        text: `${data.customer.name} (${data.customer.phone || 'No Phone'})`
                    });
                    customerSelect.tomselect.setValue(data.customer.id);
                }
                closeNewCustomerModal();
            }
        });
    }
</script>
@endpush
