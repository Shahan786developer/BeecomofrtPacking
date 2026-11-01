@extends('layouts.app')

@section('title', 'Invoice #' . $sale->invoice_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $sale->invoice_no }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">

        <!-- Screen Action Toolbar (Hidden in print) -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 d-print-none">
            <div>
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Back to Invoices
                </a>
                <h1 class="h3 fw-bold text-dark mb-0">Sales Invoice Details</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" class="btn btn-outline-warning">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Process Return
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer me-1"></i> Print Invoice
                </button>
            </div>
        </div>

        <!-- Printable Invoice Paper Card -->
        <div class="card border-0 shadow-sm p-4 p-md-5 bg-white" id="printable-invoice">
            
            <!-- Invoice Header -->
            <div class="d-flex flex-column flex-sm-row justify-content-between border-bottom pb-4 mb-4 gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bi bi-box-seam fs-2"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-black text-primary mb-0 tracking-tight">BEE COMFORT</h2>
                        <span class="text-secondary small fw-medium">Inventory & Retail Packing Management</span>
                        <div class="text-secondary small mt-1">
                            Phone: +92 300 0000000 &bull; Email: billing@beecomfort.local
                        </div>
                    </div>
                </div>

                <div class="text-sm-end">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 text-uppercase fw-bold">
                        Customer Invoice
                    </span>
                    <h3 class="h4 font-monospace fw-bold text-dark mt-2 mb-1">{{ $sale->invoice_no }}</h3>
                    <div class="text-secondary small">
                        Invoice Date: <strong class="text-dark">{{ $sale->invoice_date->format('d M Y') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Info -->
            <div class="row g-4 pb-4 mb-4 border-bottom">
                <div class="col-sm-6">
                    <span class="text-secondary text-uppercase fw-bold fs-7 d-block mb-1">Billed To (Customer):</span>
                    <h5 class="fw-bold text-dark mb-1">{{ $sale->customer->name }}</h5>
                    @if($sale->customer->phone)
                        <div class="text-secondary small"><i class="bi bi-telephone me-1 text-primary"></i> {{ $sale->customer->phone }}</div>
                    @endif
                    @if($sale->customer->email)
                        <div class="text-secondary small"><i class="bi bi-envelope me-1 text-primary"></i> {{ $sale->customer->email }}</div>
                    @endif
                    @if($sale->customer->address)
                        <div class="text-secondary small mt-1"><i class="bi bi-geo-alt me-1 text-primary"></i> {{ $sale->customer->address }}</div>
                    @endif
                </div>

                <div class="col-sm-6 text-sm-end">
                    <span class="text-secondary text-uppercase fw-bold fs-7 d-block mb-1">Invoice Info:</span>
                    <div class="text-secondary small">Created: <strong class="text-dark">{{ $sale->created_at->format('d M Y, H:i') }}</strong></div>
                    <div class="text-secondary small">Status: <span class="badge bg-success-subtle text-success">Fulfilled & Deducted</span></div>
                </div>
            </div>

            <!-- Items Table (Unified Product Title: Category + Dozens + Product Code) -->
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-center" style="width: 50px;">#</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Product Description (Unified Title)</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Rate</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Qty</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->details as $idx => $detail)
                        <tr>
                            <td class="text-center text-secondary">{{ $idx + 1 }}</td>
                            <td>
                                <span class="fw-bold text-dark d-block fs-6">
                                    {{ $detail->product_title }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                    {{ $detail->sku_code }}
                                </span>
                            </td>
                            <td class="text-end font-monospace text-secondary">Rs. {{ number_format($detail->unit_price, 2) }}</td>
                            <td class="text-center fw-bold">{{ $detail->quantity }}</td>
                            <td class="text-end font-monospace fw-bold text-dark">Rs. {{ number_format($detail->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & Notes -->
            <div class="row g-4 pt-3 border-top">
                <div class="col-sm-6">
                    @if($sale->notes)
                        <div class="bg-light p-3 rounded-3 mb-2">
                            <span class="fw-bold text-dark small d-block mb-1">Remarks:</span>
                            <p class="text-secondary small mb-0">{{ $sale->notes }}</p>
                        </div>
                    @endif
                    <p class="text-secondary small mb-0">
                        Thank you for your business with Bee Comfort! All merchandise is packed and quality-inspected.
                    </p>
                </div>

                <div class="col-sm-6">
                    <div class="d-flex flex-column gap-2 text-sm-end">
                        <div class="d-flex justify-content-between justify-content-sm-end gap-4 text-secondary">
                            <span>Subtotal:</span>
                            <span class="font-monospace text-dark fw-bold">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                        </div>

                        @if($sale->discount > 0)
                        <div class="d-flex justify-content-between justify-content-sm-end gap-4 text-danger">
                            <span>Discount:</span>
                            <span class="font-monospace fw-bold">-Rs. {{ number_format($sale->discount, 2) }}</span>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between justify-content-sm-end gap-4 pt-2 border-top">
                            <span class="h5 fw-bold text-dark mb-0">Grand Total:</span>
                            <span class="h4 fw-black text-primary font-monospace mb-0">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
