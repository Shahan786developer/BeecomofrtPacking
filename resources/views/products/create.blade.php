@extends('layouts.app')

@section('title', $activeType === 'China' ? 'Register China Product' : 'Register Bee Comfort Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Register {{ $activeType }} Product</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10 d-flex flex-column gap-4">

        <!-- Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    {{ $activeType === 'China' ? 'Register China Product' : 'Register Bee Comfort Product' }}
                </h1>
                <p class="text-secondary small mb-0">
                    @if($activeType === 'China')
                        Create a China inventory item with automated 7-digit SKU and pricing formula.
                    @else
                        Create a Bee Comfort item with manual SKU code and warehouse stock tracking.
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('products.index', ['parent_category' => $activeType]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>

        <!-- Form Category Switcher Tabs -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex flex-wrap gap-2">
                <a href="{{ route('products.create', ['type' => 'bee_comfort']) }}" 
                   class="btn {{ $activeType === 'Bee Comfort' ? 'btn-primary' : 'btn-outline-primary' }} fw-bold px-4 py-2 flex-fill flex-sm-grow-0">
                    <i class="bi bi-shield-check me-1"></i> Bee Comfort Product Registration
                </a>
                <a href="{{ route('products.create', ['type' => 'china']) }}" 
                   class="btn {{ $activeType === 'China' ? 'btn-danger' : 'btn-outline-danger' }} fw-bold px-4 py-2 flex-fill flex-sm-grow-0">
                    <i class="bi bi-box-seam me-1"></i> China Product Registration
                </a>
            </div>
        </div>

        <!-- Live Unified Product Title Card -->
        <div class="card border-0 shadow-sm {{ $activeType === 'China' ? 'bg-danger text-white' : 'bg-primary text-white' }}">
            <div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <span class="text-white-50 text-uppercase fw-bold fs-7 d-block mb-1">
                        Generated Unified Product Title ({{ $activeType }}):
                    </span>
                    <h3 id="live-product-title" class="h4 fw-bold text-white mb-0">
                        Comfort Fit (12 pieces) AA-01
                    </h3>
                </div>
                <span class="badge bg-white {{ $activeType === 'China' ? 'text-danger' : 'text-primary' }} fw-bold px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i> Live Combined Preview
                </span>
            </div>
        </div>

        <!-- Clean Product Registration Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('products.store') }}" id="product-form" class="d-flex flex-column gap-4">
                    @csrf
                    <input type="hidden" name="parent_category" value="{{ $activeType }}">

                    <!-- Section 1: Classification -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-tag {{ $activeType === 'China' ? 'text-danger' : 'text-primary' }} me-1"></i> 1. Product Classification
                        </h6>

                        <div class="row g-3">
                            <!-- Parent Category Badge (Locked to active tab) -->
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Catalog Type
                                </label>
                                <div class="form-control fw-bold {{ $activeType === 'China' ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-primary-subtle text-primary border-primary-subtle' }}">
                                    <i class="bi {{ $activeType === 'China' ? 'bi-box-seam' : 'bi-shield-check' }} me-1"></i>
                                    {{ $activeType }}
                                </div>
                            </div>

                            <!-- Bill No (Optional) -->
                            <div class="col-12 col-md-3">
                                <label for="bill_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Bill Number <span class="text-secondary fw-normal fs-8">(Optional)</span>
                                </label>
                                <input type="text" name="bill_no" id="bill_no" value="{{ old('bill_no') }}"
                                    placeholder="e.g. BILL-101 (Optional)" class="form-control font-monospace fw-semibold">
                            </div>

                            <!-- Category -->
                            <div class="col-12 col-md-3">
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
                                        <option value="{{ $category->id }}" 
                                            {{ old('category_id', $defaultCategory->id ?? '') == $category->id ? 'selected' : '' }} 
                                            data-name="{{ $category->name }}">
                                            {{ $category->name }} {{ $category->is_default ? '(Default)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dozens (12 default) -->
                            <div class="col-12 col-md-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="dozen_id" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                        Dozens Unit <span class="text-danger">*</span>
                                    </label>
                                    <a href="{{ route('dozens.index') }}" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                        + New
                                    </a>
                                </div>
                                <select name="dozen_id" id="dozen_id" required class="searchable-select form-select">
                                    <option value="">Select Dozens Unit</option>
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
                            <i class="bi bi-upc {{ $activeType === 'China' ? 'text-danger' : 'text-primary' }} me-1"></i> 2. Codes &amp; Initial Inventory
                        </h6>

                        <div class="row g-3">
                            <!-- Product Code (Auto hyphen: 2 letters, hyphen, 2 numbers) -->
                            <div class="col-12 col-md-4">
                                <label for="product_code" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Product Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="product_code" id="product_code" value="{{ old('product_code') }}" required
                                    placeholder="e.g. AA-01" maxlength="5" style="text-transform: uppercase;" class="form-control font-monospace fw-bold text-uppercase">
                                <div class="form-text small text-secondary">Auto-formatted: 2 letters, hyphen, 2 digits (e.g. AA-01)</div>
                            </div>

                            <!-- SKU Code -->
                            <div class="col-12 col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="sku_code" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                        {{ $activeType === 'China' ? 'SKU Code (7-Digits)' : 'Manual SKU Code' }} <span class="text-danger">*</span>
                                    </label>
                                    @if($activeType === 'China')
                                        <span class="badge bg-light text-danger border">Auto 7-Digits (Editable)</span>
                                    @else
                                        <span class="badge bg-light text-primary border">Manual SKU</span>
                                    @endif
                                </div>
                                <input type="text" name="sku_code" id="sku_code" 
                                    value="{{ old('sku_code', $activeType === 'China' ? $previewSku : '') }}" required
                                    placeholder="{{ $activeType === 'China' ? 'Unique 7-digit SKU' : 'Enter manual SKU code (e.g. BC-01 or custom)' }}" 
                                    class="form-control font-monospace fw-bold {{ $activeType === 'China' ? 'text-danger' : 'text-primary' }} fs-6">
                                <div class="form-text small text-secondary">
                                    @if($activeType === 'China')
                                        Auto-allocated unique 7 digits; can be edited if needed.
                                    @else
                                        Enter your manual SKU code for this Bee Comfort item.
                                    @endif
                                </div>
                            </div>

                            <!-- Initial Stock Quantity -->
                            <div class="col-12 col-md-4">
                                <label for="quantity" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Initial Stock Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required placeholder="e.g. 100" class="form-control fw-bold font-monospace">
                                <div class="form-text small text-secondary">Units available in warehouse at registration</div>
                            </div>
                        </div>
                    </div>

                    @if($activeType === 'China')
                    <!-- Section 3: Pricing Configuration (Only for China) -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-cash-coin text-danger me-1"></i> 3. Pricing Configuration (Rupees Rs.)
                        </h6>

                        <div class="row g-3">
                            <!-- Actual Price (Blank by default, Rs.) -->
                            <div class="col-12 col-md-4">
                                <label for="actual_price" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Actual Purchase Price (Rs.) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="actual_price" id="actual_price" value="{{ old('actual_price') }}" required
                                        placeholder="e.g. 650" class="form-control fw-bold text-dark font-monospace">
                                </div>
                            </div>

                            <!-- Pricing Rule -->
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

                            <!-- Selling Price (Calculated & Editable, Rs.) -->
                            <div class="col-12 col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="selling_price" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                        Calculated Selling Price (Rs.)
                                    </label>
                                    <span class="badge bg-light text-success border">Editable Price</span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price') }}"
                                        placeholder="0.00" class="form-control fw-black text-dark font-monospace fs-6">
                                </div>
                                <div class="form-text small text-secondary">Auto-calculated from pricing rule; can be customized freely.</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="pt-4 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index', ['parent_category' => $activeType]) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn {{ $activeType === 'China' ? 'btn-danger' : 'btn-primary' }} btn-lg fw-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Register {{ $activeType }} Product
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
        const sellingPriceInput = document.getElementById('selling_price');

        // Product code auto-masking: 2 letters, then auto-insert hyphen, then 2 numbers
        if (productCodeInput) {
            productCodeInput.addEventListener('input', function() {
                let raw = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (raw.length > 2) {
                    raw = raw.substring(0, 2) + '-' + raw.substring(2, 4);
                }
                this.value = raw;
                updateTitle();
            });
        }

        function updateTitle() {
            if (!liveTitle) return;

            let cat = 'Product';
            if (categorySelect) {
                if (categorySelect.tomselect) {
                    const opt = categorySelect.tomselect.options[categorySelect.tomselect.getValue()];
                    if (opt) cat = opt.text.replace(/\(Default\)/g, '').trim();
                } else if (categorySelect.selectedIndex >= 0 && categorySelect.value) {
                    cat = categorySelect.options[categorySelect.selectedIndex].text.replace(/\(Default\)/g, '').trim();
                }
            }

            let dozen = '12 pieces';
            if (dozenSelect) {
                if (dozenSelect.tomselect) {
                    const opt = dozenSelect.tomselect.options[dozenSelect.tomselect.getValue()];
                    if (opt) dozen = opt.text.replace(/\(Default\)/g, '').trim();
                } else if (dozenSelect.selectedIndex >= 0) {
                    dozen = dozenSelect.options[dozenSelect.selectedIndex].text.replace(/\(Default\)/g, '').trim();
                }
            }

            const code = (productCodeInput && productCodeInput.value.trim()) ? productCodeInput.value.trim() : 'AA-01';
            liveTitle.textContent = `${cat} (${dozen}) ${code}`;
        }

        function recalcSellingPrice() {
            if (!actualPriceInput || !pricingRuleSelect || !sellingPriceInput) return;

            const actual = parseFloat(actualPriceInput.value) || 0;
            let percentage = 0;

            let ruleId = pricingRuleSelect.tomselect ? pricingRuleSelect.tomselect.getValue() : pricingRuleSelect.value;
            if (ruleId) {
                const opt = pricingRuleSelect.querySelector(`option[value="${ruleId}"]`);
                if (opt) percentage = parseFloat(opt.getAttribute('data-percentage')) || 0;
            }

            if (actual <= 0) {
                if (!sellingPriceInput.value || sellingPriceInput.dataset.autoCalculated === 'true') {
                    sellingPriceInput.value = '';
                }
                return;
            }

            const raw = actual * (1 + (percentage / 100));
            const finalPrice = Math.ceil(raw / 10) * 10;
            sellingPriceInput.value = finalPrice.toFixed(2);
            sellingPriceInput.dataset.autoCalculated = 'true';
        }

        if (sellingPriceInput) {
            sellingPriceInput.addEventListener('input', function() {
                this.dataset.autoCalculated = 'false';
            });
        }

        if (actualPriceInput) {
            actualPriceInput.addEventListener('input', recalcSellingPrice);
        }

        setTimeout(() => {
            if (categorySelect && categorySelect.tomselect) categorySelect.tomselect.on('change', updateTitle);
            if (dozenSelect && dozenSelect.tomselect) dozenSelect.tomselect.on('change', updateTitle);
            if (pricingRuleSelect && pricingRuleSelect.tomselect) pricingRuleSelect.tomselect.on('change', recalcSellingPrice);

            updateTitle();
            if (actualPriceInput) {
                recalcSellingPrice();
            }
        }, 300);
    });
</script>
@endpush
