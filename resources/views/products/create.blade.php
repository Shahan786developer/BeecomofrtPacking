@extends('layouts.app')

@section('title', 'Register New Product')

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-amber-600">Products</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">Register Product</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Register New Product</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Create a new inventory item with automated 7-digit SKU and pricing.</p>
        </div>
        <a href="{{ route('products.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Products
        </a>
    </div>

    <!-- Live Unified Product Title Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between gap-4">
        <div class="space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Generated Product Title</span>
            <h3 id="live-product-title" class="text-lg sm:text-xl font-extrabold text-slate-900">
                Comfort Fit (12 pieces) AA-01
            </h3>
        </div>
        <span class="hidden sm:inline-flex px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600">
            Dynamic Unified Title
        </span>
    </div>

    <!-- Clean Product Form -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('products.store') }}" id="product-form" class="space-y-6">
            @csrf

            <!-- Section 1: Classification -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Product Classification
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- 1. Bill No -->
                    <div>
                        <label for="bill_no" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Bill Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="bill_no" id="bill_no" value="{{ old('bill_no', 'BILL-' . date('Y') . '-' . rand(100, 999)) }}" required
                            placeholder="e.g. BILL-2026-101"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono font-medium">
                    </div>

                    <!-- 2. Category -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="category_id" class="block text-xs font-bold text-slate-700">
                                Category <span class="text-rose-500">*</span>
                            </label>
                            <a href="{{ route('categories.index') }}" target="_blank" class="text-[11px] font-bold text-amber-600 hover:underline">
                                + New
                            </a>
                        </div>
                        <select name="category_id" id="category_id" required class="searchable-select w-full" placeholder="Select category...">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : ($loop->first ? 'selected' : '') }} data-name="{{ $category->name }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Dozens (12 default) -->
                    <div>
                        <label for="dozen_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Dozens Unit <span class="text-rose-500">*</span>
                        </label>
                        <select name="dozen_id" id="dozen_id" required class="searchable-select w-full" placeholder="Select pack unit...">
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
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Codes & Initial Inventory
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- 4. Product Code -->
                    <div>
                        <label for="product_code" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Product Code <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="product_code" id="product_code" value="{{ old('product_code', 'AA-01') }}" required
                            placeholder="e.g. AA-01"
                            style="text-transform: uppercase;"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono font-bold tracking-wider">
                        <span class="text-[11px] text-slate-400 mt-1 block">Uppercase code (e.g. AA-01)</span>
                    </div>

                    <!-- 5. SKU (Auto generated, 7 digits, read-only) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700">SKU Code</label>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">Auto Generated</span>
                        </div>
                        <input type="text" value="{{ $previewSku }}" readonly
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl bg-slate-100 border border-slate-300 font-mono font-bold tracking-widest text-slate-800 cursor-not-allowed">
                        <span class="text-[11px] text-slate-400 mt-1 block">7 numeric digits unique barcode ID</span>
                    </div>

                    <!-- 9. Initial Stock Quantity -->
                    <div>
                        <label for="quantity" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Initial Stock Quantity <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 100) }}" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-bold">
                        <span class="text-[11px] text-slate-400 mt-1 block">Units available at registration</span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Pricing -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Pricing
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- 6. Actual Price -->
                    <div>
                        <label for="actual_price" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Actual Price ($) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0" name="actual_price" id="actual_price" value="{{ old('actual_price', 650) }}" required
                                class="w-full pl-8 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-bold text-slate-900">
                        </div>
                    </div>

                    <!-- 7. Pricing Rule -->
                    <div>
                        <label for="pricing_rule_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pricing Rule <span class="text-rose-500">*</span>
                        </label>
                        <select name="pricing_rule_id" id="pricing_rule_id" required class="searchable-select w-full" placeholder="Select pricing rule...">
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
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Calculated Selling Price
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="text" id="selling_price_display" value="880.00" readonly
                                class="w-full pl-8 pr-3.5 py-2.5 text-sm rounded-xl bg-slate-100 border border-slate-300 font-black text-slate-900 cursor-not-allowed">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-xs transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Register Product
                </button>
            </div>

        </form>
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
                if (opt) dozenLabel = opt.text.replace(/\(Default\)/g, '').trim();
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

            // Formula: CEILING((Actual Price * (1 + Percentage / 100)) / 10) * 10
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
