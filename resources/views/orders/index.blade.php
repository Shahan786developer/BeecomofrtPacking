@extends('layouts.app')

@section('title', 'Sale Orders Matrix')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sale Orders</li>
@endsection

@push('styles')
<style>
    /* Headache-Free, Ultra-Clean Matrix Table Design */
    .order-matrix-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .matrix-table {
        margin-bottom: 0;
        border-collapse: collapse;
        width: 100%;
        font-size: 0.86rem;
        background: #ffffff;
    }

    /* Soft Blue Professional Header */
    .matrix-table thead th {
        background: #eef2ff;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 0.74rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 0.75rem 0.55rem;
        border: 1px solid #cbd5e1;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
    }

    .matrix-table thead th.th-left {
        text-align: left;
        padding-left: 12px;
    }

    /* Size Column Header Specific Styles */
    .matrix-table thead th.size-std {
        background: #e0f2fe;
        color: #0369a1;
        min-width: 44px;
    }

    .matrix-table thead th.size-plus {
        background: #fef3c7;
        color: #92400e;
        min-width: 44px;
    }

    /* Table Rows and Cell Alignment */
    .matrix-table tbody tr td {
        padding: 0.6rem 0.55rem;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
        font-size: 0.86rem;
        background-color: #ffffff;
    }

    .matrix-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Strong Separator Between Different Orders */
    .matrix-table tbody tr.order-group-first-row {
        border-top: 2px solid #94a3b8 !important;
    }

    .matrix-table tbody tr:first-child {
        border-top: none !important;
    }

    /* Order-Level Merged Cell Styling */
    .cell-order-header {
        background: #ffffff !important;
        vertical-align: middle !important;
    }

    /* Size Quantity Styling */
    .cell-qty {
        text-align: center;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.88rem;
    }

    .qty-active {
        font-weight: 700;
        color: #0f172a;
        background: #eff6ff;
        border-radius: 4px;
        padding: 2px 4px;
    }

    .qty-empty {
        color: #cbd5e1;
        font-size: 0.82rem;
    }

    /* Total Badge */
    .badge-qty-total {
        background-color: #10b981;
        color: #ffffff;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 30px;
        display: inline-block;
        font-size: 0.85rem;
        min-width: 45px;
        text-align: center;
    }

    /* Action Buttons (Pill styling: Blue Edit, Red View, Red Delete) */
    .btn-action-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 30px;
        font-size: 0.72rem;
        font-weight: 600;
        text-decoration: none;
        background: #ffffff;
        border: 1px solid;
        transition: 0.15s ease;
        white-space: nowrap;
    }

    .btn-edit-pill {
        border-color: #3b82f6;
        color: #2563eb;
    }
    .btn-edit-pill:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .btn-view-pill {
        border-color: #ef4444;
        color: #dc2626;
    }
    .btn-view-pill:hover {
        background: #fef2f2;
        color: #b91c1c;
    }

    .btn-delete-pill {
        border: none;
        background: transparent;
        color: #ef4444;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 4px;
    }
    .btn-delete-pill:hover {
        color: #b91c1c;
        text-decoration: underline;
    }

    /* Compact Filter Bar */
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column gap-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h3 fw-bold text-dark mb-0">Sale Orders</h1>
                <span class="badge bg-primary text-white px-2.5 py-1">Hashmi Brothers</span>
            </div>
            <p class="text-secondary small mb-0 mt-1">Book customer articles by Cup, Color, and 10-size matrix breakdown (32 to 50) with clean grouping.</p>
        </div>
        @if(Auth::user()->hasPermission('orders', 'create'))
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('orders.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Book New Order</span>
            </a>
        </div>
        @endif
    </div>

    <!-- QUICK METRIC SUMMARY CARDS -->
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Total Orders</div>
                        <div class="h4 fw-black text-dark mb-0 font-monospace">{{ number_format($totalOrdersCount) }}</div>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2">
                        <i class="bi bi-journal-text fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Total Pieces Booked</div>
                        <div class="h4 fw-black text-success mb-0 font-monospace">{{ number_format($totalPiecesBooked) }} <span class="fs-7 fw-normal text-secondary">pcs</span></div>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-2">
                        <i class="bi bi-boxes fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Pending Orders</div>
                        <div class="h4 fw-black text-warning-emphasis mb-0 font-monospace">{{ number_format($pendingCount) }}</div>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning-emphasis p-2">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase">Completed</div>
                        <div class="h4 fw-black text-primary mb-0 font-monospace">{{ number_format($completedCount) }}</div>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-2">
                        <i class="bi bi-check2-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER ORDERS BAR -->
    <div class="filter-card p-3">
        <div class="fw-bold text-dark small mb-2"><i class="bi bi-funnel me-1 text-primary"></i> Filter Orders</div>
        <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-end">
            <!-- Voucher / Order No Dropdown & Search -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">Voucher No</label>
                <div class="input-group input-group-sm">
                    <select name="order_no" class="form-select form-select-sm">
                        <option value="">All Voucher Nos</option>
                        @foreach($recentOrderNos as $vNo)
                            <option value="{{ $vNo }}" {{ request('order_no') == $vNo ? 'selected' : '' }}>
                                {{ $vNo }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Customer with Code -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->search_dropdown_label }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(Auth::user()->isSuperAdmin())
            <!-- Filter by User (Super Admin only) -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">Created By (User)</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Start Date -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>

            <!-- End Date -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>

            <!-- Status Dropdown -->
            <div class="col-6 col-md-1">
                <label class="form-label small fw-bold text-secondary mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Apply Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                @if(request()->anyFilled(['order_no', 'customer_id', 'user_id', 'status', 'start_date', 'end_date']))
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- CLEAN, PROFESSIONAL MATRIX TABLE (Rowspan Grouping + Solid Order Separator) -->
    <div class="order-matrix-card">
        <div class="table-responsive">
            <table class="table matrix-table align-middle">
                <thead>
                    <tr>
                        <!-- Order Level Columns -->
                        <th class="th-left" style="width: 95px;">Order No</th>
                        <th style="width: 95px;">Date</th>
                        <th class="th-left" style="min-width: 170px;">Customer</th>
                        <th style="width: 95px;">Status</th>

                        <!-- Article Specification Columns -->
                        <th class="th-left" style="min-width: 170px;">Article</th>
                        <th style="width: 48px;">Cup</th>
                        <th class="th-left" style="width: 100px;">Color</th>

                        <!-- Standard Sizes (32 - 42) -->
                        <th class="size-std">32</th>
                        <th class="size-std">34</th>
                        <th class="size-std">36</th>
                        <th class="size-std">38</th>
                        <th class="size-std">40</th>
                        <th class="size-std">42</th>

                        <!-- Plus Sizes (44 - 50) -->
                        <th class="size-plus">44</th>
                        <th class="size-plus">46</th>
                        <th class="size-plus">48</th>
                        <th class="size-plus">50</th>

                        <!-- Total & Action Columns -->
                        <th style="width: 75px;">Total</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $orderIndex => $order)
                        @php
                            $items = $order->items;
                            $rowCount = count($items);
                        @endphp

                        @if($rowCount > 0)
                            @php $firstItem = $items[0]; @endphp
                            <!-- FIRST ROW OF ORDER (Shows Order No, Date, Customer, Status, Actions with rowspan) -->
                            <tr class="order-group-first-row">
                                <td rowspan="{{ $rowCount }}" class="cell-order-header text-start">
                                    <span class="badge bg-primary font-monospace fs-7 px-2.5 py-1.5">{{ $order->order_no }}</span>
                                    @if(Auth::user()->isSuperAdmin() && $order->user)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mt-1 d-block text-truncate" style="max-width: 100px;" title="Created by {{ $order->user->name }}">
                                            <i class="bi bi-person me-1"></i>{{ $order->user->name }}
                                        </span>
                                    @endif
                                </td>
                                <td rowspan="{{ $rowCount }}" class="cell-order-header text-center">
                                    <span class="fw-semibold text-secondary small font-monospace">
                                        {{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                                <td rowspan="{{ $rowCount }}" class="cell-order-header text-start">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" title="{{ $order->customer->name ?? '' }}">
                                        {{ $order->customer->name ?? 'Walk-in Retail' }}
                                    </div>
                                    <span class="badge bg-light text-secondary border font-monospace small mt-0.5">
                                        {{ $order->customer->formatted_code ?? 'B000' }}
                                    </span>
                                </td>
                                <td rowspan="{{ $rowCount }}" class="cell-order-header text-center">
                                    @php $badge = $order->status_badge; @endphp
                                    <span class="badge {{ $badge['class'] }} rounded-pill px-2.5 py-1 small">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <!-- First Item Details -->
                                <td class="fw-semibold text-dark text-start">{{ $firstItem->article_name }}</td>
                                <td class="text-center font-monospace fw-bold text-secondary">{{ $firstItem->cup ?: '-' }}</td>
                                <td class="text-start">
                                    <span class="badge bg-secondary-subtle text-dark border font-monospace px-2 py-0.5">
                                        {{ $firstItem->color ?: 'STANDARD' }}
                                    </span>
                                </td>

                                <!-- First Item Sizes 32-42 -->
                                <td class="cell-qty">{{ $firstItem->size_32 > 0 ? $firstItem->size_32 : '-' }}</td>
                                <td class="cell-qty">{{ $firstItem->size_34 > 0 ? $firstItem->size_34 : '-' }}</td>
                                <td class="cell-qty">{{ $firstItem->size_36 > 0 ? $firstItem->size_36 : '-' }}</td>
                                <td class="cell-qty">{{ $firstItem->size_38 > 0 ? $firstItem->size_38 : '-' }}</td>
                                <td class="cell-qty">{{ $firstItem->size_40 > 0 ? $firstItem->size_40 : '-' }}</td>
                                <td class="cell-qty">{{ $firstItem->size_42 > 0 ? $firstItem->size_42 : '-' }}</td>

                                <!-- First Item Sizes 44-50 -->
                                <td class="cell-qty bg-light-subtle">{{ $firstItem->size_44 > 0 ? $firstItem->size_44 : '-' }}</td>
                                <td class="cell-qty bg-light-subtle">{{ $firstItem->size_46 > 0 ? $firstItem->size_46 : '-' }}</td>
                                <td class="cell-qty bg-light-subtle">{{ $firstItem->size_48 > 0 ? $firstItem->size_48 : '-' }}</td>
                                <td class="cell-qty bg-light-subtle">{{ $firstItem->size_50 > 0 ? $firstItem->size_50 : '-' }}</td>

                                <!-- First Item Total -->
                                <td class="text-center">
                                    <span class="badge-qty-total">{{ number_format($firstItem->total_qty) }}</span>
                                </td>

                                <!-- Actions Rowspan -->
                                <td rowspan="{{ $rowCount }}" class="cell-order-header text-center" style="white-space: nowrap;">
                                    <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                        @if(Auth::user()->hasPermission('orders', 'update'))
                                        <a href="{{ route('orders.edit', $order) }}" class="btn-action-pill btn-edit-pill" title="Edit Order">
                                            ✏️ Edit
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasPermission('orders', 'print'))
                                        <a href="{{ route('orders.print', $order) }}" class="btn-action-pill btn-view-pill" title="View / Print Receipt" target="_blank">
                                            👁️ View
                                        </a>
                                        @endif
                                        @if(Auth::user()->hasPermission('orders', 'delete'))
                                        <form method="POST" action="{{ route('orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Delete Order {{ $order->order_no }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete-pill" title="Delete Order">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- REMAINING ITEMS (Rows 2, 3, etc.) - Only Article, Cup, Color, Sizes, Total -->
                            @for($i = 1; $i < $rowCount; $i++)
                                @php $item = $items[$i]; @endphp
                                <tr>
                                    <td class="fw-semibold text-dark text-start">{{ $item->article_name }}</td>
                                    <td class="text-center font-monospace fw-bold text-secondary">{{ $item->cup ?: '-' }}</td>
                                    <td class="text-start">
                                        <span class="badge bg-secondary-subtle text-dark border font-monospace px-2 py-0.5">
                                            {{ $item->color ?: 'STANDARD' }}
                                        </span>
                                    </td>

                                    <td class="cell-qty">{{ $item->size_32 > 0 ? $item->size_32 : '-' }}</td>
                                    <td class="cell-qty">{{ $item->size_34 > 0 ? $item->size_34 : '-' }}</td>
                                    <td class="cell-qty">{{ $item->size_36 > 0 ? $item->size_36 : '-' }}</td>
                                    <td class="cell-qty">{{ $item->size_38 > 0 ? $item->size_38 : '-' }}</td>
                                    <td class="cell-qty">{{ $item->size_40 > 0 ? $item->size_40 : '-' }}</td>
                                    <td class="cell-qty">{{ $item->size_42 > 0 ? $item->size_42 : '-' }}</td>

                                    <td class="cell-qty bg-light-subtle">{{ $item->size_44 > 0 ? $item->size_44 : '-' }}</td>
                                    <td class="cell-qty bg-light-subtle">{{ $item->size_46 > 0 ? $item->size_46 : '-' }}</td>
                                    <td class="cell-qty bg-light-subtle">{{ $item->size_48 > 0 ? $item->size_48 : '-' }}</td>
                                    <td class="cell-qty bg-light-subtle">{{ $item->size_50 > 0 ? $item->size_50 : '-' }}</td>

                                    <td class="text-center">
                                        <span class="badge-qty-total">{{ number_format($item->total_qty) }}</span>
                                    </td>
                                </tr>
                            @endfor
                        @else
                            <!-- Order with no items -->
                            <tr class="order-group-first-row">
                                <td class="cell-order-header text-start">
                                    <span class="badge bg-primary font-monospace">{{ $order->order_no }}</span>
                                </td>
                                <td class="cell-order-header text-center small text-secondary">
                                    {{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="cell-order-header text-start">
                                    <div class="fw-bold text-dark">{{ $order->customer->name ?? 'Walk-in' }}</div>
                                    <span class="badge bg-light text-secondary border font-monospace small">{{ $order->customer->formatted_code ?? '' }}</span>
                                </td>
                                <td class="cell-order-header text-center">
                                    @php $badge = $order->status_badge; @endphp
                                    <span class="badge {{ $badge['class'] }} rounded-pill px-2.5 py-1 small">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td colspan="12" class="text-center text-muted py-3 small fst-italic">— No items recorded for order {{ $order->order_no }} —</td>
                                <td class="text-center"><span class="badge-qty-total">0</span></td>
                                <td class="cell-order-header text-center" style="white-space: nowrap;">
                                    <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                        @if(Auth::user()->hasPermission('orders', 'update'))
                                        <a href="{{ route('orders.edit', $order) }}" class="btn-action-pill btn-edit-pill">✏️ Edit</a>
                                        @endif
                                        @if(Auth::user()->hasPermission('orders', 'print'))
                                        <a href="{{ route('orders.print', $order) }}" class="btn-action-pill btn-view-pill" target="_blank">👁️ View</a>
                                        @endif
                                        @if(Auth::user()->hasPermission('orders', 'delete'))
                                        <form method="POST" action="{{ route('orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Delete Order {{ $order->order_no }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete-pill" title="Delete Order">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="18" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                                    <div class="h5 fw-bold text-dark mb-0">No Sale Orders Found</div>
                                    <p class="text-secondary small mb-3">No matrix booking orders match your filter criteria or none have been booked yet.</p>
                                    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                                        <i class="bi bi-plus-circle-fill me-1"></i> Book First Order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
