@extends('layouts.app')

@section('title', 'Edit ' . $product->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.show', $product) }}" class="text-decoration-none">{{ $product->title }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Product</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10 d-flex flex-column gap-4">

        <!-- Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Edit Product</h1>
                <p class="text-secondary small mb-0">Modify product details, category, or pricing rule. SKU is permanently locked.</p>
            </div>
            <div>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Product
                </a>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('products.update', $product) }}" class="d-flex flex-column gap-4">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Classification -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-tag text-primary me-1"></i> 1. Product Classification
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label for="parent_category" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Parent Category <span class="text-danger">*</span>
                                </label>
                                <select name="parent_category" id="parent_category" required class="form-select fw-bold border-primary-subtle text-primary">
                                    @foreach($parentCategories as $pCat)
                                        <option value="{{ $pCat }}" {{ old('parent_category', $product->parent_category ?? 'Bee Comfort') === $pCat ? 'selected' : '' }}>
                                            {{ $pCat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="bill_no" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Bill Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bill_no" id="bill_no" value="{{ old('bill_no', $product->bill_no) }}" required
                                    class="form-control font-monospace fw-semibold">
                            </div>

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
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

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
                                    @foreach($dozens as $dozen)
                                        <option value="{{ $dozen->id }}" {{ old('dozen_id', $product->dozen_id) == $dozen->id ? 'selected' : '' }}>
                                            {{ $dozen->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Codes & Current Stock -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-upc text-primary me-1"></i> 2. Codes & Inventory Status
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="product_code" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Product Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="product_code" id="product_code" value="{{ old('product_code', $product->product_code) }}" required
                                    style="text-transform: uppercase;" class="form-control font-monospace fw-bold text-uppercase">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    SKU Code (Locked)
                                </label>
                                <input type="text" value="{{ $product->sku_code }}" readonly
                                    class="form-control font-monospace fw-bold bg-light text-primary fs-6 cursor-not-allowed">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Current Warehouse Stock
                                </label>
                                <div class="form-control bg-light fw-bold font-monospace text-dark">
                                    {{ $product->stock_quantity }} units
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Pricing -->
                    <div>
                        <h6 class="fw-bold text-secondary text-uppercase border-bottom pb-2 mb-3">
                            <i class="bi bi-cash-coin text-primary me-1"></i> 3. Pricing Configuration (Rupees Rs.)
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="actual_price" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Actual Purchase Price (Rs.) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">Rs.</span>
                                    <input type="number" step="0.01" min="0" name="actual_price" id="actual_price" value="{{ old('actual_price', $product->actual_price) }}" required
                                        class="form-control fw-bold font-monospace text-dark">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="pricing_rule_id" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Pricing Rule <span class="text-danger">*</span>
                                </label>
                                <select name="pricing_rule_id" id="pricing_rule_id" required class="searchable-select form-select">
                                    @foreach($pricingRules as $rule)
                                        <option value="{{ $rule->id }}" {{ old('pricing_rule_id', $product->pricing_rule_id) == $rule->id ? 'selected' : '' }} data-percentage="{{ $rule->percentage }}">
                                            {{ $rule->name }} (+{{ number_format($rule->percentage, 0) }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Calculated Selling Price (Rs.)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">Rs.</span>
                                    <input type="text" id="selling_price_display" value="{{ number_format($product->selling_price, 2) }}" readonly
                                        class="form-control fw-black text-dark font-monospace fs-6 bg-light cursor-not-allowed">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Update Product
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
        const actualInput = document.getElementById('actual_price');
        const ruleSelect = document.getElementById('pricing_rule_id');
        const spDisplay = document.getElementById('selling_price_display');

        function recalc() {
            const actual = parseFloat(actualInput.value) || 0;
            let pct = 35;

            let ruleId = ruleSelect.tomselect ? ruleSelect.tomselect.getValue() : ruleSelect.value;
            if (ruleId) {
                const opt = ruleSelect.querySelector(`option[value="${ruleId}"]`);
                if (opt) pct = parseFloat(opt.getAttribute('data-percentage')) || 0;
            }

            const raw = actual * (1 + (pct / 100));
            const finalP = Math.ceil(raw / 10) * 10;
            spDisplay.value = finalP.toFixed(2);
        }

        actualInput.addEventListener('input', recalc);
        setTimeout(() => {
            if (ruleSelect.tomselect) ruleSelect.tomselect.on('change', recalc);
            recalc();
        }, 300);
    });
</script>
@endpush
