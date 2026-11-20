<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $receipt->voucher_no }} (52mm Thermal POS Roll) — {{ $company->name ?? 'Bee Comfort' }}</title>
    <style>
        /* 52mm Standard POS Thermal Receipt Roll */
        @page {
            size: 52mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            width: 52mm;
            max-width: 52mm;
            margin: 0 auto;
            padding: 3mm 2mm 5mm 2mm;
            background: #ffffff;
            color: #000000;
            font-family: 'Courier New', Courier, monospace, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.25;
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
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            z-index: 9999;
            font-family: system-ui, sans-serif;
            font-size: 13px;
        }

        .btn-print {
            background: #10b981;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-print:hover {
            background: #059669;
        }
        .btn-close {
            background: #334155;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            margin-right: 6px;
            font-size: 12px;
            text-decoration: none;
        }

        .screen-container {
            margin-top: 55px;
            display: flex;
            justify-content: center;
            padding: 10px;
        }

        /* The 52mm Slip Paper Sheet */
        .thermal-slip {
            width: 52mm;
            max-width: 52mm;
            background: #ffffff;
            padding: 2mm 1.5mm;
            box-sizing: border-box;
            border: 1px dashed #cbd5e1;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .fw-black { font-weight: 900; }
        .text-uppercase { text-transform: uppercase; }

        .brand-title {
            font-size: 12.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }

        .brand-sub {
            font-size: 8.5px;
            line-height: 1.15;
            margin-top: 1px;
        }

        .divider-dashed {
            border-top: 1px dashed #000000;
            margin: 2mm 0;
        }

        .divider-double {
            border-top: 1px double #000000;
            border-bottom: 1px double #000000;
            height: 2px;
            margin: 2mm 0;
        }

        .slip-title {
            font-size: 10.5px;
            font-weight: 900;
            padding: 1px 0;
            letter-spacing: 0.3px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin: 1mm 0;
        }

        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .meta-label {
            font-weight: bold;
            width: 42%;
        }

        .meta-val {
            font-weight: normal;
            width: 58%;
            text-align: right;
            word-break: break-word;
        }

        .amount-box {
            border: 1px solid #000000;
            padding: 2mm 1.5mm;
            margin: 2mm 0;
            text-align: center;
            background: #fafafa;
        }

        .amount-label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .amount-val {
            font-size: 14px;
            font-weight: 900;
            margin-top: 1px;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            font-size: 9.5px;
            font-weight: bold;
            padding: 1px 0;
        }

        .notes-box {
            font-size: 8px;
            line-height: 1.2;
            padding: 1mm 0;
            word-break: break-word;
        }

        .sig-section {
            margin-top: 4mm;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }

        .sig-box {
            width: 46%;
            text-align: center;
            border-top: 1px solid #000000;
            padding-top: 1mm;
        }

        .slip-footer {
            margin-top: 3mm;
            text-align: center;
            font-size: 7.5px;
            line-height: 1.2;
        }

        /* PRINT STYLING */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 1.5mm 1mm !important;
                width: 52mm !important;
                max-width: 52mm !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .screen-container {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }
            .thermal-slip {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Toolbar -->
    <div class="no-print-bar">
        <div>
            <strong>Bee Comfort — CRV 52mm Thermal POS Slip</strong>
            <span style="color: #94a3b8; margin-left: 8px;">
                (Voucher: {{ $receipt->voucher_no }} • Rs. {{ number_format($receipt->amount, 2) }})
            </span>
        </div>
        <div>
            <a href="javascript:window.close()" class="btn-close">Close</a>
            <button onclick="window.print()" class="btn-print">Print 52mm Slip</button>
        </div>
    </div>

    <div class="screen-container">
        <div class="thermal-slip">

            <!-- 1. Header & Brand -->
            <div class="text-center">
                <div class="brand-title">{{ $company->name ?? 'BEE COMFORT' }}</div>
                @if(!empty($company->tagline))
                    <div class="brand-sub">{{ $company->tagline }}</div>
                @endif
                @if(!empty($company->phone))
                    <div class="brand-sub">Tel: {{ $company->phone }}</div>
                @endif
                @if(!empty($company->address))
                    <div class="brand-sub">{{ $company->address }}</div>
                @endif
            </div>

            <div class="divider-double"></div>

            <!-- 2. Slip Title & Voucher Meta -->
            <div class="text-center">
                <div class="slip-title">CASH RECEIPT VOUCHER</div>
                <div style="font-size: 10px; font-weight: bold;"># {{ $receipt->voucher_no }}</div>
            </div>

            <div class="divider-dashed"></div>

            <!-- 3. Details Table -->
            <table class="meta-table">
                <tr>
                    <td class="meta-label">Date:</td>
                    <td class="meta-val">{{ $receipt->payment_date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Time:</td>
                    <td class="meta-val">{{ $receipt->created_at->format('h:i A') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Received By:</td>
                    <td class="meta-val">{{ $receipt->user->name ?? 'Admin' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Customer:</td>
                    <td class="meta-val fw-bold">{{ $receipt->customer->name }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Cust Code:</td>
                    <td class="meta-val">{{ $receipt->customer->formatted_code }}</td>
                </tr>
                @if($receipt->customer->phone)
                <tr>
                    <td class="meta-label">Phone:</td>
                    <td class="meta-val">{{ $receipt->customer->phone }}</td>
                </tr>
                @endif
                @if($receipt->customer->city)
                <tr>
                    <td class="meta-label">City:</td>
                    <td class="meta-val">{{ $receipt->customer->city }}</td>
                </tr>
                @endif
                <tr>
                    <td class="meta-label">Pay Method:</td>
                    <td class="meta-val fw-bold text-uppercase">
                        @if($receipt->payment_method === 'easypaisa')
                            EasyPaisa
                        @elseif($receipt->payment_method === 'jazzcash')
                            JazzCash
                        @else
                            {{ str_replace('_', ' ', $receipt->payment_method) }}
                        @endif
                    </td>
                </tr>
            </table>

            <!-- 4. Amount Received Highlight Box -->
            <div class="amount-box">
                <div class="amount-label">Amount Received</div>
                <div class="amount-val">Rs. {{ number_format($receipt->amount, 2) }}</div>
            </div>

            <!-- 5. Ledger Balance Info -->
            <div class="balance-row">
                <span>Customer Due:</span>
                <span>Rs. {{ number_format($receipt->customer->balance_due, 2) }}</span>
            </div>

            @if($receipt->notes)
            <div class="divider-dashed"></div>
            <div class="notes-box">
                <strong>Remarks:</strong> {{ $receipt->notes }}
            </div>
            @endif

            <div class="divider-dashed"></div>

            <!-- 6. Dual Signatures -->
            <div class="sig-section">
                <div class="sig-box">
                    Cashier / User
                </div>
                <div class="sig-box">
                    Customer Sig
                </div>
            </div>

            <!-- 7. Footer -->
            <div class="slip-footer">
                Thank you for your business!<br>
                *** System Generated POS Slip ***
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
