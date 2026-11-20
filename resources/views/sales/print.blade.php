<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $sale->invoice_no }} — {{ $sale->customer->name }} (Bee Comfort)</title>
    
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
            line-height: 1.4;
            font-size: 13px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Fixed Top Action Bar for screen view */
        .screen-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 54px;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-family: inherit;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }

        .btn-close-view {
            background-color: #334155;
            color: #f1f5f9;
            margin-right: 8px;
        }
        .btn-close-view:hover {
            background-color: #475569;
        }

        /* Printable Paper Container */
        .sheet-container {
            width: 210mm;
            min-height: 297mm;
            margin: 70px auto 40px auto;
            background: #ffffff;
            padding: 14mm 14mm 16mm 14mm;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Document Header */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }

        .brand-logo {
            max-height: 55px;
            max-width: 190px;
            object-fit: contain;
            margin-bottom: 6px;
            display: block;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .brand-tagline {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .brand-contact {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.4;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .bill-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .badge-bee-comfort {
            background: #ffffff;
            color: #000000;
            border: 1px solid #000000;
        }

        .badge-china {
            background: #ffffff;
            color: #000000;
            border: 1px solid #000000;
        }

        .meta-details {
            margin-top: 8px;
            font-size: 12px;
            color: #000000;
            line-height: 1.6;
        }

        .meta-details strong {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000000;
        }

        /* Customer Card */
        .info-card {
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }

        .info-card-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
            margin-bottom: 6px;
            border-bottom: 1px solid #000000;
            padding-bottom: 4px;
        }

        .customer-company {
            font-size: 16px;
            font-weight: 700;
            color: #000000;
        }

        .info-row {
            font-size: 12px;
            color: #000000;
            margin-top: 3px;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .info-label {
            font-size: 11px;
            color: #333333;
            min-width: 60px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .items-table th {
            background: #ffffff;
            color: #000000;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #000000;
        }

        .items-table th.text-center, .items-table td.text-center {
            text-align: center;
        }

        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 8px 10px;
            border: 1px solid #000000;
            font-size: 12px;
            color: #000000;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .item-title {
            font-weight: 700;
            color: #000000;
            font-size: 12.5px;
        }

        .item-subtext {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .item-price, .item-total {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 12.5px;
        }

        /* Totals Block */
        .summary-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .notes-box {
            flex: 1;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 80px;
        }

        .notes-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .notes-content {
            font-size: 11.5px;
            color: #334155;
            font-style: italic;
        }

        .totals-table-box {
            width: 250px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 8px;
            font-size: 12px;
        }

        .totals-table tr.total-row {
            background: #ffffff;
            color: #000000;
            border-top: 2px solid #000000;
            border-bottom: 2px solid #000000;
        }

        .totals-table tr.total-row td {
            font-size: 13.5px;
            font-weight: 800;
            padding: 8px 10px;
            color: #000000;
        }

        .totals-table .amount {
            font-family: 'Courier New', Courier, monospace;
            text-align: right;
            font-weight: 700;
        }

        /* Signatures Block (2 columns) */
        .signatures-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            padding-top: 28px;
            margin-top: auto;
            border-top: 1px dashed #cbd5e1;
            max-width: 550px;
            margin-left: auto;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #64748b;
            margin-bottom: 6px;
        }

        .signature-title {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .signature-subtitle {
            font-size: 10px;
            color: #94a3b8;
        }

        /* PRINT MEDIA RULES */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            .screen-toolbar {
                display: none !important;
            }

            .sheet-container {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .items-table th {
                background: #ffffff !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
            }

            .items-table td {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                background: #ffffff !important;
            }

            .totals-table tr.total-row {
                background: #ffffff !important;
                color: #000000 !important;
                border-top: 2px solid #000000 !important;
                border-bottom: 2px solid #000000 !important;
            }

            .totals-table tr.total-row td {
                color: #000000 !important;
            }

            .bill-badge {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                background: #ffffff !important;
            }

            .info-card {
                background: #ffffff !important;
                border: 1px solid #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Toolbar -->
    <div class="screen-toolbar">
        <div>
            <strong>Bee Comfort — Invoice Print Preview</strong>
            <span style="color: #94a3b8; margin-left: 10px;">
                Standard A4 Document &bull; Invoice #{{ $sale->invoice_no }} ({{ $sale->bill_type === 'china' ? 'China Bill' : 'Bee Comfort Bill' }})
            </span>
        </div>
        <div>
            <button type="button" onclick="window.close()" class="btn-action btn-close-view">Close Preview</button>
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Invoice (A4)
            </button>
        </div>
    </div>

    <!-- Sheet Container -->
    <div class="sheet-container">
        
        <div>
            <!-- Header -->
            <div class="inv-header">
                <div>
                    @if(!empty($company->logo) && file_exists(public_path($company->logo)))
                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name ?? 'Bee Comfort' }}" class="brand-logo">
                    @endif
                    <div class="brand-title">{{ $company->name ?? 'Bee Comfort' }}</div>
                    @if(!empty($company->tagline))
                        <div class="brand-tagline">{{ $company->tagline }}</div>
                    @endif
                    <div class="brand-contact">
                        @if(!empty($company->address))
                            <div>{{ $company->address }}</div>
                        @endif
                        <div>
                            @if(!empty($company->phone))
                                <span>Phone: {{ $company->phone }}</span>
                            @endif
                            @if(!empty($company->phone) && !empty($company->email))
                                &bull;
                            @endif
                            @if(!empty($company->email))
                                <span>{{ $company->email }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="doc-meta">
                    <div class="doc-title">Sales Invoice</div>
                    <div>
                        @if($sale->bill_type === 'china')
                            <span class="bill-badge badge-china">China Bill</span>
                        @else
                            <span class="bill-badge badge-bee-comfort">Bee Comfort Bill</span>
                        @endif
                    </div>
                    <div class="meta-details">
                        Invoice No: <strong>{{ $sale->invoice_no }}</strong><br>
                        Date: <strong>{{ $sale->invoice_date->format('d M Y') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="info-card">
                <div class="info-card-header">Bill To Customer / Buyer</div>
                <div class="customer-company">{{ $sale->customer->name }}</div>
                
                @if($sale->customer->phone)
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <strong>{{ $sale->customer->phone }}</strong>
                    </div>
                @endif

                @if($sale->customer->address)
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span>{{ $sale->customer->address }}</span>
                    </div>
                @endif
            </div>

            <!-- Items Table (Clean Product Title, No pieces/code inside title) -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 35px;">#</th>
                        <th>Product Title</th>
                        <th class="text-center" style="width: 90px;">Unit Pack</th>
                        <th class="text-center" style="width: 70px;">Qty</th>
                        <th class="text-right" style="width: 110px;">Rate (Rs.)</th>
                        <th class="text-right" style="width: 120px;">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->details as $index => $item)
                    <tr>
                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                        <td>
                            <div class="item-title">{{ $item->clean_product_title }}</div>
                        </td>
                        <td class="text-center small">{{ $item->product->dozen->label ?? 'Unit' }}</td>
                        <td class="text-center font-monospace" style="font-weight: 700;">{{ $item->quantity }}</td>
                        <td class="text-right item-price">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right item-total">{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary & Totals -->
            <div class="summary-section">
                <!-- Notes -->
                <div class="notes-box">
                    <div class="notes-title">Notes &amp; Terms</div>
                    <div class="notes-content">
                        {{ $sale->notes ?: ($company->terms_conditions ?? 'Thank you for your business. Please inspect goods upon receipt.') }}
                    </div>
                </div>

                <!-- Totals Table -->
                <div class="totals-table-box">
                    <table class="totals-table">
                        <tr>
                            <td>Total Quantity:</td>
                            <td class="amount">{{ $sale->details->sum('quantity') }} units</td>
                        </tr>
                        <tr>
                            <td>Subtotal:</td>
                            <td class="amount">Rs. {{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        @if($sale->discount > 0)
                        <tr>
                            <td>Discount:</td>
                            <td class="amount">- Rs. {{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        @endif

                        @if($sale->show_previous_balance)
                            <tr>
                                <td>Current Bill:</td>
                                <td class="amount">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Previous Balance:</td>
                                <td class="amount">Rs. {{ number_format($sale->previous_balance, 2) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Payable:</td>
                                <td class="amount">Rs. {{ number_format($sale->total_amount + $sale->previous_balance, 2) }}</td>
                            </tr>
                        @else
                            <tr class="total-row">
                                <td>Grand Total:</td>
                                <td class="amount">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Signatures Section -->
        <div>
            <div class="signatures-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-title">Checked &amp; Verified</div>
                    <div class="signature-subtitle">Warehouse Dispatcher</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-title">Customer Receiver</div>
                    <div class="signature-subtitle">Signature &amp; Stamp</div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-trigger print dialog after small delay
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
