@extends('layouts.app')

@section('title', 'Return Receipt #' . $return->return_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Bill Returns</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $return->return_no }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">

        <!-- Screen Action Toolbar (Hidden in print) -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 d-print-none">
            <div>
                <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Back to Returns
                </a>
                <h1 class="h3 fw-bold text-dark mb-0">Merchandise Return Receipt</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('sales.show', $return->sale) }}" class="btn btn-outline-primary">
                    <i class="bi bi-receipt me-1"></i> View Original Invoice
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer me-1"></i> Print Receipt
                </button>
            </div>
        </div>

        <!-- Printable Return Receipt Card -->
        <div class="card border-0 shadow-sm p-4 p-md-5 bg-white" id="printable-return">

            <!-- Return Header -->
            <div class="d-flex flex-column flex-sm-row justify-content-between border-bottom pb-4 mb-4 gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning text-dark rounded-3 p-3 d-flex align-items-center justify-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bi bi-arrow-counterclockwise fs-2"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-black text-dark mb-0 tracking-tight">BEE COMFORT</h2>
                        <span class="text-secondary small fw-medium">Customer Merchandise Return Voucher</span>
                        <div class="text-secondary small mt-1">
                            Warehouse Intake &bull; Email: returns@beecomfort.local
                        </div>
                    </div>
                </div>

                <div class="text-sm-end">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 text-uppercase fw-bold">
                        Return Receipt
                    </span>
                    <h3 class="h4 font-monospace fw-bold text-danger mt-2 mb-1">{{ $return->return_no }}</h3>
                    <div class="text-secondary small">
                        Return Date: <strong class="text-dark">{{ $return->return_date->format('d M Y') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Customer & Reference Info -->
            <div class="row g-4 pb-4 mb-4 border-bottom">
                <div class="col-sm-6">
                    <span class="text-secondary text-uppercase fw-bold fs-7 d-block mb-1">Customer Details:</span>
                    <h5 class="fw-bold text-dark mb-1">{{ $return->customer->name }}</h5>
                    @if($return->customer->phone)
                        <div class="text-secondary small"><i class="bi bi-telephone me-1 text-primary"></i> {{ $return->customer->phone }}</div>
                    @endif
                    @if($return->customer->address)
                        <div class="text-secondary small mt-1"><i class="bi bi-geo-alt me-1 text-primary"></i> {{ $return->customer->address }}</div>
                    @endif
                </div>

                <div class="col-sm-6 text-sm-end">
                    <span class="text-secondary text-uppercase fw-bold fs-7 d-block mb-1">Original Invoice Reference:</span>
                    <div class="h5 fw-bold font-monospace mb-1">
                        <a href="{{ route('sales.show', $return->sale) }}" class="text-primary text-decoration-none">
                            {{ $return->sale->invoice_no }}
                        </a>
                    </div>
                    <div class="text-secondary small">Invoice Date: {{ $return->sale->invoice_date->format('d M Y') }}</div>
                    <div class="text-success small fw-bold mt-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Stock Automatically Restored into Inventory
                    </div>
                </div>
            </div>

            <!-- Returned Items Table -->
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-center" style="width: 50px;">#</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">Returned Product (Unified Title)</th>
                            <th class="py-3 text-secondary text-uppercase fs-7">SKU</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Rate</th>
                            <th class="py-3 text-center text-secondary text-uppercase fs-7">Qty Returned</th>
                            <th class="py-3 text-end text-secondary text-uppercase fs-7">Refund Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->details as $idx => $item)
                        <tr>
                            <td class="text-center text-secondary">{{ $idx + 1 }}</td>
                            <td>
                                <span class="fw-bold text-dark d-block fs-6">
                                    {{ $item->product_title }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                    {{ $item->sku_code }}
                                </span>
                            </td>
                            <td class="text-end font-monospace text-secondary">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold fs-7 px-2 py-1">
                                    +{{ $item->quantity }}
                                </span>
                            </td>
                            <td class="text-end font-monospace fw-bold text-danger">${{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & Reason -->
            <div class="row g-4 pt-3 border-top">
                <div class="col-sm-6">
                    @if($return->reason)
                        <div class="bg-light p-3 rounded-3 mb-2">
                            <span class="fw-bold text-dark small d-block mb-1">Return Reason / Feedback:</span>
                            <p class="text-secondary small mb-0">{{ $return->reason }}</p>
                        </div>
                    @endif
                    <p class="text-secondary small mb-0">
                        Inventory quantities have been updated to reflect this return.
                    </p>
                </div>

                <div class="col-sm-6">
                    <div class="d-flex flex-column gap-2 text-sm-end">
                        <div class="d-flex justify-content-between justify-content-sm-end gap-4 text-secondary">
                            <span>Total Units Returned:</span>
                            <span class="font-monospace text-dark fw-bold">{{ $return->details->sum('quantity') }} unit(s)</span>
                        </div>

                        <div class="d-flex justify-content-between justify-content-sm-end gap-4 pt-2 border-top">
                            <span class="h5 fw-bold text-dark mb-0">Total Refund Credited:</span>
                            <span class="h4 fw-black text-danger font-monospace mb-0">${{ number_format($return->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
