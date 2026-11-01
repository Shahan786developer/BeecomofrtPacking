@extends('layouts.app')

@section('title', 'Thermal Barcode Generator')

@section('breadcrumb')
    <span class="text-slate-800 font-semibold">Barcode Studio</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Thermal Barcode Generator</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Generate Code39 barcode labels formatted for 32 mm × 22 mm thermal sticker rolls.</p>
        </div>
        <a href="{{ route('barcode.settings') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-gear text-slate-400"></i> Setup & Origins
        </a>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Controls (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-6">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">
                Label Generation Details
            </h2>

            <form method="POST" action="{{ route('barcode.print') }}" target="_blank" id="barcode-form" class="space-y-5">
                @csrf

                <!-- Product Selection -->
                <div>
                    <label for="product_select" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Select Product <span class="text-rose-500">*</span>
                    </label>
                    <select name="product_id" id="product_select" required class="searchable-select w-full" placeholder="Search product title, code, or SKU...">
                        <option value="">Choose a product...</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}"
                                {{ (isset($selectedProduct) && $selectedProduct->id == $prod->id) ? 'selected' : '' }}
                                data-title="{{ $prod->title }}"
                                data-sku="{{ $prod->sku_code }}"
                                data-code="{{ $prod->product_code }}"
                                data-stock="{{ $prod->stock_quantity }}"
                                data-price="{{ $prod->selling_price }}">
                                {{ $prod->title }} — [SKU: {{ $prod->sku_code }}] (Stock: {{ $prod->stock_quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Second Line Description (Combined with Product Code) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="description_id" class="block text-xs font-bold text-slate-700">
                            Origin / Description <span class="text-rose-500">*</span>
                        </label>
                        <a href="{{ route('barcode.settings') }}" class="text-[11px] font-bold text-amber-600 hover:underline">
                            + Manage Origins
                        </a>
                    </div>
                    <select name="description_id" id="description_id" required class="searchable-select w-full" placeholder="Select origin...">
                        @foreach($descriptions as $desc)
                            <option value="{{ $desc->id }}" 
                                {{ $desc->is_default ? 'selected' : '' }}
                                data-name="{{ $desc->name }}">
                                {{ $desc->name }} {{ $desc->is_default ? '(Default)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-500 mt-1 block">
                        Preview on 2nd line: <strong id="preview-second-line-text" class="text-slate-900 font-mono">INDIA AA-01</strong>
                    </span>
                </div>

                <!-- Stock and Print Quantity -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Available Stock</label>
                        <input type="text" id="available_stock_display" value="120 units" readonly
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl bg-slate-100 border border-slate-200 font-bold text-slate-800 cursor-not-allowed">
                    </div>

                    <div>
                        <label for="print_quantity" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Number of Stickers to Print <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quantity" id="print_quantity" value="120" min="1" max="1000" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 font-bold">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Standard 32 mm × 22 mm Thermal Roll</span>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow-xs transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-print"></i> Print Stickers
                    </button>
                </div>

            </form>
        </div>

        <!-- Live Preview (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col items-center">
            <div class="w-full flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Label Live Preview
                </h3>
                <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    32 mm × 22 mm
                </span>
            </div>

            <!-- Preview Card -->
            <div class="p-8 bg-slate-50 rounded-2xl border border-slate-200/80 w-full flex items-center justify-center min-h-[180px]">
                
                <div id="thermal-sticker-box" class="thermal-label-container shadow-md border border-slate-300 bg-white" style="transform: scale(2.2); transform-origin: center;">
                    <!-- Line 1: BEE COMFORT -->
                    <div id="label-brand" class="font-extrabold text-[8px] tracking-wider uppercase text-black">
                        {{ $config->brand_name }}
                    </div>

                    <!-- Line 2: Product Description (e.g. INDIA AA-01) -->
                    <div id="label-desc" class="font-bold text-[7.5px] uppercase text-black -mt-0.5 tracking-tight whitespace-nowrap overflow-hidden">
                        INDIA AA-01
                    </div>

                    <!-- Line 3: Code39 Barcode -->
                    <div class="w-full flex items-center justify-center my-auto overflow-hidden">
                        <svg id="barcode-svg" class="max-w-full max-h-[8mm]"></svg>
                    </div>

                    <!-- Line 4: Human-readable 7-digit SKU -->
                    <div id="label-sku" class="font-bold text-[8px] font-mono tracking-widest text-black -mt-0.5">
                        1000001
                    </div>
                </div>

            </div>

            <!-- Specs summary -->
            <div class="w-full mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between">
                    <span>Barcode Format:</span>
                    <strong class="text-slate-800">Code39 1D</strong>
                </div>
                <div class="flex justify-between">
                    <span>Dimensions:</span>
                    <strong class="text-slate-800">32 mm × 22 mm</strong>
                </div>
                <div class="flex justify-between">
                    <span>Font Family:</span>
                    <strong class="text-slate-800">Arabic Transparent (Size 8)</strong>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productSelect = document.getElementById('product_select');
        const descriptionSelect = document.getElementById('description_id');
        const printQuantityInput = document.getElementById('print_quantity');
        const availableStockDisplay = document.getElementById('available_stock_display');

        const labelDesc = document.getElementById('label-desc');
        const labelSku = document.getElementById('label-sku');
        const previewSecondLineText = document.getElementById('preview-second-line-text');
        const barcodeSvg = document.getElementById('barcode-svg');

        function renderLabel() {
            let selectedProd = null;
            let prodId = productSelect.tomselect ? productSelect.tomselect.getValue() : productSelect.value;
            if (prodId) {
                selectedProd = productSelect.querySelector(`option[value="${prodId}"]`);
            }

            let descName = 'INDIA';
            let descId = descriptionSelect.tomselect ? descriptionSelect.tomselect.getValue() : descriptionSelect.value;
            if (descId) {
                const descOpt = descriptionSelect.querySelector(`option[value="${descId}"]`);
                if (descOpt) descName = descOpt.getAttribute('data-name') || descOpt.text.replace(/\(Default\)/g, '').trim();
            }

            let sku = '1000001';
            let productCode = 'AA-01';
            let stock = 0;

            if (selectedProd) {
                sku = selectedProd.getAttribute('data-sku') || '1000001';
                productCode = selectedProd.getAttribute('data-code') || 'AA-01';
                stock = parseInt(selectedProd.getAttribute('data-stock')) || 0;

                availableStockDisplay.value = stock + ' units';
                printQuantityInput.value = Math.max(1, stock);
            }

            const secondLine = `${descName} ${productCode}`.trim();
            labelDesc.textContent = secondLine;
            previewSecondLineText.textContent = secondLine;
            labelSku.textContent = sku;

            try {
                JsBarcode(barcodeSvg, sku, {
                    format: "CODE39",
                    lineColor: "#000000",
                    width: 1.1,
                    height: 24,
                    displayValue: false,
                    margin: 0,
                    flat: true,
                });
            } catch (e) {
                console.error("Barcode error:", e);
            }
        }

        setTimeout(() => {
            if (productSelect.tomselect) productSelect.tomselect.on('change', renderLabel);
            if (descriptionSelect.tomselect) descriptionSelect.tomselect.on('change', renderLabel);

            if (productSelect.value || (productSelect.tomselect && productSelect.tomselect.getValue())) {
                renderLabel();
            } else {
                const firstOpt = productSelect.querySelector('option[value]:not([value=""])');
                if (firstOpt && productSelect.tomselect) {
                    productSelect.tomselect.setValue(firstOpt.value);
                } else {
                    renderLabel();
                }
            }
        }, 300);
    });
</script>
@endpush
