<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print {{ $quantity }}x Barcode Labels (32mm x 22mm) — {{ $product->sku_code }}</title>
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        /* Arabic Transparent Font Definition */
        @font-face {
            font-family: 'Arabic Transparent';
            src: local('Arabic Transparent'), local('Arial');
            font-weight: normal;
            font-style: normal;
        }

        /* Thermal roll page configuration */
        @page {
            size: 32mm 22mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Arabic Transparent', Arial, sans-serif;
            color: #000000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen display toolbar */
        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            z-index: 9999;
            font-family: system-ui, sans-serif;
            font-size: 14px;
        }

        .btn-print {
            background: #f59e0b;
            color: #020617;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-print:hover {
            background: #fbbf24;
        }
        .btn-close {
            background: #334155;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin-right: 8px;
            font-size: 13px;
            text-decoration: none;
        }

        .stickers-wrapper {
            margin-top: 70px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 20px;
            justify-content: center;
        }

        /* Individual 32mm x 22mm Thermal Label Card */
        .thermal-sticker-page {
            width: 32mm;
            height: 22mm;
            background: #ffffff;
            color: #000000;
            padding: 1mm 1.2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            text-align: center;
            line-height: 1.15;
            border: 1px dashed #cbd5e1;
            page-break-after: always;
            page-break-inside: avoid;
            box-sizing: border-box;
        }

        .line-brand {
            font-size: 7.5pt;
            font-weight: 900;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            line-height: 1.1;
            margin: 0;
        }

        .line-desc {
            font-size: 6.8pt;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            margin-top: 0.4mm;
            line-height: 1.1;
        }

        .line-barcode {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0.5mm 0;
        }

        .line-barcode svg {
            max-width: 100%;
            height: 9.2mm;
            display: block;
        }

        .line-sku {
            font-size: 7.8pt;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            letter-spacing: 1.4px;
            margin-top: 0.3mm;
            line-height: 1;
        }

        /* PRINT MEDIA STYLING */
        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .stickers-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }
            .thermal-sticker-page {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                page-break-after: always;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Toolbar (Hidden during print) -->
    <div class="no-print-bar">
        <div>
            <strong>Bee Comfort — Thermal Sticker Print View</strong>
            <span style="color: #94a3b8; margin-left: 8px;">
                ({{ $quantity }} Identical 32mm × 22mm Labels • SKU: {{ $product->sku_code }})
            </span>
        </div>
        <div>
            <a href="javascript:window.close()" class="btn-close">Close</a>
            <button onclick="window.print()" class="btn-print">Print {{ $quantity }} Stickers</button>
        </div>
    </div>

    <!-- Stickers Container -->
    <div class="stickers-wrapper">
        @for($i = 0; $i < $quantity; $i++)
        <div class="thermal-sticker-page">
            <!-- Line 1: BEE COMFORT -->
            <div class="line-brand">{{ $config->brand_name }}</div>

            <!-- Line 2: Product Description (e.g. INDIA AA-01) -->
            <div class="line-desc">{{ $secondLine }}</div>

            <!-- Line 3: Code39 Barcode -->
            <div class="line-barcode">
                <svg class="barcode-item" data-sku="{{ $product->sku_code }}"></svg>
            </div>

            <!-- Line 4: Human-readable 7-digit SKU -->
            <div class="line-sku">{{ $product->sku_code }}</div>
        </div>
        @endfor
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render Code39 on all stickers
            document.querySelectorAll('.barcode-item').forEach(function(el) {
                const sku = el.getAttribute('data-sku');
                JsBarcode(el, sku, {
                    format: "CODE39",
                    lineColor: "#000000",
                    width: 1.15,
                    height: 38,
                    displayValue: false, // Line 4 renders human-readable SKU
                    margin: 0,
                    flat: true,
                });
            });

            // Auto-trigger print dialog after brief delay
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
