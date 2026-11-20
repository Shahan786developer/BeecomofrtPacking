@extends('layouts.app')

@section('title', 'New Journal Voucher (JV)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}" class="text-decoration-none text-secondary">Journal Vouchers</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Voucher</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-plus text-primary fs-5"></i>
                    <h5 class="mb-0 fw-bold text-dark">Create Journal Voucher (JV)</h5>
                </div>
                <a href="{{ $redirectTo === 'customer_ledger' && $preselectedCustomerId ? route('customers.ledger', $preselectedCustomerId) : route('vouchers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <form method="POST" action="{{ route('vouchers.store') }}">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

                <div class="card-body p-4 d-flex flex-column gap-3">
                    @if($errors->any())
                        <div class="alert alert-danger mb-0">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <!-- Voucher No -->
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Voucher Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="voucher_no" value="{{ old('voucher_no', $voucherNo) }}" required class="form-control font-monospace fw-bold">
                        </div>

                        <!-- Date -->
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Voucher Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="voucher_date" value="{{ old('voucher_date', now()->toDateString()) }}" required class="form-control">
                        </div>
                    </div>

                    <!-- Customer / Account -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Customer / Account Name
                        </label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">-- General / Owner Capital Account --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $preselectedCustomerId) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — Owner: {{ $c->owner_name ?: '—' }} • City: {{ $c->city ?: '—' }} (Due: Rs. {{ number_format($c->balance_due, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text fs-7 text-muted">Select customer to adjust their balance and ledger automatically.</div>
                    </div>

                    <div class="row g-3">
                        <!-- Voucher Type (Debit / Credit) -->
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Voucher Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="jv_type" required class="form-select fw-bold">
                                <option value="debit" {{ old('type', $preselectedType) === 'debit' ? 'selected' : '' }}>
                                    DEBIT (+) — Increase Customer Balance / Receivables
                                </option>
                                <option value="credit" {{ old('type', $preselectedType) === 'credit' ? 'selected' : '' }}>
                                    CREDIT (-) — Decrease Customer Balance / Owner Capital / Discount
                                </option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Amount (PKR) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-secondary">Rs.</span>
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required placeholder="0.00" class="form-control font-monospace fs-5 fw-bold text-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Reference No -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Reference / Cheque / Bill # (Optional)
                        </label>
                        <input type="text" name="reference_no" value="{{ old('reference_no') }}" placeholder="e.g. Bill Ref #104, Online Transaction ID, Bank Cheque #" class="form-control">
                    </div>

                    <!-- Notes / Reason -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Description / Accounting Remarks
                        </label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Explain the reason for this journal voucher entry (e.g. Owner capital investment, freight adjustment, customer discount, penalty adjustment)">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card-footer bg-light border-top py-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i> This voucher will be recorded directly into the selected customer's ledger.
                    </span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Journal Voucher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
