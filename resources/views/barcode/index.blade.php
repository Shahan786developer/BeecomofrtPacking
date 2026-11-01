@extends('layouts.app')

@section('title', 'Thermal Barcode Generator')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Barcode Studio</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Thermal Barcode Generator</h1>
            <p class="text-secondary small mb-0">Generate Code39 barcode labels formatted for 32 mm &times; 22 mm thermal sticker rolls.</p>
        </div>
        <div>
            <a href="{{ route('barcode.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Setup & Origins
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="row g-4 items-start">

        <!-- Controls (Col 7) -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Label Generation Details</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('barcode.print') }}" target="_blank" id="barcode-form" class="d-flex flex-column gap-4">
                        @csrf

                        <!-- Product Selection -->
                        <div>
                            <label for="product_select" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                Select Product <span class="text-danger">*</span>
                            </label>
                            <select name="product_id" id="product_select" required class="searchable-select form-select">
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
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="description_id" class="form-label small fw-bold text-secondary text-uppercase mb-0">
                                    Origin / Description <span class="text-danger">*</span>
                                </label>
                                <a href="{{ route('barcode.settings') }}" class="text-primary text-decoration-none small fw-bold">
                                    + Manage Origins
                                </a>
                            </div>
                            <select name="description_id" id="description_id" required class="searchable-select form-select">
                                @foreach($descriptions as $desc)
                                    <option value="{{ $desc->id }}" 
                                        {{ $desc->is_default ? 'selected' : '' }}
                                        data-name="{{ $desc->name }}">
                                        {{ $desc->name }} {{ $desc->is_default ? '(Default)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-secondary mt-1">
                                Second line preview: <strong id="preview-second-line-text" class="text-dark font-monospace">INDIA AA-01</strong>
                            </div>
                        </div>

                        <!-- Stock and Print Quantity -->
                        <div class="row g-3 pt-2 border-top">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Available Stock</label>
                                <input type="text" id="available_stock_display" value="120 units" readonly class="form-control fw-bold font-monospace bg-light">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="print_quantity" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Stickers to Print <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="quantity" id="print_quantity" value="120" min="1" max="1000" required class="form-control fw-bold font-monospace">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Standard 32 mm &times; 22 mm Thermal Roll</span>
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                <i class="bi bi-printer me-1"></i> Print Stickers
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Live Preview (Col 5) -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Label Live Preview</h5>
                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                        32 mm &times; 22 mm
                    </span>
                </div>

                <div class="card-body p-4 d-flex flex-column align-items-center">
                    <!-- Preview Card Area -->
                    <div class="p-4 bg-light rounded-3 border w-100 d-flex justify-content-center align-items-center" style="min-height: 220px;">
                        
                        <div id="thermal-sticker-box" class="thermal-label-container shadow-sm border bg-white" style="transform: scale(2.2); transform-origin: center;">
                            <!-- Line 1: Brand -->
                            <div id="label-brand" class="fw-black text-uppercase text-dark" style="font-size: 8px; letter-spacing: 0.5px;">
                                {{ $config->brand_name }}
                            </div>

                            <!-- Line 2: Origin + Code -->
                            <div id="label-desc" class="fw-bold text-uppercase text-dark" style="font-size: 7.5px; white-space: nowrap; overflow: hidden; margin-top: -2px;">
                                INDIA AA-01
                            </div>

                            <!-- Line 3: Code39 Barcode -->
                            <div class="w-100 d-flex justify-content-center align-items-center my-auto overflow-hidden">
                                <svg id="barcode-svg" style="max-width: 100%; max-height: 8mm;"></svg>
                            </div>

                            <!-- Line 4: Human-readable 7-digit SKU -->
                            <div id="label-sku" class="fw-bold font-monospace text-dark" style="font-size: 8px; letter-spacing: 1.5px; margin-top: -2px;">
                                1000001
                            </div>
                        </div>

                    </div>

                    <!-- Specs summary -->
                    <div class="w-100 mt-4 pt-3 border-top small text-secondary d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between">
                            <span>Barcode Standard:</span>
                            <strong class="text-dark">Code39 1D</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Dimensions:</span>
                            <strong class="text-dark">32 mm &times; 22 mm</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Typography:</span>
                            <strong class="text-dark">Arabic Transparent / Arial</strong>
                        </div>
                    </div>
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
