@extends('layouts.app')

@section('title', 'Register New Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Register Product</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10 d-flex flex-column gap-4">

        <!-- Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Register New Product</h1>
                <p class="text-secondary small mb-0">Create a new inventory item with automated 7-digit SKU and pricing.</p>
            </div>
            <div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>

        <!-- Live Unified Product Title Card -->
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <span class="text-white-50 text-uppercase fw-bold fs-7 d-block mb-1">Generated Unified Product Title:</span>
                    <h3 id="live-product-title" class="h4 fw-bold text-white mb-0">
                        Comfort Fit (12 pieces) AA-01
                    </h3>
                </div>
                <span class="badge bg-white text-primary fw-bold px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i> Live Combined Preview
                </span>
            </div>
        </div>

        <!-- Clean Product Registration Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('products.store') }}" id="product-form" class="d-flex flex-column gap-4">
                    @csrf

                    <!-- Section 1: Classification -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-tag text-primary me-1"></i> 1. Product Classification
                        </h6>

                        <div class="row g-3">
                            <!-- 1. Bill No -->
                            <div class="col-12 col-md-4">
                                <label for="bill_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Bill Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bill_no" id="bill_no" value="{{ old('bill_no', 'BILL-' . date('Y') . '-' . rand(100, 999)) }}" required
                                    placeholder="e.g. BILL-2026-101" class="form-control font-monospace fw-semibold">
                            </div>

                            <!-- 2. Category -->
                            <div class="col-12 col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="category_id" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <a href="{{ route('categories.index') }}" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                        + New
                                    </a>
                                </div>
                                <select name="category_id" id="category_id" required class="searchable-select form-select">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : ($loop->first ? 'selected' : '') }} data-name="{{ $category->name }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Dozens (12 default) -->
                            <div class="col-12 col-md-4">
                                <label for="dozen_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Dozens Unit <span class="text-danger">*</span>
                                </label>
                                <select name="dozen_id" id="dozen_id" required class="searchable-select form-select">
                                    @foreach($dozens as $dozen)
                                        <option value="{{ $dozen->id }}" 
                                            {{ old('dozen_id', $defaultDozen->id ?? null) == $dozen->id ? 'selected' : '' }}
                                            data-label="{{ $dozen->label }}">
                                            {{ $dozen->label }} {{ $dozen->is_default ? '(Default)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Code, SKU, and Stock -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-upc text-primary me-1"></i> 2. Codes & Initial Inventory
                        </h6>

                        <div class="row g-3">
                            <!-- 4. Product Code -->
                            <div class="col-12 col-md-4">
                                <label for="product_code" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Product Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="product_code" id="product_code" value="{{ old('product_code', 'AA-01') }}" required
                                    placeholder="e.g. AA-01" style="text-transform: uppercase;" class="form-control font-monospace fw-bold text-uppercase">
                                <div class="form-text small text-secondary">Uppercase alphanumeric code (e.g. AA-01)</div>
                            </div>

                            <!-- 5. SKU (Auto generated, 7 digits, read-only) -->
                            <div class="col-12 col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-secondary text-uppercase mb-0">SKU Code</label>
                                    <span class="badge bg-light text-secondary border">Auto Generated</span>
                                </div>
                                <input type="text" value="{{ $previewSku }}" readonly
                                    class="form-control font-monospace fw-bold bg-light text-primary fs-6 cursor-not-allowed">
                                <div class="form-text small text-secondary">Unique 7-digit numeric barcode ID</div>
                            </div>

                            <!-- 9. Initial Stock Quantity -->
                            <div class="col-12 col-md-4">
                                <label for="quantity" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Initial Stock Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 100) }}" required class="form-control fw-bold font-monospace">
                                <div class="form-text small text-secondary">Units available in warehouse at registration</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Pricing -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-cash-coin text-primary me-1"></i> 3. Pricing Configuration
                        </h6>

                        <div class="row g-3">
                            <!-- 6. Actual Price -->
                            <div class="col-12 col-md-4">
                                <label for="actual_price" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Actual Purchase Price ($) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">$</span>
                                    <input type="number" step="0.01" min="0" name="actual_price" id="actual_price" value="{{ old('actual_price', 650) }}" required
                                        class="form-control fw-bold text-dark font-monospace">
                                </div>
                            </div>

                            <!-- 7. Pricing Rule -->
                            <div class="col-12 col-md-4">
                                <label for="pricing_rule_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Pricing Rule <span class="text-danger">*</span>
                                </label>
                                <select name="pricing_rule_id" id="pricing_rule_id" required class="searchable-select form-select">
                                    @foreach($pricingRules as $rule)
                                        <option value="{{ $rule->id }}" 
                                            {{ old('pricing_rule_id', $defaultPricingRule->id ?? null) == $rule->id ? 'selected' : '' }}
                                            data-percentage="{{ $rule->percentage }}">
                                            {{ $rule->name }} (+{{ number_format($rule->percentage, 0) }}%) {{ $rule->is_default ? '★' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 8. Selling Price (Auto Calculated, Read-only) -->
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Calculated Selling Price
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">$</span>
                                    <input type="text" id="selling_price_display" value="880.00" readonly
                                        class="form-control fw-black text-dark font-monospace fs-6 bg-light cursor-not-allowed">
                                </div>
                                <div class="form-text small text-secondary">Computed automatically from purchase price and markup rule</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-4 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Register Product
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('category_id');
        const dozenSelect = document.getElementById('dozen_id');
        const productCodeInput = document.getElementById('product_code');
        const liveTitle = document.getElementById('live-product-title');

        const actualPriceInput = document.getElementById('actual_price');
        const pricingRuleSelect = document.getElementById('pricing_rule_id');
        const sellingPriceDisplay = document.getElementById('selling_price_display');

        productCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            updateTitle();
        });

        function updateTitle() {
            let cat = 'Comfort Fit';
            if (categorySelect.tomselect) {
                const opt = categorySelect.tomselect.options[categorySelect.tomselect.getValue()];
                if (opt) cat = opt.text;
            } else if (categorySelect.selectedIndex >= 0) {
                cat = categorySelect.options[categorySelect.selectedIndex].text;
            }

            let dozen = '12 pieces';
            if (dozenSelect.tomselect) {
                const opt = dozenSelect.tomselect.options[dozenSelect.tomselect.getValue()];
                if (opt) dozen = opt.text.replace(/\(Default\)/g, '').trim();
            } else if (dozenSelect.selectedIndex >= 0) {
                dozen = dozenSelect.options[dozenSelect.selectedIndex].text.replace(/\(Default\)/g, '').trim();
            }

            const code = productCodeInput.value.trim() || 'AA-01';
            liveTitle.textContent = `${cat} (${dozen}) ${code}`;
        }

        function recalcSellingPrice() {
            const actual = parseFloat(actualPriceInput.value) || 0;
            let percentage = 35;

            let ruleId = pricingRuleSelect.tomselect ? pricingRuleSelect.tomselect.getValue() : pricingRuleSelect.value;
            if (ruleId) {
                const opt = pricingRuleSelect.querySelector(`option[value="${ruleId}"]`);
                if (opt) percentage = parseFloat(opt.getAttribute('data-percentage')) || 0;
            }

            if (actual <= 0) {
                sellingPriceDisplay.value = '0.00';
                return;
            }

            const raw = actual * (1 + (percentage / 100));
            const finalPrice = Math.ceil(raw / 10) * 10;
            sellingPriceDisplay.value = finalPrice.toFixed(2);
        }

        actualPriceInput.addEventListener('input', recalcSellingPrice);

        setTimeout(() => {
            if (categorySelect.tomselect) categorySelect.tomselect.on('change', updateTitle);
            if (dozenSelect.tomselect) dozenSelect.tomselect.on('change', updateTitle);
            if (pricingRuleSelect.tomselect) pricingRuleSelect.tomselect.on('change', recalcSellingPrice);

            updateTitle();
            recalcSellingPrice();
        }, 300);
    });
</script>
@endpush
