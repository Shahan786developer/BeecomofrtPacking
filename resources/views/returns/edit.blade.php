@extends('layouts.app')

@section('title', 'Edit Return Record #' . $return->return_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Bill Returns</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit #{{ $return->return_no }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Edit Return Record #{{ $return->return_no }}</h1>
            <p class="text-secondary small mb-0">Modify return quantities or return date. Inventory stock is automatically re-adjusted.</p>
        </div>
        <div>
            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Returns
            </a>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form method="POST" action="{{ route('returns.update', $return) }}" id="return-form" class="d-flex flex-column gap-4">
        @csrf
        @method('PUT')

        <!-- METADATA CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Return Number -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Return Number
                        </label>
                        <input type="text" value="{{ $return->return_no }}" readonly class="form-control font-monospace fw-bold bg-light cursor-not-allowed">
                    </div>

                    <!-- Original Invoice -->
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Original Invoice &amp; Customer
                        </label>
                        <input type="text" value="Invoice #{{ $return->sale->invoice_no }} — {{ $return->customer->name }}" readonly class="form-control bg-light cursor-not-allowed fw-semibold">
                    </div>

                    <!-- Return Date -->
                    <div class="col-12 col-md-3">
                        <label for="return_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Return Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="return_date" id="return_date" value="{{ old('return_date', $return->return_date->format('Y-m-d')) }}" required class="form-control font-monospace">
                    </div>
                </div>
            </div>
        </div>

        <!-- RETURN ITEMS CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Returned Items</h5>
                <span class="badge bg-light text-dark border px-2 py-1">
                    Invoice #{{ $return->sale->invoice_no }} Items
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="return-items-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-center" style="width: 70px;">Return?</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Product Description</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Sold</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Rate (Rs.)</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7" style="width: 140px;">Return Qty</th>
                            <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7">Refund (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body" class="border-top-0">
                        @php
                            $returnedDetailsMap = $return->details->keyBy('sale_detail_id');
                        @endphp
                        @foreach($return->sale->details as $idx => $sDetail)
                            @php
                                $existingReturn = $returnedDetailsMap->get($sDetail->id);
                                $isChecked = !is_null($existingReturn);
                                $currentReturnQty = $existingReturn ? $existingReturn->quantity : 1;
                                // Max allowed is what's currently returned + eligible quantity
                                $maxReturnQty = $sDetail->eligible_return_quantity + ($existingReturn ? $existingReturn->quantity : 0);
                            @endphp
                            <tr class="{{ $isChecked ? 'table-warning' : '' }}">
                                <td class="ps-4 py-3 text-center align-middle">
                                    <input type="checkbox" id="check_{{ $idx }}" {{ $isChecked ? 'checked' : '' }}
                                        onchange="toggleEditRow({{ $idx }})"
                                        class="form-check-input row-checkbox" style="width: 24px; height: 24px; cursor: pointer;">
                                    <input type="hidden" name="items[{{ $idx }}][sale_detail_id]" id="input_detail_{{ $idx }}" value="{{ $sDetail->id }}" {{ $isChecked ? '' : 'disabled' }}>
                                </td>
                                <td class="py-3">
                                    <span class="fw-bold text-dark d-block fs-6">{{ $sDetail->product_title }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark font-monospace border px-2 py-1">{{ $sDetail->sku_code }}</span>
                                </td>
                                <td class="py-3 text-center font-bold text-dark">{{ $sDetail->quantity }}</td>
                                <td class="py-3 text-end font-monospace fw-bold text-secondary">
                                    Rs. {{ number_format($sDetail->unit_price, 2) }}
                                    <input type="hidden" id="unit_price_{{ $idx }}" value="{{ $sDetail->unit_price }}">
                                </td>
                                <td class="py-3 text-center">
                                    <input type="number" name="items[{{ $idx }}][quantity]" id="qty_{{ $idx }}"
                                        value="{{ $currentReturnQty }}" min="1" max="{{ $maxReturnQty }}" required
                                        {{ $isChecked ? '' : 'disabled' }}
                                        oninput="recalcEditTotals()"
                                        class="form-control form-control-sm text-center fw-bold font-monospace mx-auto" style="max-width: 90px;">
                                    <small class="text-secondary d-block mt-1">Max: {{ $maxReturnQty }}</small>
                                </td>
                                <td class="pe-4 py-3 text-end font-monospace fw-bold fs-6" id="line_total_{{ $idx }}">
                                    Rs. {{ number_format($isChecked ? ($currentReturnQty * $sDetail->unit_price) : 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
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
                        <textarea name="reason" id="reason" rows="3" placeholder="Optional explanation or customer return feedback..." class="form-control bg-white">{{ old('reason', $return->reason) }}</textarea>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-4 bg-white d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center text-secondary">
                                <span>Units Returned:</span>
                                <span id="total-qty-display" class="fw-bold font-monospace text-dark fs-6">{{ $return->details->sum('quantity') }} unit(s)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <span class="h5 fw-bold text-dark mb-0">Total Refund:</span>
                                <span id="total-refund-display" class="h3 fw-black text-danger font-monospace mb-0">Rs. {{ number_format($return->total_amount, 2) }}</span>
                            </div>

                            <button type="submit" id="btn-submit-return" class="btn btn-warning btn-lg fw-bold w-100 shadow-sm mt-2">
                                <i class="bi bi-check2-circle me-1"></i> Update Return Record
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
    function toggleEditRow(idx) {
        const check = document.getElementById('check_' + idx);
        const detailInput = document.getElementById('input_detail_' + idx);
        const qtyInput = document.getElementById('qty_' + idx);
        const row = check.closest('tr');

        if (check.checked) {
            detailInput.disabled = false;
            qtyInput.disabled = false;
            row.classList.add('table-warning');
        } else {
            detailInput.disabled = true;
            qtyInput.disabled = true;
            row.classList.remove('table-warning');
        }
        recalcEditTotals();
    }

    function recalcEditTotals() {
        let totalRefund = 0;
        let totalUnits = 0;
        let anyChecked = false;

        document.querySelectorAll('.row-checkbox').forEach((cb, idx) => {
            const qtyInput = document.getElementById('qty_' + idx);
            const unitPrice = parseFloat(document.getElementById('unit_price_' + idx).value) || 0;
            const lineTotalCell = document.getElementById('line_total_' + idx);

            if (cb.checked) {
                anyChecked = true;
                const qty = parseInt(qtyInput.value) || 0;
                const lineTotal = qty * unitPrice;
                totalUnits += qty;
                totalRefund += lineTotal;
                lineTotalCell.textContent = 'Rs. ' + lineTotal.toFixed(2);
            } else {
                lineTotalCell.textContent = 'Rs. 0.00';
            }
        });

        document.getElementById('total-qty-display').textContent = totalUnits + ' unit(s)';
        document.getElementById('total-refund-display').textContent = 'Rs. ' + totalRefund.toFixed(2);
        document.getElementById('btn-submit-return').disabled = !anyChecked;
    }
</script>
@endpush
