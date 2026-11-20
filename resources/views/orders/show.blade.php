@extends('layouts.app')

@section('title', 'Order Details ' . $order->order_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none text-secondary">Sale Orders</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $order->order_no }}</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h3 fw-bold text-dark mb-0">Order {{ $order->order_no }}</h1>
                @php $badge = $order->status_badge; @endphp
                <span class="badge {{ $badge['class'] }} px-2.5 py-1">
                    <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                </span>
            </div>
            <p class="text-secondary small mb-0 mt-1">Booked for {{ $order->customer->name ?? 'Walk-in' }} ({{ $order->customer->formatted_code ?? 'B000' }}) on {{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if(Auth::user()->hasPermission('orders', 'print'))
            <a href="{{ route('orders.print', $order) }}" class="btn btn-outline-primary btn-sm px-3" target="_blank">
                <i class="bi bi-printer me-1"></i> Print Sheet
            </a>
            @endif
            @if(Auth::user()->hasPermission('orders', 'update'))
            <a href="{{ route('orders.edit', $order) }}" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-pencil-square me-1"></i> Edit Order
            </a>
            @endif
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    <!-- Order Summary Cards -->
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Customer</div>
                <div class="h6 fw-bold text-dark mb-0 text-truncate">{{ $order->customer->name ?? 'N/A' }}</div>
                <span class="badge bg-light text-primary border font-monospace mt-1 align-self-start">{{ $order->customer->formatted_code ?? 'B000' }}</span>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Booking Date</div>
                <div class="h5 fw-bold text-dark mb-0 font-monospace">{{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Total Quantity</div>
                <div class="h4 fw-black text-success mb-0 font-monospace">{{ number_format($order->total_quantity) }} <span class="fs-6 fw-normal text-secondary">pcs</span></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-secondary small fw-bold text-uppercase">Booking Status</div>
                <div class="h5 fw-bold text-dark mb-0">{{ $order->status }}</div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm bg-white overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="fw-bold text-dark">Booked Articles & Size Breakdown</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th class="text-center">Cup</th>
                        <th>Color</th>
                        <th class="text-center">32</th>
                        <th class="text-center">34</th>
                        <th class="text-center">36</th>
                        <th class="text-center">38</th>
                        <th class="text-center">40</th>
                        <th class="text-center">42</th>
                        <th class="text-center bg-warning-subtle text-warning-emphasis">44</th>
                        <th class="text-center bg-warning-subtle text-warning-emphasis">46</th>
                        <th class="text-center bg-warning-subtle text-warning-emphasis">48</th>
                        <th class="text-center bg-warning-subtle text-warning-emphasis">50</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="fw-bold text-dark">{{ $item->article_name }}</td>
                            <td class="text-center font-monospace fw-bold text-secondary">{{ $item->cup ?: '-' }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark border font-monospace">{{ $item->color ?: 'STANDARD' }}</span>
                            </td>
                            <td class="text-center font-monospace">{{ $item->size_32 ?: '-' }}</td>
                            <td class="text-center font-monospace">{{ $item->size_34 ?: '-' }}</td>
                            <td class="text-center font-monospace">{{ $item->size_36 ?: '-' }}</td>
                            <td class="text-center font-monospace">{{ $item->size_38 ?: '-' }}</td>
                            <td class="text-center font-monospace">{{ $item->size_40 ?: '-' }}</td>
                            <td class="text-center font-monospace">{{ $item->size_42 ?: '-' }}</td>
                            <td class="text-center font-monospace bg-light-subtle">{{ $item->size_44 ?: '-' }}</td>
                            <td class="text-center font-monospace bg-light-subtle">{{ $item->size_46 ?: '-' }}</td>
                            <td class="text-center font-monospace bg-light-subtle">{{ $item->size_48 ?: '-' }}</td>
                            <td class="text-center font-monospace bg-light-subtle">{{ $item->size_50 ?: '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold px-2 py-1">{{ $item->total_qty }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
