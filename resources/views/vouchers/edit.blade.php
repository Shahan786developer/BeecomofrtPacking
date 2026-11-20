@extends('layouts.app')

@section('title', 'Edit Journal Voucher #' . $voucher->voucher_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}" class="text-decoration-none text-secondary">Journal Vouchers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vouchers.show', $voucher) }}" class="text-decoration-none text-secondary">{{ $voucher->voucher_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-5"></i>
                    <h5 class="mb-0 fw-bold text-dark">Edit Journal Voucher: {{ $voucher->voucher_no }}</h5>
                </div>
                <a href="{{ route('vouchers.show', $voucher) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <form method="POST" action="{{ route('vouchers.update', $voucher) }}">
                @csrf
                @method('PUT')

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
                            <input type="text" name="voucher_no" value="{{ old('voucher_no', $voucher->voucher_no) }}" required class="form-control font-monospace fw-bold">
                        </div>

                        <!-- Date -->
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Voucher Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="voucher_date" value="{{ old('voucher_date', $voucher->voucher_date->format('Y-m-d')) }}" required class="form-control">
                        </div>
                    </div>

                    <!-- Customer / Account -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Customer / Account Name
                        </label>
                        <select name="customer_id" class="form-select">
                            <option value="">-- General / Owner Capital Account --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $voucher->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — Owner: {{ $c->owner_name ?: '—' }} • City: {{ $c->city ?: '—' }} (Due: Rs. {{ number_format($c->balance_due, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <!-- Voucher Type (Debit / Credit) -->
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Voucher Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" required class="form-select fw-bold">
                                <option value="debit" {{ old('type', $voucher->type) === 'debit' ? 'selected' : '' }}>
                                    DEBIT (+) — Increase Customer Balance / Receivables
                                </option>
                                <option value="credit" {{ old('type', $voucher->type) === 'credit' ? 'selected' : '' }}>
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
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $voucher->amount) }}" required class="form-control font-monospace fs-5 fw-bold text-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Reference No -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Reference / Cheque / Bill # (Optional)
                        </label>
                        <input type="text" name="reference_no" value="{{ old('reference_no', $voucher->reference_no) }}" class="form-control">
                    </div>

                    <!-- Notes / Reason -->
                    <div>
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                            Description / Accounting Remarks
                        </label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $voucher->notes) }}</textarea>
                    </div>
                </div>

                <div class="card-footer bg-light border-top py-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('vouchers.show', $voucher) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
