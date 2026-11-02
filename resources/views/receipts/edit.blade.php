@extends('layouts.app')

@section('title', 'Edit Voucher ' . $receipt->voucher_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('receipts.index') }}" class="text-decoration-none">Cash Receipts</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $receipt->voucher_no }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10 d-flex flex-column gap-4">

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Edit Cash Receipt Voucher</h1>
                <p class="text-secondary small mb-0">Modify payment amount or notes for voucher {{ $receipt->voucher_no }}.</p>
            </div>
            <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Receipts
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('receipts.update', $receipt) }}" class="d-flex flex-column gap-4">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Voucher Number</label>
                            <input type="text" value="{{ $receipt->voucher_no }}" readonly class="form-control font-monospace fw-bold bg-light">
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="customer_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">Customer Account <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" required class="searchable-select form-select">
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id', $receipt->customer_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} {{ $c->owner_name ? '('.$c->owner_name.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="payment_date" class="form-label small fw-bold text-secondary text-uppercase mb-1">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', $receipt->payment_date->format('Y-m-d')) }}" required class="form-control fw-semibold">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="amount" class="form-label small fw-bold text-secondary text-uppercase mb-1">Amount Received (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-success text-white fw-bold">Rs.</span>
                                <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $receipt->amount) }}" required min="0.01" class="form-control font-monospace fw-bold text-success fs-4">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="payment_method" class="form-label small fw-bold text-secondary text-uppercase mb-1">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" required class="form-select form-select-lg">
                                <option value="cash" {{ old('payment_method', $receipt->payment_method) === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                                <option value="bank_transfer" {{ old('payment_method', $receipt->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Online</option>
                                <option value="cheque" {{ old('payment_method', $receipt->payment_method) === 'cheque' ? 'selected' : '' }}>Bank Cheque</option>
                                <option value="other" {{ old('payment_method', $receipt->payment_method) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="form-label small fw-bold text-secondary text-uppercase mb-1">Description / Remarks</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $receipt->notes) }}</textarea>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Update Cash Receipt</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
