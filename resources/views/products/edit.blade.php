@extends('layouts.app')

@section('title', 'Edit ' . $product->title)

@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-amber-600">Products</a>
    <span>/</span>
    <span class="text-slate-800 font-semibold">Edit Product</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Product</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Modify product details, category, or pricing rule. SKU is permanently locked.</p>
        </div>
        <a href="{{ route('products.show', $product) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Product
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Row 1: Bill No, Category, Dozens -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="bill_no" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Bill Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="bill_no" id="bill_no" value="{{ old('bill_no', $product->bill_no) }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                </div>

                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Category <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required class="searchable-select w-full">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="dozen_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Dozens Unit <span class="text-rose-500">*</span>
                    </label>
                    <select name="dozen_id" id="dozen_id" required class="searchable-select w-full">
                        @foreach($dozens as $dozen)
                            <option value="{{ $dozen->id }}" {{ old('dozen_id', $product->dozen_id) == $dozen->id ? 'selected' : '' }}>
                                {{ $dozen->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Row 2: Product Code, 7-Digit Readonly SKU, Current Stock -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2 border-t border-slate-100">
                <div>
                    <label for="product_code" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Product Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="product_code" id="product_code" value="{{ old('product_code', $product->product_code) }}" required
                        style="text-transform: uppercase;"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono font-bold tracking-wider">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        SKU (Locked)
                    </label>
                    <input type="text" value="{{ $product->sku_code }}" readonly
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl bg-slate-100 text-slate-700 border border-slate-300 font-mono font-bold tracking-widest cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Current Warehouse Stock
                    </label>
                    <div class="px-3.5 py-2.5 text-sm rounded-xl bg-slate-100 border border-slate-200 font-extrabold text-slate-800">
                        {{ $product->stock_quantity }} units
                    </div>
                </div>
            </div>

            <!-- Row 3: Actual Price, Pricing Rule, Selling Price -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2 border-t border-slate-100">
                <div>
                    <label for="actual_price" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Actual Price ($) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" min="0" name="actual_price" id="actual_price" value="{{ old('actual_price', $product->actual_price) }}" required
                            class="w-full pl-8 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-400 font-bold">
                    </div>
                </div>

                <div>
                    <label for="pricing_rule_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pricing Rule <span class="text-rose-500">*</span>
                    </label>
                    <select name="pricing_rule_id" id="pricing_rule_id" required class="searchable-select w-full">
                        @foreach($pricingRules as $rule)
                            <option value="{{ $rule->id }}" {{ old('pricing_rule_id', $product->pricing_rule_id) == $rule->id ? 'selected' : '' }} data-percentage="{{ $rule->percentage }}">
                                {{ $rule->name }} (+{{ number_format($rule->percentage, 0) }}%)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Calculated Selling Price
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold">$</span>
                        <input type="text" id="selling_price_display" value="{{ number_format($product->selling_price, 2) }}" readonly
                            class="w-full pl-8 pr-3.5 py-2.5 text-sm rounded-xl bg-slate-100 text-slate-900 border border-slate-300 font-black cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('products.show', $product) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-xs transition-colors">
                    Update Product
                </button>
            </div>
        </form>
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
