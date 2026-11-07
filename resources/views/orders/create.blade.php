@extends('layouts.app')

@section('title', 'Create New Order — Hashmi Brothers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none text-secondary">Sale Orders</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create New Order</li>
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
        border: 1px solid #cbd5e1;
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
        border: 1px solid #e2e8f0;
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

    /* Fixed Bottom Action Bar */
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
<form action="{{ route('orders.store') }}" method="POST" id="orderBookingForm">
    @csrf

    <div class="d-flex flex-column gap-3 mb-5">

        <!-- HEADER TITLE -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 fw-bold text-dark mb-0">Create New Order - Hashmi Brothers</h1>
                </div>
                <p class="text-secondary small mb-0 mt-1">Multi-article booking with Cup, Color, and 10-Size matrix quantity breakdown.</p>
            </div>
            <div>
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
            <div class="row g-3">
                <!-- Customer Selection -->
                <div class="col-12 col-md-4">
                    <label class="form-label required fw-bold small text-secondary">Customer</label>
                    <select name="customer_id" id="customerId" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->formatted_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Order No -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label required fw-bold small text-secondary">Order / Voucher No</label>
                    <input type="text" name="order_no" class="form-control font-monospace fw-bold @error('order_no') is-invalid @enderror" value="{{ old('order_no', $nextOrderNo) }}" required>
                    @error('order_no')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Order Date -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label required fw-bold small text-secondary">Order Date</label>
                    <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                    @error('order_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Order Status -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label required fw-bold small text-secondary">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="Pending" {{ old('status', 'Pending') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Completed" {{ old('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Rejected" {{ old('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Notes / Special Instructions -->
                <div class="col-12">
                    <label class="form-label fw-bold small text-secondary">Special Booking Instructions / Remarks</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Deliver before next Monday, pack in 1-dozen boxes..." value="{{ old('notes') }}">
                </div>
            </div>
        </div>

        <!-- SECTION TITLE: ORDER ARTICLES -->
        <div class="d-flex align-items-center justify-content-between mt-2">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-box-seam text-primary me-2"></i>Order Articles
            </h5>
            <span class="small text-secondary">Add multiple articles and configure size breakdowns</span>
        </div>

        <!-- ARTICLES CONTAINER -->
        <div id="articlesContainer">
            <!-- Article Block #1 -->
            <div class="article-block" data-article-index="0">
                <div class="article-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 650px;">
                        <span class="badge bg-primary rounded-pill px-2.5 py-1.5 article-badge">Article #1</span>
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <label class="small fw-bold text-secondary text-nowrap mb-0">Select Article</label>
                            <input list="articleSuggestions" name="articles[0][article_name]" class="form-control form-control-sm fw-bold text-dark article-name-input" placeholder="-- Select Article --" value="{{ old('articles.0.article_name', 'COMFORT FIT 32-42') }}" required>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border fs-7 font-monospace px-2.5 py-1.5">
                            Article Total: <span class="article-total-pieces fw-bold text-primary">0</span> pcs
                        </span>
                        <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2 remove-article-btn d-none" title="Remove Article">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Article Details Sub-header -->
                <div class="px-3 py-1.5 bg-light-subtle border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-secondary">Article Details (Color, Cup & Sizes)</span>
                    <span class="small text-muted font-monospace">Sizes (32-42: Standard Rate | 44-50: Plus Size Rate)</span>
                </div>

                <div class="table-responsive">
                    <table class="table size-matrix-table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 140px;" class="text-start ps-3">Color</th>
                                <th style="width: 65px;">Cup</th>
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
                                <th style="width: 75px;" title="Standard Rate (32-42)">Rate Std</th>
                                <th style="width: 75px;" title="Plus Size Rate (44-50)">Rate Plus</th>
                                <th style="width: 90px;">Total</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody class="rows-tbody">
                            <!-- Row 0 -->
                            <tr class="item-row" data-row-index="0">
                                <td class="ps-3">
                                    <input list="colorSuggestions" name="articles[0][items][0][color]" class="form-control form-control-sm text-uppercase fw-semibold" placeholder="-- Select --" value="SKIN">
                                </td>
                                <td>
                                    <select name="articles[0][items][0][cup]" class="form-select form-select-sm text-center fw-bold">
                                        <option value="B" selected>B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </td>
                                <!-- Standard Sizes (32 - 42) -->
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_32]" class="form-control form-control-sm size-input input-std" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_34]" class="form-control form-control-sm size-input input-std" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_36]" class="form-control form-control-sm size-input input-std" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_38]" class="form-control form-control-sm size-input input-std" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_40]" class="form-control form-control-sm size-input input-std" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_42]" class="form-control form-control-sm size-input input-std" value="0"></td>

                                <!-- Plus Sizes (44 - 50) -->
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_44]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_46]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_48]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>
                                <td><input type="number" min="0" step="1" name="articles[0][items][0][size_50]" class="form-control form-control-sm size-input input-plus bg-warning-subtle" value="0"></td>

                                <!-- Rates -->
                                <td><input type="number" min="0" step="0.5" name="articles[0][items][0][rate_std]" class="form-control form-control-sm text-center rate-std-input" placeholder="0"></td>
                                <td><input type="number" min="0" step="0.5" name="articles[0][items][0][rate_plus]" class="form-control form-control-sm text-center rate-plus-input" placeholder="0"></td>

                                <!-- Row Total -->
                                <td class="text-center">
                                    <span class="badge bg-success font-monospace fw-bold px-2 py-1 row-total-badge">Total: 0 pcs</span>
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
                    <span class="small fw-bold text-dark font-monospace">
                        Article Total: <span class="article-total-pieces text-primary">0</span> pcs
                    </span>
                </div>
            </div>
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
                    <div class="h5 fw-bold text-dark mb-0 font-monospace" id="summaryTotalArticles">1</div>
                </div>
                <div>
                    <div class="text-secondary small fw-bold text-uppercase">Total Color Lines</div>
                    <div class="h5 fw-bold text-dark mb-0 font-monospace" id="summaryTotalRows">1</div>
                </div>
                <div class="border-start ps-4">
                    <div class="text-secondary small fw-bold text-uppercase">Grand Total Quantity</div>
                    <div class="h3 fw-black text-success mb-0 font-monospace">
                        <span id="summaryGrandTotalQty">0</span> <span class="fs-6 fw-normal text-secondary">Total pieces across all items</span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('orders.index') }}" class="btn btn-light border px-3">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Save & Book Order</span>
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
                    rowBadge.textContent = `Total: ${rowQty} pcs`;
                }

                articleQty += rowQty;
            });

            artBlock.querySelectorAll('.article-total-pieces').forEach(el => {
                el.textContent = articleQty;
            });

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
                    <input list="colorSuggestions" name="articles[${artIdx}][items][${nextRowIdx}][color]" class="form-control form-control-sm text-uppercase fw-semibold" placeholder="-- Select --" value="BLACK">
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
                    <span class="badge bg-success font-monospace fw-bold px-2 py-1 row-total-badge">Total: 0 pcs</span>
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
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 650px;">
                    <span class="badge bg-primary rounded-pill px-2.5 py-1.5 article-badge">Article #${nextArtIdx + 1}</span>
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <label class="small fw-bold text-secondary text-nowrap mb-0">Select Article</label>
                        <input list="articleSuggestions" name="articles[${nextArtIdx}][article_name]" class="form-control form-control-sm fw-bold text-dark article-name-input" placeholder="-- Select Article --" required>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border fs-7 font-monospace px-2.5 py-1.5">
                        Article Total: <span class="article-total-pieces fw-bold text-primary">0</span> pcs
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2 remove-article-btn" title="Remove Article">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <div class="px-3 py-1.5 bg-light-subtle border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold small text-secondary">Article Details (Color, Cup & Sizes)</span>
                <span class="small text-muted font-monospace">Sizes (32-42: Standard Rate | 44-50: Plus Size Rate)</span>
            </div>

            <div class="table-responsive">
                <table class="table size-matrix-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 140px;" class="text-start ps-3">Color</th>
                            <th style="width: 65px;">Cup</th>
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
                            <th style="width: 75px;" title="Standard Rate (32-42)">Rate Std</th>
                            <th style="width: 75px;" title="Plus Size Rate (44-50)">Rate Plus</th>
                            <th style="width: 90px;">Total</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody class="rows-tbody">
                        <tr class="item-row" data-row-index="0">
                            <td class="ps-3">
                                <input list="colorSuggestions" name="articles[${nextArtIdx}][items][0][color]" class="form-control form-control-sm text-uppercase fw-semibold" placeholder="-- Select --" value="SKIN">
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
                                <span class="badge bg-success font-monospace fw-bold px-2 py-1 row-total-badge">Total: 0 pcs</span>
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
                <span class="small fw-bold text-dark font-monospace">
                    Article Total: <span class="article-total-pieces text-primary">0</span> pcs
                </span>
            </div>
        `;

        container.appendChild(newBlock);
        recalculateAll();
    });

    recalculateAll();
});
</script>
@endpush
