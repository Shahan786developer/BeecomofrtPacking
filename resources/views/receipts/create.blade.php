@extends('layouts.app')

@section('title', 'New Cash Receipt Voucher (CRV)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('receipts.index') }}" class="text-decoration-none">Cash Receipts</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Voucher</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10 d-flex flex-column gap-4">

        <!-- Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">New Cash Receipt Voucher (CRV)</h1>
                <p class="text-secondary small mb-0">Record payments received from customers and automatically update their credit ledger.</p>
            </div>
            <div>
                <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Receipts
                </a>
            </div>
        </div>

        <!-- FORM CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('receipts.store') }}" id="receipt-form" class="d-flex flex-column gap-4">
                    @csrf
                    @if(request('redirect_to'))
                        <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
                    @endif

                    <!-- Section: Voucher Meta -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-receipt text-success me-1"></i> Voucher Identification
                        </h6>

                        <div class="row g-3">
                            <!-- Voucher No -->
                            <div class="col-12 col-md-4">
                                <label for="voucher_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Voucher Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="voucher_no" id="voucher_no" value="{{ old('voucher_no', $nextVoucherNo) }}" required
                                    class="form-control font-monospace fw-bold fs-6 bg-light">
                            </div>

                            <!-- Customer Selection -->
                            <div class="col-12 col-md-5">
                                <label for="customer_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Customer Account <span class="text-danger">*</span>
                                </label>
                                <select name="customer_id" id="customer_id" required class="searchable-select form-select">
                                    <option value="">Select customer paying cash...</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" 
                                            {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}
                                            data-name="{{ $c->name }}"
                                            data-owner="{{ $c->owner_name }}"
                                            data-invoiced="{{ $c->total_invoiced }}"
                                            data-paid="{{ $c->total_paid }}"
                                            data-returned="{{ $c->total_returned }}"
                                            data-due="{{ $c->balance_due }}">
                                            {{ $c->name }} {{ $c->owner_name ? '('.$c->owner_name.')' : '' }} — [Pending Due: Rs. {{ number_format($c->balance_due, 2) }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Date -->
                            <div class="col-12 col-md-3">
                                <label for="payment_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Payment Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', $today) }}" required class="form-control fw-semibold">
                            </div>
                        </div>
                    </div>

                    <!-- Live Customer Balance Preview Card -->
                    <div id="customer-balance-box" class="card border-0 bg-light p-3 rounded-3 d-none">
                        <div class="row g-3 text-center align-items-center">
                            <div class="col-12 col-sm-4 border-end">
                                <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Total Invoiced</span>
                                <span id="disp-total-invoiced" class="h5 fw-bold font-monospace text-dark mb-0">Rs. 0.00</span>
                            </div>
                            <div class="col-12 col-sm-4 border-end">
                                <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Previously Received</span>
                                <span id="disp-total-paid" class="h5 fw-bold font-monospace text-success mb-0">Rs. 0.00</span>
                            </div>
                            <div class="col-12 col-sm-4">
                                <span class="text-danger small fw-bold text-uppercase d-block mb-1">Current Outstanding Due</span>
                                <span id="disp-balance-due" class="h4 fw-black font-monospace text-danger mb-0">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Payment Amount & Calculations -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-cash-coin text-success me-1"></i> Payment Details
                        </h6>

                        <div class="row g-3">
                            <!-- Amount Received -->
                            <div class="col-12 col-md-6">
                                <label for="amount" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Amount Received (Rs.) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-success text-white fw-bold">Rs.</span>
                                    <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required min="0.01"
                                        placeholder="e.g. 1000.00" class="form-control font-monospace fw-bold text-success fs-4" autofocus>
                                </div>
                                <div class="form-text small text-secondary">
                                    Enter cash or online amount received from customer.
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-12 col-md-6">
                                <label for="payment_method" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Payment Method <span class="text-danger">*</span>
                                </label>
                                <select name="payment_method" id="payment_method" required class="form-select form-select-lg">
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Online</option>
                                    <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Bank Cheque</option>
                                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Live Remaining Balance After Payment Calculation Box -->
                        <div class="card border-success-subtle bg-success-subtle mt-3 p-3">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                <div>
                                    <span class="small fw-bold text-success-emphasis text-uppercase d-block">
                                        Estimated Customer Remaining Balance:
                                    </span>
                                    <span class="small text-secondary">Outstanding balance after this receipt voucher is recorded.</span>
                                </div>
                                <div class="text-sm-end">
                                    <span id="disp-remaining-balance" class="h3 fw-black text-success font-monospace mb-0">
                                        Rs. 0.00
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description / Remarks -->
                    <div>
                        <label for="notes" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Description / Remarks
                        </label>
                        <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Optional payment description, bank slip #, cheque details, or notes...">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Cash Receipt Voucher
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer_id');
        const amountInput = document.getElementById('amount');
        const balanceBox = document.getElementById('customer-balance-box');

        const dispInvoiced = document.getElementById('disp-total-invoiced');
        const dispPaid = document.getElementById('disp-total-paid');
        const dispDue = document.getElementById('disp-balance-due');
        const dispRemaining = document.getElementById('disp-remaining-balance');

        let currentCustomerDue = 0;

        function updateCustomerInfo() {
            let custId = customerSelect.tomselect ? customerSelect.tomselect.getValue() : customerSelect.value;
            if (!custId) {
                balanceBox.classList.add('d-none');
                currentCustomerDue = 0;
                calculateRemaining();
                return;
            }

            const opt = customerSelect.querySelector(`option[value="${custId}"]`);
            if (opt) {
                const invoiced = parseFloat(opt.getAttribute('data-invoiced')) || 0;
                const paid = parseFloat(opt.getAttribute('data-paid')) || 0;
                const returned = parseFloat(opt.getAttribute('data-returned')) || 0;
                const due = parseFloat(opt.getAttribute('data-due')) || 0;

                currentCustomerDue = due;

                dispInvoiced.textContent = 'Rs. ' + invoiced.toFixed(2);
                dispPaid.textContent = 'Rs. ' + (paid + returned).toFixed(2);
                dispDue.textContent = 'Rs. ' + due.toFixed(2);

                balanceBox.classList.remove('d-none');
            }

            calculateRemaining();
        }

        function calculateRemaining() {
            const paying = parseFloat(amountInput.value) || 0;
            const remaining = Math.max(0, currentCustomerDue - paying);
            dispRemaining.textContent = 'Rs. ' + remaining.toFixed(2);
        }

        setTimeout(() => {
            if (customerSelect.tomselect) {
                customerSelect.tomselect.on('change', updateCustomerInfo);
            } else {
                customerSelect.addEventListener('change', updateCustomerInfo);
            }

            if (customerSelect.value || (customerSelect.tomselect && customerSelect.tomselect.getValue())) {
                updateCustomerInfo();
            }
        }, 300);

        amountInput.addEventListener('input', calculateRemaining);
    });
</script>
@endpush
