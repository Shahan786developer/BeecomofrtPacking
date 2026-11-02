<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Statement — {{ $customer->name }} (Bee Comfort)</title>
    
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
            margin: 74px auto 30px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 14mm 16mm 16mm 16mm;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header Layout */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .brand-logo-area h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .brand-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .company-contacts {
            font-size: 11px;
            color: #475569;
            line-height: 1.45;
        }

        .statement-title-badge {
            text-align: right;
        }

        .statement-label {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #0f172a;
            line-height: 1;
            margin-bottom: 6px;
        }

        .meta-pill {
            display: inline-block;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            font-family: monospace;
            color: #1e293b;
        }

        /* Parties Info Grid */
        .parties-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }

        .section-heading {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }

        .customer-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .party-detail {
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
        }

        .period-info {
            font-size: 12px;
            color: #334155;
        }
        .period-info strong {
            color: #0f172a;
        }

        /* Metric Summary Cards */
        .summary-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-mini-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }

        .summary-mini-card.highlight {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .summary-mini-card.balance {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        .smc-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .smc-value {
            font-size: 14px;
            font-weight: 800;
            font-family: monospace;
            color: #0f172a;
        }

        /* Table Design */
        .statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .statement-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.6px;
            padding: 8px 10px;
            text-align: left;
        }

        .statement-table th.text-center, .statement-table td.text-center { text-align: center; }
        .statement-table th.text-right, .statement-table td.text-right { text-align: right; }

        .statement-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .statement-table tr:nth-child(even) td {
            background-color: #fbfcfe;
        }

        .type-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .type-sale { background: #e0f2fe; color: #0284c7; }
        .type-return { background: #fee2e2; color: #dc2626; }
        .type-receipt { background: #dcfce7; color: #16a34a; }

        .fw-mono { font-family: monospace; }
        .fw-bold { font-weight: 700; }
        .text-debit { color: #0f172a; font-weight: 700; }
        .text-credit { color: #16a34a; font-weight: 700; }
        .text-balance { color: #dc2626; font-weight: 800; }

        /* Signatures & Footer */
        .footer-bottom {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }

        .signatures-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding: 0 20px;
        }

        .sig-block {
            text-align: center;
            width: 180px;
            border-top: 1px solid #475569;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
            }
            .sheet-container {
                margin: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN ACTIONS BAR (Hidden on Print) -->
    <div class="screen-toolbar no-print">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('customers.ledger', $customer) }}" class="btn-action btn-close-view">
                &larr; Back to Ledger
            </a>
            <span style="font-size:14px; font-weight:600; color:#cbd5e1;">
                Customer Statement: <strong style="color:#ffffff;">{{ $customer->name }}</strong>
            </span>
        </div>
        <div>
            <button onclick="window.print()" class="btn-action btn-print">
                Print Statement (A4)
            </button>
        </div>
    </div>

    <!-- MAIN PRINTABLE SHEET -->
    <div class="sheet-container">
        
        <div>
            <!-- HEADER -->
            <div class="header-row">
                <div class="brand-logo-area">
                    <h1>{{ $company->name ?? 'Bee Comfort' }}</h1>
                    @if(!empty($company->tagline))
                        <div class="brand-tagline">{{ $company->tagline }}</div>
                    @endif
                    <div class="company-contacts">
                        <div>{{ $company->address ?? 'Warehouse • Pakistan' }}</div>
                        <div>Phone/WhatsApp: <strong>{{ $company->phone ?? '+92 300 0000000' }}</strong> &bull; Email: {{ $company->email ?? 'orders@beecomfort.com' }}</div>
                    </div>
                </div>

                <div class="statement-title-badge">
                    <div class="statement-label">STATEMENT</div>
                    <div class="meta-pill">Date: {{ now()->format('d M Y') }}</div>
                </div>
            </div>

            <!-- PARTIES & PERIOD INFO -->
            <div class="parties-grid">
                <div>
                    <div class="section-heading">Account Holder / Customer</div>
                    <div class="customer-name">{{ $customer->name }}</div>
                    @if($customer->owner_name)
                        <div class="party-detail">Owner: <strong>{{ $customer->owner_name }}</strong></div>
                    @endif
                    <div class="party-detail">
                        Phones: {{ implode(' / ', array_filter([$customer->phone, $customer->phone_2, $customer->phone_3, $customer->phone_4])) ?: 'N/A' }}
                    </div>
                    @if($customer->address || $customer->city)
                        <div class="party-detail">Location: {{ $customer->address }} {{ $customer->city ? '(' . $customer->city . ')' : '' }}</div>
                    @endif
                </div>

                <div>
                    <div class="section-heading">Statement Period &amp; Details</div>
                    <div class="period-info">
                        <div>Period: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Start of Account' }}</strong> to <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Today (' . now()->format('d M Y') . ')' }}</strong></div>
                        <div>Currency: <strong>PKR (Rs.)</strong></div>
                        <div>Account ID: <strong>CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- METRIC CARDS -->
            <div class="summary-cards-grid">
                <div class="summary-mini-card">
                    <div class="smc-label">Opening Balance</div>
                    <div class="smc-value">Rs. {{ number_format($openingBalance, 2) }}</div>
                </div>
                <div class="summary-mini-card highlight">
                    <div class="smc-label">Total Invoiced (Debits)</div>
                    <div class="smc-value">Rs. {{ number_format($rangeDebits, 2) }}</div>
                </div>
                <div class="summary-mini-card">
                    <div class="smc-label">Payments &amp; Returns (Credits)</div>
                    <div class="smc-value">Rs. {{ number_format($rangeCredits, 2) }}</div>
                </div>
                <div class="summary-mini-card balance">
                    <div class="smc-label">Closing Balance Due</div>
                    <div class="smc-value text-balance">Rs. {{ number_format($closingBalance, 2) }}</div>
                </div>
            </div>

            <!-- LEDGER TRANSACTIONS TABLE -->
            <table class="statement-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Date</th>
                        <th style="width: 16%;">Voucher #</th>
                        <th style="width: 14%;">Type</th>
                        <th style="width: 28%;">Description / Reference</th>
                        <th class="text-right" style="width: 14%;">Debit (Rs.)</th>
                        <th class="text-right" style="width: 14%;">Credit (Rs.)</th>
                        <th class="text-right" style="width: 14%;">Balance (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @if($openingBalance > 0)
                    <tr>
                        <td class="fw-mono">{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '—' }}</td>
                        <td class="fw-mono">—</td>
                        <td><span class="type-badge" style="background:#f1f5f9; color:#475569;">OPENING</span></td>
                        <td><strong>Balance Brought Forward</strong></td>
                        <td class="text-right fw-mono">—</td>
                        <td class="text-right fw-mono">—</td>
                        <td class="text-right fw-mono fw-bold">Rs. {{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    @endif

                    @forelse($withBalance as $tx)
                    <tr>
                        <td class="fw-mono">{{ $tx['date_formatted'] }}</td>
                        <td class="fw-mono fw-bold">{{ $tx['doc_no'] }}</td>
                        <td>
                            @if($tx['type'] === 'sale')
                                <span class="type-badge type-sale">SALE BILL</span>
                            @elseif($tx['type'] === 'return')
                                <span class="type-badge type-return">RETURN</span>
                            @else
                                <span class="type-badge type-receipt">CASH PAID</span>
                            @endif
                        </td>
                        <td>
                            @if($tx['type'] === 'sale')
                                Sale Invoice ({{ $tx['items_count'] }} items)
                            @elseif($tx['type'] === 'return')
                                Bill Return {{ $tx['related_doc_no'] ? '(Ref: ' . $tx['related_doc_no'] . ')' : '' }}
                            @else
                                Payment Voucher
                            @endif
                            @if(!empty($tx['notes']))
                                <div style="font-size: 11px; color:#64748b; font-style: italic;">{{ $tx['notes'] }}</div>
                            @endif
                        </td>
                        <td class="text-right fw-mono text-debit">
                            {{ $tx['debit'] > 0 ? 'Rs. ' . number_format($tx['debit'], 2) : '—' }}
                        </td>
                        <td class="text-right fw-mono text-credit">
                            {{ $tx['credit'] > 0 ? 'Rs. ' . number_format($tx['credit'], 2) : '—' }}
                        </td>
                        <td class="text-right fw-mono fw-bold">
                            Rs. {{ number_format($tx['running_balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 24px; color: #64748b;">
                            No transaction entries recorded in this statement range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc; font-weight: bold; border-top: 2px solid #0f172a;">
                        <td colspan="4" style="padding: 10px; text-transform: uppercase;">Period Totals</td>
                        <td class="text-right fw-mono" style="padding: 10px;">Rs. {{ number_format($rangeDebits, 2) }}</td>
                        <td class="text-right fw-mono text-credit" style="padding: 10px;">Rs. {{ number_format($rangeCredits, 2) }}</td>
                        <td class="text-right fw-mono text-balance" style="padding: 10px;">Rs. {{ number_format($closingBalance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- FOOTER & SIGNATURES -->
        <div class="footer-bottom">
            <div style="font-size: 11px; color: #64748b; margin-bottom: 30px;">
                <strong>Terms &amp; Account Conditions:</strong> This is a computer-generated statement of accounts. If any discrepancy is noted, please notify our accounts department within 7 business days.
            </div>

            <div class="signatures-row">
                <div class="sig-block">Prepared By</div>
                <div class="sig-block">Customer Acceptance</div>
                <div class="sig-block">Authorized Signatory</div>
            </div>
        </div>

    </div>

</body>
</html>
