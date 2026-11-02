@extends('layouts.app')

@section('title', 'Voucher ' . $receipt->voucher_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('receipts.index') }}" class="text-decoration-none">Cash Receipts</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $receipt->voucher_no }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 d-flex flex-column gap-3">

        <!-- Top Actions Bar -->
        <div class="d-flex justify-content-between align-items-center d-print-none">
            <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Receipts
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('customers.ledger', $receipt->customer_id) }}" class="btn btn-outline-info">
                    <i class="bi bi-journal-text me-1"></i> Customer Ledger
                </a>
                <button type="button" onclick="window.print()" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-printer me-1"></i> Print Voucher
                </button>
            </div>
        </div>

        <!-- Printable Voucher Card -->
        <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h2 class="h3 fw-black text-dark text-uppercase mb-0">{{ $company->name ?? 'Bee Comfort' }}</h2>
                    <div class="text-primary small fw-semibold">{{ $company->tagline ?? 'Packing & Retail Garment Solutions' }}</div>
                    <div class="text-secondary small mt-1">
                        {{ $company->address ?? 'Warehouse • Pakistan' }}<br>
                        Phone / WhatsApp: {{ $company->phone ?? '+92 300 0000000' }}
                    </div>
                </div>

                <div class="text-end">
                    <span class="badge bg-success text-white px-3 py-1.5 text-uppercase fw-bold fs-7">
                        Cash Receipt Voucher
                    </span>
                    <h4 class="font-monospace fw-bold text-dark mt-2 mb-1">{{ $receipt->voucher_no }}</h4>
                    <div class="text-secondary small font-monospace">
                        Date: {{ $receipt->payment_date->format('d-M-Y') }}
                    </div>
                </div>
            </div>

            <!-- Receipt Information Box -->
            <div class="p-4 bg-light rounded-3 border mb-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Received From:</span>
                        <h4 class="fw-bold text-dark mb-1">{{ $receipt->customer->name }}</h4>
                        @if($receipt->customer->owner_name)
                            <div class="text-secondary small">Owner: <strong class="text-dark">{{ $receipt->customer->owner_name }}</strong></div>
                        @endif
                        @if($receipt->customer->phone)
                            <div class="text-secondary small font-monospace">Phone: {{ $receipt->customer->phone }}</div>
                        @endif
                        @if($receipt->customer->city)
                            <div class="text-secondary small">City: {{ $receipt->customer->city }}</div>
                        @endif
                    </div>

                    <div class="col-12 col-sm-6 text-sm-end">
                        <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Payment Method:</span>
                        <span class="badge bg-white text-dark border px-3 py-1.5 text-uppercase fw-bold font-monospace">
                            {{ str_replace('_', ' ', $receipt->payment_method) }}
                        </span>
                        <div class="mt-3">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Amount Paid:</span>
                            <div class="h2 fw-black text-success font-monospace mb-0">
                                Rs. {{ number_format($receipt->amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Balance Status -->
            <div class="row g-3 mb-5">
                <div class="col-12 col-sm-7">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <span class="small fw-bold text-secondary text-uppercase d-block mb-1">Description / Notes:</span>
                        <div class="text-dark small">
                            {{ $receipt->notes ?: 'Cash received against outstanding customer billing account.' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-5">
                    <div class="p-3 border rounded-3 bg-light text-end">
                        <span class="small fw-bold text-secondary text-uppercase d-block mb-1">Customer Remaining Due:</span>
                        <div class="h4 fw-black text-danger font-monospace mb-0">
                            Rs. {{ number_format($receipt->customer->balance_due, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="row text-center pt-4 border-top mt-auto">
                <div class="col-6">
                    <div class="border-top border-dark mx-auto w-75 pt-1">
                        <span class="small fw-bold text-uppercase text-secondary">Cashier / Received By</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top border-dark mx-auto w-75 pt-1">
                        <span class="small fw-bold text-uppercase text-secondary">Customer Signature</span>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted small mt-4 pt-2 border-top">
                {{ $company->name ?? 'Bee Comfort' }} &bull; Official Computer-Generated Cash Receipt Voucher
            </div>

        </div>

    </div>
</div>
@endsection
