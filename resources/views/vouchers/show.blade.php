@extends('layouts.app')

@section('title', 'Journal Voucher #' . $voucher->voucher_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}" class="text-decoration-none text-secondary">Journal Vouchers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $voucher->voucher_no }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-check text-primary fs-5"></i>
                    <h5 class="mb-0 fw-bold text-dark">Journal Voucher: {{ $voucher->voucher_no }}</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('vouchers.print', $voucher) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-printer me-1"></i> Print Voucher
                    </a>
                    <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body p-4 d-flex flex-column gap-3">
                <!-- Voucher Key Info Grid -->
                <div class="row g-3">
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Voucher #</span>
                            <span class="fs-5 fw-bold font-monospace text-primary">{{ $voucher->voucher_no }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Voucher Date</span>
                            <span class="fs-6 fw-bold text-dark">{{ $voucher->voucher_date->format('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Type</span>
                            @if($voucher->type === 'debit')
                                <span class="badge bg-warning-subtle text-dark border border-warning fs-6 px-2.5 py-1">DEBIT (+)</span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis border border-info fs-6 px-2.5 py-1">CREDIT (-)</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Amount</span>
                            <span class="fs-5 fw-black text-danger font-monospace">Rs. {{ number_format($voucher->amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Account & Creator Details -->
                <div class="card border border-light-subtle">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Accounting Details</h6>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <span class="text-secondary small d-block">Account / Customer:</span>
                                @if($voucher->customer)
                                    <a href="{{ route('customers.ledger', $voucher->customer) }}" class="fw-bold text-dark fs-6 text-decoration-none hover-primary">
                                        {{ $voucher->customer->name }} <i class="bi bi-box-arrow-up-right small ms-1 text-primary"></i>
                                    </a>
                                    @if($voucher->customer->owner_name)
                                        <div class="small text-muted">Owner: {{ $voucher->customer->owner_name }}</div>
                                    @endif
                                    @if($voucher->customer->phone)
                                        <div class="small text-muted font-monospace">Phone: {{ $voucher->customer->phone }}</div>
                                    @endif
                                @else
                                    <span class="fw-bold text-dark">General / Owner Capital</span>
                                @endif
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-secondary small d-block">Recorded By:</span>
                                <span class="fw-bold text-dark">{{ $voucher->user ? $voucher->user->name : 'System' }}</span>
                                <div class="small text-muted">Entry Time: {{ $voucher->created_at ? $voucher->created_at->format('d M Y, h:i A') : '—' }}</div>
                            </div>
                        </div>

                        @if($voucher->reference_no)
                        <div class="mt-3 pt-3 border-top">
                            <span class="text-secondary small d-block">Reference Document / Cheque #:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $voucher->reference_no }}</span>
                        </div>
                        @endif

                        @if($voucher->notes)
                        <div class="mt-3 pt-3 border-top">
                            <span class="text-secondary small d-block">Remarks / Reason:</span>
                            <p class="mb-0 text-dark">{{ $voucher->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light border-top py-3 d-flex justify-content-between align-items-center">
                @if($voucher->customer)
                    <a href="{{ route('customers.ledger', $voucher->customer) }}" class="btn btn-outline-info btn-sm fw-bold">
                        <i class="bi bi-journal-text me-1"></i> View In Customer Ledger
                    </a>
                @else
                    <span></span>
                @endif
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('vouchers.destroy', $voucher) }}" onsubmit="return confirm('Delete this Journal Voucher? This will revert the balance effect.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i> Delete Voucher
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
