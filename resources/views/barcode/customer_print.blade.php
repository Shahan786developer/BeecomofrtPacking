<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print {{ $quantity }}x Customer Barcode (32mm x 22mm) — {{ $customer->formatted_code }}</title>
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
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
            font-family: Arial, Helvetica, sans-serif;
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

        /* Individual 32mm x 22mm Thermal Label Card (5 Rows) */
        .thermal-sticker-page {
            width: 32mm;
            height: 22mm;
            background: #ffffff;
            color: #000000;
            padding: 0.6mm 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            text-align: center;
            border: 1px dashed #cbd5e1;
            page-break-after: always;
            page-break-inside: avoid;
            box-sizing: border-box;
        }

        /* Row 1: Company / Brand Name */
        .row-brand {
            font-size: 5.5pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.1;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            max-width: 100%;
        }

        /* Row 2: Customer Name */
        .row-customer-name {
            font-size: 6pt;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            max-width: 100%;
            line-height: 1.1;
            margin: 0;
        }

        /* Row 3: City / Phone */
        .row-customer-details {
            font-size: 5.5pt;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            max-width: 100%;
            color: #1e293b;
        }

        /* Row 4: Barcode Lines */
        .row-barcode {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
        }

        .row-barcode svg {
            max-width: 100%;
            height: 7.8mm;
            display: block;
        }

        /* Row 5: Human-readable Customer Code */
        .row-code {
            font-size: 7pt;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 900;
            letter-spacing: 1.2px;
            line-height: 1;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
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
            <strong>Bee Comfort — Customer Barcode Print View (32mm &times; 22mm)</strong>
            <span style="color: #94a3b8; margin-left: 8px;">
                ({{ $quantity }} Labels • Customer: {{ $customer->name }} • Code: {{ $customer->formatted_code }})
            </span>
        </div>
        <div>
            <a href="javascript:window.close()" class="btn-close">Close</a>
            <button onclick="window.print()" class="btn-print">Print {{ $quantity }} Stickers</button>
        </div>
    </div>

    @php
        $details = array_filter([$customer->city, $customer->phone]);
        $detailsText = !empty($details) ? implode(' • ', $details) : ($customer->owner_name ?? 'CUSTOMER');
        $barcodeValue = $customer->formatted_code;
    @endphp

    <!-- Stickers Container -->
    <div class="stickers-wrapper">
        @for($i = 0; $i < $quantity; $i++)
        <div class="thermal-sticker-page">
            <!-- Row 1: Company / Brand Name -->
            <div class="row-brand">{{ $config->brand_name ?? 'BEE COMFORT' }}</div>

            <!-- Row 2: Customer Name -->
            <div class="row-customer-name">{{ $customer->name }}</div>

            <!-- Row 3: City / Phone -->
            <div class="row-customer-details">{{ $detailsText }}</div>

            <!-- Row 4: Barcode Lines (Code128) -->
            <div class="row-barcode">
                <svg class="barcode-item" data-code="{{ $barcodeValue }}"></svg>
            </div>

            <!-- Row 5: Customer Code -->
            <div class="row-code">{{ $customer->formatted_code }}</div>
        </div>
        @endfor
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render Code128 on all stickers
            document.querySelectorAll('.barcode-item').forEach(function(el) {
                const code = el.getAttribute('data-code');
                JsBarcode(el, code, {
                    format: "CODE128",
                    lineColor: "#000000",
                    width: 1.1,
                    height: 32,
                    displayValue: false,
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
