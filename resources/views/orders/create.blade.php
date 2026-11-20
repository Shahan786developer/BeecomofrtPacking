@extends('layouts.app')

@section('title', 'Create New Order — Hashmi Brothers')
@section('body_class', 'sidebar-collapsed')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none text-secondary">Sale Orders</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create New Order</li>
@endsection

@push('styles')
<style>
    /* Vibrant Top Header Banner */
    .order-header-banner {
        background: #0060df;
        border-radius: 8px;
        padding: 12px 20px;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 96, 223, 0.2);
    }

    /* Top Order Details Card */
    .order-top-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px 24px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    /* Vibrant Section Banner */
    .order-section-banner {
        background: #0060df;
        color: #ffffff;
        border-radius: 8px;
        padding: 10px 18px;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(0, 96, 223, 0.15);
    }

    /* Article Card with Blue Border */
    .order-article-box {
        background: #ffffff;
        border: 2px solid #0060df;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 96, 223, 0.06);
    }

    /* Matrix Row Card with Green Left Accent */
    .size-matrix-row-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #10b981;
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 14px;
    }

    /* Size Input Styling */
    .size-input-box {
        text-align: center;
    }

    .size-input-label {
        font-size: 0.76rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
    }

    .size-input {
        width: 100%;
        text-align: center;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.35rem 0.2rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        height: 38px;
    }

    .size-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    /* Plus Size Inputs (Warm Yellow/Cream) */
    .size-input.input-plus {
        background-color: #fef9c3 !important;
        border-color: #fde047 !important;
        color: #854d0e !important;
    }

    /* Article Total Bar */
    .article-total-bar {
        background: #cffafe;
        border: 1px solid #a5f3fc;
        border-radius: 6px;
        padding: 10px 18px;
    }

    /* Grand Total Card (Purple/Indigo) */
    .grand-total-card {
        background: #6366f1;
        border-radius: 12px;
        padding: 26px 20px;
        color: #ffffff;
        box-shadow: 0 4px 16px rgba(99, 102, 241, 0.25);
    }

    .grand-total-title {
        font-size: 1.05rem;
        font-weight: 600;
        opacity: 0.95;
        letter-spacing: 0.4px;
    }

    .grand-total-number {
        font-size: 3.6rem;
        font-weight: 900;
        line-height: 1.1;
        margin: 6px 0;
        font-family: 'JetBrains Mono', monospace;
        color: #ffffff;
    }

    .grand-total-subtitle {
        font-size: 0.88rem;
        opacity: 0.85;
    }

    /* Sleek TomSelect Dropdown Customization */
    .ts-control {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 0.45rem 0.75rem !important;
        font-size: 0.9rem !important;
        background-color: #ffffff !important;
        min-height: 38px !important;
    }

    .ts-dropdown {
        border-radius: 6px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid #e2e8f0 !important;
        z-index: 1050 !important;
    }

    .ts-dropdown .active {
        background-color: #0060df !important;
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<form action="{{ route('orders.store') }}" method="POST" id="orderBookingForm">
    @csrf

    <div class="d-flex flex-column gap-3 mb-5">

        <!-- 1. TOP HEADER BANNER -->
        <div class="order-header-banner d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 fw-bold fs-5">
                <i class="bi bi-cart3 fs-4"></i>
                <span>Create New Order - Hashmi Brothers</span>
            </div>
            <div>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-light text-dark fw-semibold px-3 py-1 shadow-sm rounded">
                    ← Back to Orders
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

        <!-- 2. TOP ORDER DETAILS CONFIGURATION CARD -->
        <div class="order-top-card">
            <div class="row g-3">
                <!-- Customer Selection -->
                <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label required fw-bold small text-secondary mb-0">
                            Customer <span class="text-danger">*</span>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-customer-scanner" data-target="customerId" title="Scan Customer Barcode with Camera">
                            <i class="bi bi-camera me-1"></i> Scan Barcode
                        </button>
                    </div>
                    <select name="customer_id" id="customerId" class="form-select searchable-select @error('customer_id') is-invalid @enderror" required placeholder="-- Select Customer --">
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-code="{{ $c->formatted_code }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->search_dropdown_label }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Order Date -->
                <div class="col-12 col-md-6">
                    <label class="form-label required fw-bold small text-secondary">
                        Order Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                    @error('order_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Order Status -->
                <div class="col-12 col-md-6">
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

                <!-- Order / Voucher No -->
                <div class="col-12 col-md-6">
                    <label class="form-label required fw-bold small text-secondary">Order / Voucher No</label>
                    <input type="text" name="order_no" class="form-control font-monospace fw-bold @error('order_no') is-invalid @enderror" value="{{ old('order_no', $nextOrderNo) }}" required>
                    @error('order_no')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Special Booking Instructions -->
                <div class="col-12">
                    <label class="form-label fw-bold small text-secondary">Special Booking Instructions / Remarks</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Deliver before next Monday, pack in 1-dozen boxes..." value="{{ old('notes') }}">
                </div>
            </div>
        </div>

        <!-- 3. SECTION BANNER: ORDER ARTICLES -->
        <div class="order-section-banner d-flex align-items-center gap-2">
            <i class="bi bi-box-seam fs-5"></i>
            <span class="fw-bold fs-6">Order Articles</span>
        </div>

        <!-- 4. ARTICLES CONTAINER -->
        <div id="articlesContainer">
            <!-- Article Block #1 -->
            <div class="order-article-box article-block" data-article-index="0">
                <!-- Article Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary fs-5"></i>
                        <span class="fw-bold text-primary fs-5 article-badge">Article #1</span>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm px-3 py-1 fw-bold remove-article-btn d-none">
                        <i class="bi bi-trash-fill me-1"></i> Remove Article
                    </button>
                </div>

                <!-- Select Article Dropdown -->
                <div class="mb-3">
                    <label class="form-label required fw-bold small text-secondary mb-1">
                        Select Article <span class="text-danger">*</span>
                    </label>
                    <select name="articles[0][article_name]" class="form-select article-select searchable-select" placeholder="Select an article" required>
                        <option value="">Select an article</option>
                        @foreach($availableArticles as $art)
                            <option value="{{ $art }}" {{ old('articles.0.article_name', 'COMFORT FIT 32-42') == $art ? 'selected' : '' }}>
                                {{ $art }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Article Details Sub-header -->
                <div class="mb-2">
                    <label class="fw-bold small text-secondary mb-0">
                        Article Details (Color, Cup & Sizes) <span class="text-danger">*</span>
                    </label>
                </div>

                <!-- Rows Container for this Article -->
                <div class="article-rows-list">
                    <!-- Row 0 -->
                    <div class="size-matrix-row-card item-row" data-row-index="0">
                        <!-- Top Line: Color, Cup, and Remove Row button -->
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label fw-bold small text-secondary mb-1">Color <span class="text-danger">*</span></label>
                                <select name="articles[0][items][0][color]" class="form-select form-select-sm fw-semibold color-select">
                                    <option value="">-- Select --</option>
                                    @foreach($availableColors as $col)
                                        <option value="{{ $col }}" {{ strtolower($col) === 'skin' ? 'selected' : '' }}>{{ $col }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label fw-bold small text-secondary mb-1">Cup <span class="text-danger">*</span></label>
                                <select name="articles[0][items][0][cup]" class="form-select form-select-sm text-center fw-bold cup-select">
                                    @foreach($cupOptions as $cup)
                                        <option value="{{ $cup }}" {{ $cup === 'B' ? 'selected' : '' }}>{{ $cup }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-5 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1.5 remove-row-btn fw-semibold" title="Remove Row">
                                    <i class="bi bi-x-circle me-1"></i> Remove Row
                                </button>
                            </div>
                        </div>

                        <!-- Sub-header: Sizes note and Row Total Badge -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-dark">Sizes (32-42: Standard Rate | 44-50: Plus Size Rate)</span>
                            <span class="badge bg-success font-monospace fw-bold px-3 py-1.5 row-total-badge fs-7">Total: 0 pcs</span>
                        </div>

                        <!-- 10 Sizes Horizontal Grid -->
                        <div class="row g-2 align-items-end mt-1">
                            <div class="col size-input-box">
                                <div class="size-input-label">32</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_32]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">34</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_34]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">36</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_36]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">38</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_38]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">40</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_40]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">42</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_42]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>

                            <!-- Plus Sizes (44 - 50) with Light Amber Background -->
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">44</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_44]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">46</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_46]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">48</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_48]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">50</div>
                                <input type="number" min="0" step="1" name="articles[0][items][0][size_50]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>

                            <!-- Standard & Plus Rate Hidden Inputs -->
                            <input type="hidden" name="articles[0][items][0][rate_std]" class="rate-std-input" value="0">
                            <input type="hidden" name="articles[0][items][0][rate_plus]" class="rate-plus-input" value="0">
                        </div>
                    </div>
                </div>

                <!-- Centered Teal + Add Row Button -->
                <div class="text-center my-3">
                    <button type="button" class="btn text-white px-4 py-1.5 fw-bold add-color-row-btn" style="background-color: #0d9488; border: none; border-radius: 6px;">
                        <i class="bi bi-plus me-1"></i> Add Row
                    </button>
                </div>

                <!-- Article Total Bottom Bar -->
                <div class="article-total-bar d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        <i class="bi bi-info-circle-fill text-info me-1"></i> Article Total:
                    </span>
                    <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill font-monospace article-total-pieces">
                        0 pcs
                    </span>
                </div>
            </div>
        </div>

        <!-- 5. GREEN + ADD ARTICLE BUTTON -->
        <div>
            <button type="button" id="addNewArticleBtn" class="btn btn-success fw-bold px-4 py-2 d-inline-flex align-items-center gap-2 rounded shadow-sm" style="background-color: #198754; border: none;">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Add Article</span>
            </button>
        </div>

        <!-- 6. PURPLE GRAND TOTAL CARD -->
        <div class="grand-total-card text-center my-4">
            <div class="grand-total-title">Grand Total Quantity</div>
            <div class="grand-total-number" id="summaryGrandTotalQty">0</div>
            <div class="grand-total-subtitle">Total pieces across all items</div>
        </div>

        <!-- 7. SAVE / CANCEL ACTION BUTTONS -->
        <div class="d-flex justify-content-end align-items-center gap-2 mt-2 mb-5">
            <a href="{{ route('orders.index') }}" class="btn btn-light border px-4 py-2 fw-semibold">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-5 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span>Save & Book Order</span>
            </button>
        </div>

    </div>
</form>

@include('components.customer-barcode-scanner')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('orderBookingForm');
    const container = document.getElementById('articlesContainer');
    const addArticleBtn = document.getElementById('addNewArticleBtn');

    // Default arrays from backend
    const defaultColors = @json($availableColors);
    const cupOptions = @json($cupOptions);
    const availableArticles = @json($availableArticles);

    // Safe TomSelect Initializer
    function safeInitTomSelect(selectEl, placeholderText = "Select an article") {
        if (!selectEl) return null;
        if (selectEl.tomselect) return selectEl.tomselect;
        if (typeof TomSelect === 'undefined') return null;

        try {
            return new TomSelect(selectEl, {
                create: true,
                sortField: { field: "text", direction: "asc" },
                placeholder: placeholderText,
                allowEmptyOption: true,
                maxOptions: 500,
            });
        } catch (e) {
            console.warn('TomSelect init warning:', e);
            return null;
        }
    }

    // Initialize customer dropdown
    const customerSelect = document.getElementById('customerId');
    if (customerSelect) {
        safeInitTomSelect(customerSelect, "-- Select Customer --");
    }

    // Initialize article selects
    document.querySelectorAll('.article-select').forEach(function(el) {
        safeInitTomSelect(el, "Select an article");
    });

    // Recalculate totals
    function recalculateAll() {
        let grandTotalQty = 0;
        const articleBlocks = container.querySelectorAll('.article-block');

        articleBlocks.forEach((artBlock, artIdx) => {
            const badge = artBlock.querySelector('.article-badge');
            if (badge) badge.textContent = `Article #${artIdx + 1}`;

            const removeArtBtn = artBlock.querySelector('.remove-article-btn');
            if (removeArtBtn) {
                if (articleBlocks.length > 1) {
                    removeArtBtn.classList.remove('d-none');
                } else {
                    removeArtBtn.classList.add('d-none');
                }
            }

            let articleQty = 0;
            const rows = artBlock.querySelectorAll('.item-row');

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

            const artTotalPieces = artBlock.querySelector('.article-total-pieces');
            if (artTotalPieces) {
                artTotalPieces.textContent = `${articleQty} pcs`;
            }

            grandTotalQty += articleQty;
        });

        const grandTotalDisplay = document.getElementById('summaryGrandTotalQty');
        if (grandTotalDisplay) {
            grandTotalDisplay.textContent = grandTotalQty.toLocaleString();
        }
    }

    // Input event for real-time recalculation
    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('size-input')) {
            recalculateAll();
        }
    });

    // Helper to generate a new row element
    function createRowElement(artIdx, rowIdx, initialColor = 'Skin', initialCup = 'B') {
        const newRow = document.createElement('div');
        newRow.className = 'size-matrix-row-card item-row';
        newRow.dataset.rowIndex = rowIdx;

        let colorOptionsHtml = '<option value="">-- Select --</option>';
        let colorFound = false;
        defaultColors.forEach(col => {
            const isSelected = col.toLowerCase() === initialColor.toLowerCase();
            if (isSelected) colorFound = true;
            colorOptionsHtml += `<option value="${col}" ${isSelected ? 'selected' : ''}>${col}</option>`;
        });
        if (!colorFound && initialColor && initialColor.trim() !== '') {
            colorOptionsHtml += `<option value="${initialColor}" selected>${initialColor}</option>`;
        }

        let cupOptionsHtml = '';
        cupOptions.forEach(cup => {
            cupOptionsHtml += `<option value="${cup}" ${cup === initialCup ? 'selected' : ''}>${cup}</option>`;
        });

        newRow.innerHTML = `
            <div class="row g-2 align-items-end mb-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label fw-bold small text-secondary mb-1">Color <span class="text-danger">*</span></label>
                    <select name="articles[${artIdx}][items][${rowIdx}][color]" class="form-select form-select-sm fw-semibold color-select">
                        ${colorOptionsHtml}
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-bold small text-secondary mb-1">Cup <span class="text-danger">*</span></label>
                    <select name="articles[${artIdx}][items][${rowIdx}][cup]" class="form-select form-select-sm text-center fw-bold cup-select">
                        ${cupOptionsHtml}
                    </select>
                </div>
                <div class="col-12 col-md-5 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1.5 remove-row-btn fw-semibold" title="Remove Row">
                        <i class="bi bi-x-circle me-1"></i> Remove Row
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold text-dark">Sizes (32-42: Standard Rate | 44-50: Plus Size Rate)</span>
                <span class="badge bg-success font-monospace fw-bold px-3 py-1.5 row-total-badge fs-7">Total: 0 pcs</span>
            </div>

            <div class="row g-2 align-items-end mt-1">
                <div class="col size-input-box">
                    <div class="size-input-label">32</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_32]" class="form-control form-control-sm size-input input-std" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label">34</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_34]" class="form-control form-control-sm size-input input-std" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label">36</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_36]" class="form-control form-control-sm size-input input-std" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label">38</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_38]" class="form-control form-control-sm size-input input-std" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label">40</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_40]" class="form-control form-control-sm size-input input-std" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label">42</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_42]" class="form-control form-control-sm size-input input-std" value="0">
                </div>

                <div class="col size-input-box">
                    <div class="size-input-label text-warning-emphasis">44</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_44]" class="form-control form-control-sm size-input input-plus" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label text-warning-emphasis">46</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_46]" class="form-control form-control-sm size-input input-plus" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label text-warning-emphasis">48</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_48]" class="form-control form-control-sm size-input input-plus" value="0">
                </div>
                <div class="col size-input-box">
                    <div class="size-input-label text-warning-emphasis">50</div>
                    <input type="number" min="0" step="1" name="articles[${artIdx}][items][${rowIdx}][size_50]" class="form-control form-control-sm size-input input-plus" value="0">
                </div>

                <input type="hidden" name="articles[${artIdx}][items][${rowIdx}][rate_std]" class="rate-std-input" value="0">
                <input type="hidden" name="articles[${artIdx}][items][${rowIdx}][rate_plus]" class="rate-plus-input" value="0">
            </div>
        `;

        return newRow;
    }

    // Unified click delegation for all buttons
    document.addEventListener('click', function (e) {
        // 1. Add Row Button (+ Add Row)
        const addColorBtn = e.target.closest('.add-color-row-btn');
        if (addColorBtn) {
            e.preventDefault();
            const artBlock = addColorBtn.closest('.article-block');
            const rowsList = artBlock.querySelector('.article-rows-list');
            const artIdx = artBlock.dataset.articleIndex;
            const nextRowIdx = rowsList.querySelectorAll('.item-row').length;

            const newRow = createRowElement(artIdx, nextRowIdx, 'Skin', 'B');
            rowsList.appendChild(newRow);
            recalculateAll();
            return;
        }

        // 2. Remove Row Button
        const removeRowBtn = e.target.closest('.remove-row-btn');
        if (removeRowBtn) {
            e.preventDefault();
            const row = removeRowBtn.closest('.item-row');
            const rowsList = row.closest('.article-rows-list');
            if (rowsList.querySelectorAll('.item-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('.size-input').forEach(i => i.value = '0');
            }
            recalculateAll();
            return;
        }

        // 4. Remove Article Block Button
        const removeArtBtn = e.target.closest('.remove-article-btn');
        if (removeArtBtn) {
            e.preventDefault();
            const artBlock = removeArtBtn.closest('.article-block');
            if (container.querySelectorAll('.article-block').length > 1) {
                artBlock.remove();
                recalculateAll();
            }
            return;
        }

        // 5. Add Another Article Button (+ Add Article)
        const addArtBtnClick = e.target.closest('#addNewArticleBtn');
        if (addArtBtnClick) {
            e.preventDefault();
            const nextArtIdx = container.querySelectorAll('.article-block').length;

            let articleOptionsHtml = '<option value="">Select an article</option>';
            availableArticles.forEach(art => {
                articleOptionsHtml += `<option value="${art}">${art}</option>`;
            });

            let colorOptionsHtml = '<option value="">-- Select --</option>';
            defaultColors.forEach(col => {
                const isSelected = col.toLowerCase() === 'skin';
                colorOptionsHtml += `<option value="${col}" ${isSelected ? 'selected' : ''}>${col}</option>`;
            });

            let cupOptionsHtml = '';
            cupOptions.forEach(cup => {
                cupOptionsHtml += `<option value="${cup}" ${cup === 'B' ? 'selected' : ''}>${cup}</option>`;
            });

            const newBlock = document.createElement('div');
            newBlock.className = 'order-article-box article-block';
            newBlock.dataset.articleIndex = nextArtIdx;
            newBlock.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary fs-5"></i>
                        <span class="fw-bold text-primary fs-5 article-badge">Article #${nextArtIdx + 1}</span>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm px-3 py-1 fw-bold remove-article-btn">
                        <i class="bi bi-trash-fill me-1"></i> Remove Article
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label required fw-bold small text-secondary mb-1">
                        Select Article <span class="text-danger">*</span>
                    </label>
                    <select name="articles[${nextArtIdx}][article_name]" class="form-select article-select searchable-select" placeholder="Select an article" required>
                        ${articleOptionsHtml}
                    </select>
                </div>

                <div class="mb-2">
                    <label class="fw-bold small text-secondary mb-0">
                        Article Details (Color, Cup & Sizes) <span class="text-danger">*</span>
                    </label>
                </div>

                <div class="article-rows-list">
                    <div class="size-matrix-row-card item-row" data-row-index="0">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label fw-bold small text-secondary mb-1">Color <span class="text-danger">*</span></label>
                                <select name="articles[${nextArtIdx}][items][0][color]" class="form-select form-select-sm fw-semibold color-select">
                                    ${colorOptionsHtml}
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label fw-bold small text-secondary mb-1">Cup <span class="text-danger">*</span></label>
                                <select name="articles[${nextArtIdx}][items][0][cup]" class="form-select form-select-sm text-center fw-bold cup-select">
                                    ${cupOptionsHtml}
                                </select>
                            </div>
                            <div class="col-12 col-md-5 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1.5 remove-row-btn fw-semibold" title="Remove Row">
                                    <i class="bi bi-x-circle me-1"></i> Remove Row
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-dark">Sizes (32-42: Standard Rate | 44-50: Plus Size Rate)</span>
                            <span class="badge bg-success font-monospace fw-bold px-3 py-1.5 row-total-badge fs-7">Total: 0 pcs</span>
                        </div>

                        <div class="row g-2 align-items-end mt-1">
                            <div class="col size-input-box">
                                <div class="size-input-label">32</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_32]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">34</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_34]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">36</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_36]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">38</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_38]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">40</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_40]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label">42</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_42]" class="form-control form-control-sm size-input input-std" value="0">
                            </div>

                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">44</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_44]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">46</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_46]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">48</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_48]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>
                            <div class="col size-input-box">
                                <div class="size-input-label text-warning-emphasis">50</div>
                                <input type="number" min="0" step="1" name="articles[${nextArtIdx}][items][0][size_50]" class="form-control form-control-sm size-input input-plus" value="0">
                            </div>

                            <input type="hidden" name="articles[${nextArtIdx}][items][0][rate_std]" class="rate-std-input" value="0">
                            <input type="hidden" name="articles[${nextArtIdx}][items][0][rate_plus]" class="rate-plus-input" value="0">
                        </div>
                    </div>
                </div>

                <div class="text-center my-3">
                    <button type="button" class="btn text-white px-4 py-1.5 fw-bold add-color-row-btn" style="background-color: #0d9488; border: none; border-radius: 6px;">
                        <i class="bi bi-plus me-1"></i> Add Row
                    </button>
                </div>

                <div class="article-total-bar d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        <i class="bi bi-info-circle-fill text-info me-1"></i> Article Total:
                    </span>
                    <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill font-monospace article-total-pieces">
                        0 pcs
                    </span>
                </div>
            `;

            container.appendChild(newBlock);
            safeInitTomSelect(newBlock.querySelector('.article-select'), "Select an article");
            recalculateAll();
            return;
        }
    });


    // Run initial calculation
    recalculateAll();
});
</script>
@endpush
