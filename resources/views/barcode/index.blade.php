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
            <p class="text-secondary small mb-0">Generate Code39 barcode labels formatted for standard 32 mm &times; 22 mm thermal sticker rolls.</p>
        </div>
        <div>
            <a href="{{ route('barcode.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Printer Settings
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="row g-4 items-start">

        <!-- Controls (Col 7) -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">Print Thermal Stickers</h5>
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
                                        data-category="{{ $prod->category->name ?? '' }}"
                                        data-stock="{{ $prod->stock_quantity }}"
                                        data-price="{{ $prod->selling_price }}">
                                        {{ $prod->title }} — [SKU: {{ $prod->sku_code }}] (Stock: {{ $prod->stock_quantity }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-secondary mt-1">
                                Selecting a product auto-fetches Category &amp; Product Code for Row 2, and populates current available stock.
                            </div>
                        </div>

                        <!-- Automated Label Text Display (Auto-Fetched) -->
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Row 1 (Brand Name):</span>
                                    <div class="fw-black text-dark font-monospace fs-6 text-uppercase">
                                        {{ $config->brand_name ?? 'BEE COMFORT' }}
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Row 2 (Category + Code):</span>
                                    <div id="row2-display" class="fw-bold text-primary font-monospace fs-6 text-uppercase">
                                        —
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock and Print Quantity -->
                        <div class="row g-3 pt-2 border-top">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Available Warehouse Stock</label>
                                <input type="text" id="available_stock_display" value="0 units" readonly class="form-control fw-bold font-monospace bg-light">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="print_quantity" class="form-label small fw-bold text-secondary text-uppercase mb-1">
                                    Quantity to Print <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="quantity" id="print_quantity" value="1" min="1" max="1000" required class="form-control fw-bold font-monospace text-primary fs-5">
                                <div class="form-text small text-secondary">Pre-filled with available stock; change manually if required.</div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Standard 32 mm &times; 22 mm Thermal Roll</span>
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm px-4">
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
                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">
                        32 mm &times; 22 mm
                    </span>
                </div>

                <div class="card-body p-4 d-flex flex-column align-items-center">
                    <!-- Preview Card Area -->
                    <div class="p-4 bg-light rounded-3 border w-100 d-flex justify-content-center align-items-center" style="min-height: 250px;">
                        
                        <!-- 32mm x 22mm Container scaled up for clear viewing -->
                        <div id="thermal-sticker-box" class="thermal-label-container shadow-sm border bg-white d-flex flex-column align-items-center justify-content-between" 
                             style="width: 32mm; height: 22mm; padding: 1mm 1.2mm; transform: scale(2.4); transform-origin: center; box-sizing: border-box; overflow: hidden; text-align: center;">
                            
                            <!-- Header Info Block with clean proportional spacing -->
                            <div class="w-100 d-flex flex-column align-items-center" style="line-height: 1.15; gap: 0.8px;">
                                <!-- Line 1: Brand Name (BEE COMFORT) -->
                                <div id="label-brand" class="fw-black text-uppercase text-dark" style="font-size: 7.5pt; font-weight: 900; letter-spacing: 0.6px; margin: 0;">
                                    {{ $config->brand_name ?? 'BEE COMFORT' }}
                                </div>

                                <!-- Line 2: Category + Manual Product Code (e.g. SILK TOUCH AA-01) -->
                                <div id="label-desc" class="fw-bold text-uppercase text-dark" style="font-size: 6.8pt; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; margin: 0;">
                                    Category AA-01
                                </div>
                            </div>

                            <!-- Line 3: Code39 Barcode (Longer/Taller Barcode Lines) -->
                            <div class="w-100 d-flex justify-content-center align-items-center overflow-hidden" style="margin: 0.6px 0;">
                                <svg id="barcode-svg" style="max-width: 100%; height: 9mm; display: block;"></svg>
                            </div>

                            <!-- Line 4: Human-readable 7-digit SKU with clean distance -->
                            <div id="label-sku" class="fw-bold font-monospace text-dark" style="font-size: 7.5pt; letter-spacing: 1.4px; line-height: 1; margin: 0;">
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
                            <span>Sticker Dimensions:</span>
                            <strong class="text-dark">32 mm &times; 22 mm</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Row 1:</span>
                            <strong class="text-dark">{{ $config->brand_name ?? 'BEE COMFORT' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Row 2:</span>
                            <strong class="text-dark">Auto: [Category] [Code]</strong>
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
        const printQuantityInput = document.getElementById('print_quantity');
        const availableStockDisplay = document.getElementById('available_stock_display');
        const row2Display = document.getElementById('row2-display');

        const labelDesc = document.getElementById('label-desc');
        const labelSku = document.getElementById('label-sku');
        const barcodeSvg = document.getElementById('barcode-svg');

        function renderLabel() {
            let selectedProd = null;
            let prodId = productSelect.tomselect ? productSelect.tomselect.getValue() : productSelect.value;
            if (prodId) {
                selectedProd = productSelect.querySelector(`option[value="${prodId}"]`);
            }

            let sku = '1000001';
            let productCode = 'AA-01';
            let categoryName = 'Bee Comfort';
            let stock = 0;

            if (selectedProd) {
                sku = selectedProd.getAttribute('data-sku') || '1000001';
                productCode = selectedProd.getAttribute('data-code') || 'AA-01';
                categoryName = selectedProd.getAttribute('data-category') || '';
                stock = parseInt(selectedProd.getAttribute('data-stock')) || 0;

                availableStockDisplay.value = stock + ' units';
                printQuantityInput.value = Math.max(1, stock);
            }

            const secondLine = `${categoryName} ${productCode}`.trim().toUpperCase();
            row2Display.textContent = secondLine || '—';
            labelDesc.textContent = secondLine || 'CATEGORY AA-01';
            labelSku.textContent = sku;

            try {
                JsBarcode(barcodeSvg, sku, {
                    format: "CODE39",
                    lineColor: "#000000",
                    width: 1.15,
                    height: 38,
                    displayValue: false,
                    margin: 0,
                    flat: true,
                });
            } catch (e) {
                console.error("Barcode rendering error:", e);
            }
        }

        setTimeout(() => {
            if (productSelect.tomselect) {
                productSelect.tomselect.on('change', renderLabel);
            } else {
                productSelect.addEventListener('change', renderLabel);
            }

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
