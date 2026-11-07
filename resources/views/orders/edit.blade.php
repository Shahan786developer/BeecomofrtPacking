@extends('layouts.app')

@section('title', 'Edit Order ' . $order->order_no . ' — Hashmi Brothers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none text-secondary">Sale Orders</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $order->order_no }}</li>
@endsection

@push('styles')
<style>
    .order-booking-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .article-block {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .article-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.25rem;
    }

    .size-matrix-table {
        margin-bottom: 0;
        font-size: 0.86rem;
    }

    .size-matrix-table thead th {
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
        padding: 0.55rem 0.35rem;
        border-bottom: 1px solid #cbd5e1;
        vertical-align: middle;
    }

    .size-matrix-table thead th.th-std {
        background: #e0f2fe;
        color: #0369a1;
    }

    .size-matrix-table thead th.th-plus {
        background: #fef3c7;
        color: #92400e;
    }

    .size-matrix-table tbody td {
        padding: 0.45rem 0.35rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .size-input {
        width: 100%;
        min-width: 48px;
        text-align: center;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        padding: 0.35rem 0.2rem;
        border-radius: 6px;
        font-size: 0.86rem;
    }

    .size-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .sticky-order-summary {
        position: sticky;
        bottom: 0;
        background: #ffffff;
        border-top: 2px solid #e2e8f0;
        box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
        padding: 1rem 1.5rem;
        z-index: 1000;
        border-radius: 12px 12px 0 0;
    }
</style>
@endpush

@section('content')
<form action="{{ route('orders.update', $order) }}" method="POST" id="orderBookingForm">
    @csrf
    @method('PUT')

    <div class="d-flex flex-column gap-3 mb-5">

        <!-- HEADER TITLE -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-dark mb-0">Edit Order {{ $order->order_no }}</h1>
                    <span class="badge bg-primary text-white px-2.5 py-1">Hashmi Brothers</span>
                </div>
                <p class="text-secondary small mb-0 mt-1">Modify customer order booking details and size quantities.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('orders.print', $order) }}" class="btn btn-outline-primary btn-sm px-3" target="_blank">
                    <i class="bi bi-printer me-1"></i> Print Sheet
                </a>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Orders
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0 py-2">
                <div class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please check form errors:</div>
                <ul class="mb-0 small ps-3 mt-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ORDER HEADER CONFIGURATION CARD -->
        <div class="order-booking-card p-3 p-md-4">
            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Order & Customer Details
            </h5>

            <div class="row g-3">
                <!-- Customer Selection -->
                <div class="col-12 col-md-4">
                    <label class="form-label required fw-bold small text-secondary">Customer *</label>
                    <select name="customer_id" id="customerId" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id', $order->customer_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->formatted_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Order No -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label required fw-bold small text-secondary">Order / Voucher No *</label>
                    <input type="text" name="order_no" class="form-control font-monospace fw-bold @error('order_no') is-invalid @enderror" value="{{ old('order_no', $order->order_no) }}" required>
                </div>

                <!-- Order Date -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label required fw-bold small text-secondary">Order Date *</label>
                    <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $order->order_date ? $order->order_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>

                <!-- Order Status -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label required fw-bold small text-secondary">Status *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="Pending" {{ old('status', $order->status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Completed" {{ old('status', $order->status) === 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Rejected" {{ old('status', $order->status) === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Notes / Special Instructions -->
                <div class="col-12">
                    <label class="form-label fw-bold small text-secondary">Special Booking Instructions / Remarks</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Deliver before next Monday..." value="{{ old('notes', $order->notes) }}">
                </div>
            </div>
        </div>

        <!-- ARTICLES CONTAINER -->
        <div id="articlesContainer">
            @php $artIdx = 0; @endphp
            @forelse($groupedArticles as $articleName => $items)
                <div class="article-block" data-article-index="{{ $artIdx }}">
                    <div class="article-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 600px;">
                            <span class="badge bg-primary rounded-pill px-2.5 py-1.5 article-badge">Article #{{ $artIdx + 1 }}</span>
                            <div class="flex-grow-1">
                                <input list="articleSuggestions" name="articles[{{ $artIdx }}][article_name]" class="form-control form-control-sm fw-bold text-dark article-name-input" placeholder="Article Name" value="{{ $articleName }}" required>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border fs-7 font-monospace">
                                Article Total: <span class="article-total-pieces fw-bold text-dark">0</span> pcs
                            </span>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2 remove-article-btn {{ count($groupedArticles) > 1 ? '' : 'd-none' }}" title="Remove Article">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table size-matrix-table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 130px;" class="text-start ps-3">Color</th>
                                    <th style="width: 70px;">Cup</th>
                                    <th class="th-std">32</th>
                                    <th class="th-std">34</th>
                                    <th class="th-std">36</th>
                                    <th class="th-std">38</th>
                                    <th class="th-std">40</th>
                                    <th class="th-std">42</th>
                                    <th class="th-plus">44</th>
                                    <th class="th-plus">46</th>
                                    <th class="th-plus">48</th>
                                    <th class="th-plus">50</th>
                                    <th style="width: 75px;">Rate Std</th>
                                    <th style="width: 75px;">Rate Plus</th>
                                    <th style="width: 80px;">Total Pcs</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody class="rows-tbody">
                                @foreach($items as $rowIdx => $item)
                                    <tr class="item-row" data-row-index="{{ $rowIdx }}">
                                        <td class="ps-3">
                                            <input list="colorSuggestions" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][color]" class="form-control form-control-sm text-uppercase fw-semibold" placeholder="Color" value="{{ $item->color }}">
                                        </td>
                                        <td>
                                            <select name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][cup]" class="form-select form-select-sm text-center fw-bold">
                                                <option value="B" {{ $item->cup === 'B' ? 'selected' : '' }}>B</option>
                                                <option value="C" {{ $item->cup === 'C' ? 'selected' : '' }}>C</option>
                                                <option value="D" {{ $item->cup === 'D' ? 'selected' : '' }}>D</option>
                                            </select>
                                        </td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_32]" class="form-control form-control-sm size-input input-std" value="{{ $item->size_32 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_34]" class="form-control form-control-sm size-input input-std" value="{{ $item->size_34 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_36]" class="form-control form-control-sm size-input input-std" value="{{ $item->size_36 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_38]" class="form-control form-control-sm size-input input-std" value="{{ $item->size_38 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_40]" class="form-control form-control-sm size-input input-std" value="{{ $item->size_40 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_42]" class="form-control form-control-sm size-input input-std" value="{{ $item->size_42 }}"></td>

                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_44]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="{{ $item->size_44 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_46]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="{{ $item->size_46 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_48]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="{{ $item->size_48 }}"></td>
                                        <td><input type="number" min="0" step="1" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][size_50]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="{{ $item->size_50 }}"></td>

                                        <td><input type="number" min="0" step="0.5" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][rate_std]" class="form-control form-control-sm text-center rate-std-input" placeholder="0" value="{{ $item->rate_std }}"></td>
                                        <td><input type="number" min="0" step="0.5" name="articles[{{ $artIdx }}][items][{{ $rowIdx }}][rate_plus]" class="form-control form-control-sm text-center rate-plus-input" placeholder="0" value="{{ $item->rate_plus }}"></td>

                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold px-2 py-1 row-total-badge">{{ $item->total_qty }}</span>
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-row-btn" title="Delete Row">
                                                <i class="bi bi-x-circle fs-6"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-2 bg-light border-top d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 add-color-row-btn">
                            <i class="bi bi-plus-lg me-1"></i> Add Color / Cup Row
                        </button>
                        <span class="small text-secondary fst-italic">Standard Sizes: 32 to 42 | Plus Sizes: 44 to 50</span>
                    </div>
                </div>
                @php $artIdx++; @endphp
            @empty
                <!-- Fallback empty article block if order was empty -->
                <div class="article-block" data-article-index="0">
                    <div class="article-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 600px;">
                            <span class="badge bg-primary rounded-pill px-2.5 py-1.5 article-badge">Article #1</span>
                            <div class="flex-grow-1">
                                <input list="articleSuggestions" name="articles[0][article_name]" class="form-control form-control-sm fw-bold text-dark article-name-input" placeholder="Article Name" value="COMFORT FIT 32-42" required>
                            </div>
                        </div>
                    </div>
                    <!-- Empty row body -->
                </div>
            @endforelse
        </div>

        <!-- ADD ARTICLE BUTTON -->
        <div>
            <button type="button" id="addNewArticleBtn" class="btn btn-outline-success px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i>
                <span class="fw-bold">Add Another Article</span>
            </button>
        </div>

    </div>

    <!-- DATALISTS FOR AUTOCOMPLETE -->
    <datalist id="articleSuggestions">
        @foreach($availableArticles as $art)
            <option value="{{ $art }}">
        @endforeach
    </datalist>

    <datalist id="colorSuggestions">
        @foreach($availableColors as $col)
            <option value="{{ $col }}">
        @endforeach
    </datalist>

    <!-- STICKY ORDER BOTTOM SUMMARY BAR -->
    <div class="sticky-order-summary">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">Total Articles</div>
                    <div class="h5 fw-bold text-dark mb-0 font-monospace" id="summaryTotalArticles">{{ count($groupedArticles) }}</div>
                </div>
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">Total Color Lines</div>
                    <div class="h5 fw-bold text-dark mb-0 font-monospace" id="summaryTotalRows">{{ $order->items->count() }}</div>
                </div>
                <div class="border-start ps-4">
                    <div class="text-secondary small fw-bold text-uppercase">Grand Total Quantity</div>
                    <div class="h3 fw-black text-success mb-0 font-monospace">
                        <span id="summaryGrandTotalQty">{{ number_format($order->total_quantity) }}</span> <span class="fs-6 fw-normal text-secondary">pieces</span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('orders.index') }}" class="btn btn-light border px-3">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Update Order</span>
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('articlesContainer');
    const addArticleBtn = document.getElementById('addNewArticleBtn');

    function recalculateAll() {
        let grandTotalQty = 0;
        let totalArticles = 0;
        let totalRows = 0;

        const articleBlocks = container.querySelectorAll('.article-block');
        totalArticles = articleBlocks.length;

        articleBlocks.forEach((artBlock, artIdx) => {
            const badge = artBlock.querySelector('.article-badge');
            if (badge) badge.textContent = `Article #${artIdx + 1}`;

            const removeArtBtn = artBlock.querySelector('.remove-article-btn');
            if (removeArtBtn) {
                if (totalArticles > 1) {
                    removeArtBtn.classList.remove('d-none');
                } else {
                    removeArtBtn.classList.add('d-none');
                }
            }

            let articleQty = 0;
            const rows = artBlock.querySelectorAll('.item-row');
            totalRows += rows.length;

            rows.forEach((row) => {
                let rowQty = 0;
                row.querySelectorAll('.size-input').forEach(input => {
                    const val = parseInt(input.value, 10);
                    if (!isNaN(val) && val > 0) {
                        rowQty += val;
                    }
                });

                const rowBadge = row.querySelector('.row-total-badge');
                if (rowBadge) {
                    rowBadge.textContent = rowQty;
                }

                articleQty += rowQty;
            });

            const artPiecesEl = artBlock.querySelector('.article-total-pieces');
            if (artPiecesEl) {
                artPiecesEl.textContent = articleQty;
            }

            grandTotalQty += articleQty;
        });

        document.getElementById('summaryTotalArticles').textContent = totalArticles;
        document.getElementById('summaryTotalRows').textContent = totalRows;
        document.getElementById('summaryGrandTotalQty').textContent = grandTotalQty.toLocaleString();
    }

    container.addEventListener('input', function (e) {
        if (e.target.classList.contains('size-input') || e.target.classList.contains('rate-std-input') || e.target.classList.contains('rate-plus-input')) {
            recalculateAll();
        }
    });

    container.addEventListener('click', function (e) {
        const addColorBtn = e.target.closest('.add-color-row-btn');
        if (addColorBtn) {
            const artBlock = addColorBtn.closest('.article-block');
            const tbody = artBlock.querySelector('.rows-tbody');
            const artIdx = artBlock.dataset.articleIndex;
            const nextRowIdx = tbody.querySelectorAll('.item-row').length;

            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.dataset.rowIndex = nextRowIdx;
            newRow.innerHTML = `
                <td class="ps-3">
                    <input list="colorSuggestions" name="articles[${artIdx}][items][${nextRowIdx}][color]" class="form-control form-control-sm text-uppercase fw-semibold" placeholder="Color" value="BLACK">
                </td>
                <td>
                    <select name="articles[${artIdx}][items][${nextRowIdx}][cup]" class="form-select form-select-sm text-center fw-bold">
                        <option value="B" selected>B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_32]" class="form-control form-control-sm size-input input-std" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_34]" class="form-control form-control-sm size-input input-std" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_36]" class="form-control form-control-sm size-input input-std" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_38]" class="form-control form-control-sm size-input input-std" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_40]" class="form-control form-control-sm size-input input-std" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_42]" class="form-control form-control-sm size-input input-std" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_44]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_46]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_48]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                <td><input type="number" min="0" step="1" name="articles[${artIdx}][items][${nextRowIdx}][size_50]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                <td><input type="number" min="0" step="0.5" name="articles[${artIdx}][items][${nextRowIdx}][rate_std]" class="form-control form-control-sm text-center rate-std-input" placeholder="0"></td>
                <td><input type="number" min="0" step="0.5" name="articles[${artIdx}][items][${nextRowIdx}][rate_plus]" class="form-control form-control-sm text-center rate-plus-input" placeholder="0"></td>
                <td class="text-center">
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold px-2 py-1 row-total-badge">0</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link text-danger p-0 remove-row-btn" title="Delete Row">
                        <i class="bi bi-x-circle fs-6"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(newRow);
            recalculateAll();
            return;
        }

        const removeRowBtn = e.target.closest('.remove-row-btn');
        if (removeRowBtn) {
            const row = removeRowBtn.closest('.item-row');
            const tbody = row.closest('.rows-tbody');
            if (tbody.querySelectorAll('.item-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('.size-input').forEach(i => i.value = '0');
            }
            recalculateAll();
            return;
        }

        const removeArtBtn = e.target.closest('.remove-article-btn');
        if (removeArtBtn) {
            const artBlock = removeArtBtn.closest('.article-block');
            if (container.querySelectorAll('.article-block').length > 1) {
                artBlock.remove();
                recalculateAll();
            }
            return;
        }
    });

    addArticleBtn.addEventListener('click', function () {
        const nextArtIdx = container.querySelectorAll('.article-block').length;

        const newBlock = document.createElement('div');
        newBlock.className = 'article-block';
        newBlock.dataset.articleIndex = nextArtIdx;
        newBlock.innerHTML = `
            <div class="article-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 600px;">
                    <span class="badge bg-primary rounded-pill px-2.5 py-1.5 article-badge">Article #${nextArtIdx + 1}</span>
                    <div class="flex-grow-1">
                        <input list="articleSuggestions" name="articles[${nextArtIdx}][article_name]" class="form-control form-control-sm fw-bold text-dark article-name-input" placeholder="Article Name" required>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border fs-7 font-monospace">
                        Article Total: <span class="article-total-pieces fw-bold text-dark">0</span> pcs
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2 remove-article-btn" title="Remove Article">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table size-matrix-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 130px;" class="text-start ps-3">Color</th>
                            <th style="width: 70px;">Cup</th>
                            <th class="th-std">32</th>
                            <th class="th-std">34</th>
                            <th class="th-std">36</th>
                            <th class="th-std">38</th>
                            <th class="th-std">40</th>
                            <th class="th-std">42</th>
                            <th class="th-plus">44</th>
                            <th class="th-plus">46</th>
                            <th class="th-plus">48</th>
                            <th class="th-plus">50</th>
                            <th style="width: 75px;">Rate Std</th>
                            <th style="width: 75px;">Rate Plus</th>
                            <th style="width: 80px;">Total Pcs</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody class="rows-tbody">
                        <tr class="item-row" data-row-index="0">
                            <td class="ps-3">
                                <input list="colorSuggestions" name="articles[${nextArtIdx}][items][0][color]" class="form-control form-control-sm text-uppercase fw-semibold" placeholder="Color" value="SKIN">
                            </td>
                            <td>
                                <select name="articles[${nextArtIdx}][items][0][cup]" class="form-select form-select-sm text-center fw-bold">
                                    <option value="B" selected>B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_32]" class="form-control form-control-sm size-input input-std" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_34]" class="form-control form-control-sm size-input input-std" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_36]" class="form-control form-control-sm size-input input-std" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_38]" class="form-control form-control-sm size-input input-std" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_40]" class="form-control form-control-sm size-input input-std" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_42]" class="form-control form-control-sm size-input input-std" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_44]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_46]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_48]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                            <td><input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_50]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                            <td><input type="number" min="0" step="0.5" name="articles[${nextArtIdx}][items][0][rate_std]" class="form-control form-control-sm text-center rate-std-input" placeholder="0"></td>
                            <td><input type="number" min="0" step="0.5" name="articles[${nextArtIdx}][items][0][rate_plus]" class="form-control form-control-sm text-center rate-plus-input" placeholder="0"></td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold px-2 py-1 row-total-badge">0</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-link text-danger p-0 remove-row-btn" title="Delete Row">
                                <i class="bi bi-x-circle fs-6"></i>
                            </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-2 bg-light border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-primary btn-sm px-3 add-color-row-btn">
                    <i class="bi bi-plus-lg me-1"></i> Add Color / Cup Row
                </button>
                <span class="small text-secondary fst-italic">Standard Sizes: 32 to 42 | Plus Sizes: 44 to 50</span>
            </div>
        `;

        container.appendChild(newBlock);
        recalculateAll();
    });

    recalculateAll();
});
</script>
@endpush
