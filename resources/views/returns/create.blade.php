@extends('layouts.app')

@section('title', 'Process Bill Return')

@section('breadcrumb')
    <a href="{{ route('returns.index') }}" class="hover:text-amber-600">Bill Returns</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">New Return</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Process Bill Return</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Select original sales invoice, scan returned merchandise, and restore warehouse inventory.</p>
        </div>
        <a href="{{ route('returns.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Returns
        </a>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('returns.store') }}" id="return-form" class="space-y-6">
        @csrf

        <!-- INVOICE SELECTION CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <!-- 1. Original Invoice -->
                <div>
                    <label for="sale_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Original Invoice <span class="text-rose-500">*</span>
                    </label>
                    <select name="sale_id" id="sale_id" required class="searchable-select w-full" placeholder="Search invoice or customer...">
                        <option value="">Select Invoice...</option>
                        @foreach($sales as $sale)
                            <option value="{{ $sale->id }}" {{ (isset($selectedSaleId) && $selectedSaleId == $sale->id) ? 'selected' : ($loop->first ? 'selected' : '') }}>
                                {{ $sale->invoice_no }} — {{ $sale->customer->name }} (${{ number_format($sale->total_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Return Number -->
                <div>
                    <label for="return_no" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Return Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="return_no" id="return_no" value="{{ old('return_no', $nextReturnNo) }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 font-mono font-bold text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-amber-400">
                </div>

                <!-- 3. Return Date -->
                <div>
                    <label for="return_date" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Return Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="return_date" id="return_date" value="{{ old('return_date', $today) }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-amber-400">
                </div>

            </div>
        </div>

        <!-- SCAN RETURNED PRODUCT BARCODE -->
        <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-sm border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-orange-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-barcode"></i> Scan Returned Product
                </span>
                <span class="text-[11px] text-slate-400">Scan 7-digit SKU to match item</span>
            </div>

            <div class="max-w-md relative">
                <input type="text" id="return_scanner_input" placeholder="Scan or enter returned 7-digit SKU..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 font-mono font-bold text-sm tracking-wider focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400">
                <i class="fa-solid fa-barcode absolute left-3.5 top-3 text-orange-400"></i>
            </div>

            <div id="return-scanner-feedback" class="text-xs text-orange-400 font-medium hidden">
                <i class="fa-solid fa-check-circle mr-1"></i> <span id="return-feedback-text"></span>
            </div>
        </div>

        <!-- ITEMS RETURN TABLE (Requirement #5) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 px-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">Merchandise on Selected Invoice</h3>
                <span id="invoice-items-count-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    Loading items...
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="return-items-table">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-[11px]">
                        <tr>
                            <th class="p-4 pl-6 text-center w-12">Return?</th>
                            <th class="p-4">Product Title</th>
                            <th class="p-4">SKU</th>
                            <th class="p-4 text-center">Sold</th>
                            <th class="p-4 text-center">Already Returned</th>
                            <th class="p-4 text-center">Eligible for Return</th>
                            <th class="p-4 text-right">Selling Rate ($)</th>
                            <th class="p-4 text-center w-28">Return Qty</th>
                            <th class="p-4 pr-6 text-right">Refund Subtotal ($)</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                Select an invoice to load merchandise items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- REASON & TOTALS -->
            <div class="p-6 bg-slate-50 border-t border-slate-200">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <div class="lg:col-span-7 space-y-2">
                        <label for="reason" class="block text-xs font-bold text-slate-700">
                            Reason for Return
                        </label>
                        <textarea name="reason" id="reason" rows="3" placeholder="Optional explanation..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 bg-white"></textarea>
                    </div>

                    <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>Units Returned:</span>
                            <span id="total-qty-return-display" class="font-bold text-slate-900">0 units</span>
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900">Total Refund:</span>
                            <span id="total-return-amount-display" class="text-2xl font-black text-orange-600 font-mono">$0.00</span>
                        </div>

                        <button type="submit" id="btn-submit-return" disabled
                            class="w-full py-3 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs shadow-xs transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-rotate-left"></i> Confirm Return & Restore Stock
                        </button>
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
            let saleId = saleSelect.tomselect ? saleSelect.tomselect.getValue() : saleSelect.value;
            if (!saleId) {
                returnItemsBody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-slate-400">Please select an invoice first.</td></tr>`;
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
            if (saleSelect.tomselect) saleSelect.tomselect.on('change', loadItems);
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
                return;
            }
            item.selected = true;
            renderReturnTable();
            returnScannerInput.value = '';

            feedbackText.textContent = `Matched '${item.product_title}'. Added to return.`;
            feedbackBox.classList.remove('hidden');
            setTimeout(() => { feedbackBox.classList.add('hidden'); }, 3000);
        } else {
            alert(`SKU '${sku}' was not found on this invoice.`);
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
            returnItemsBody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-slate-400">No items on this invoice.</td></tr>`;
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
            <tr class="${isChecked ? 'bg-orange-50/70' : 'hover:bg-slate-50'} transition-colors">
                <td class="p-4 pl-6 text-center">
                    <input type="checkbox" ${isChecked ? 'checked' : ''} ${!isEligible ? 'disabled' : ''}
                        onchange="toggleSelect(${idx})"
                        class="rounded text-orange-500 focus:ring-orange-400 w-4 h-4 cursor-pointer">
                    ${isChecked ? `<input type="hidden" name="items[${idx}][sale_detail_id]" value="${item.sale_detail_id}">` : ''}
                </td>
                <td class="p-4 font-bold text-slate-900 text-sm">
                    ${item.product_title}
                </td>
                <td class="p-4 font-mono font-bold text-slate-700">
                    <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200">${item.sku_code}</span>
                </td>
                <td class="p-4 text-center font-bold text-slate-700">${item.quantity_sold}</td>
                <td class="p-4 text-center text-slate-500">${item.returned_quantity}</td>
                <td class="p-4 text-center">
                    ${isEligible 
                        ? `<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">${item.eligible_quantity} units</span>`
                        : `<span class="text-slate-400 text-xs">Fully Returned</span>`
                    }
                </td>
                <td class="p-4 text-right font-bold text-slate-800">$${item.unit_price.toFixed(2)}</td>
                <td class="p-4 text-center">
                    ${isChecked 
                        ? `<input type="number" name="items[${idx}][quantity]" value="${item.return_qty}" min="1" max="${item.eligible_quantity}" required
                            onchange="updateReturnQty(${idx}, this.value)"
                            class="w-20 px-2 py-1 text-center font-bold rounded-lg border border-orange-400 focus:ring-2 focus:ring-orange-400">`
                        : `<span class="text-slate-400">—</span>`
                    }
                </td>
                <td class="p-4 pr-6 text-right font-black text-sm ${isChecked ? 'text-orange-600' : 'text-slate-400'}">
                    $${lineRefund}
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
        totalReturnDisplay.textContent = '$' + totalRefund.toFixed(2);
    }
</script>
@endpush
