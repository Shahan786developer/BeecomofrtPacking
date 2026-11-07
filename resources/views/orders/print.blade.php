<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->order_no }} — {{ $order->customer->name ?? 'Customer' }} (Bee Comfort / Hashmi Brothers)</title>
    
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm 14mm 14mm;
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

        .screen-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 52px;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
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
            background: #2563eb;
            color: #ffffff;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: #334155;
            color: #ffffff;
        }

        .btn-back:hover {
            background: #475569;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            margin: 70px auto 40px auto;
            background: #ffffff;
            padding: 16mm 18mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media print {
            .screen-toolbar { display: none !important; }
            body { background: #ffffff !important; }
            .print-page {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .company-subtitle {
            font-size: 12px;
            color: #475569;
            font-weight: 600;
            margin-top: 2px;
        }

        .company-contacts {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.4;
        }

        .voucher-title-box {
            text-align: right;
        }

        .voucher-title {
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .voucher-no {
            font-size: 16px;
            font-weight: 800;
            font-family: monospace;
            color: #0f172a;
            margin-top: 2px;
        }

        .meta-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .meta-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Matrix Table */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .order-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .order-table th.th-std {
            background: #e0f2fe;
            color: #0369a1;
        }

        .order-table th.th-plus {
            background: #fef3c7;
            color: #92400e;
        }

        .order-table td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: center;
        }

        .order-table td.td-left {
            text-align: left;
        }

        .order-table tbody tr.article-header-row td {
            background: #f8fafc;
            font-weight: 700;
            color: #1e3a8a;
            text-align: left;
            padding: 6px 10px;
            border-top: 2px solid #94a3b8;
        }

        .qty-cell {
            font-family: monospace;
            font-weight: 600;
        }

        .qty-empty {
            color: #cbd5e1;
        }

        .total-cell {
            background: #ecfdf5;
            color: #047857;
            font-weight: 800;
            font-family: monospace;
        }

        /* Summary & Signatures */
        .summary-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 20px;
            margin-top: 10px;
            margin-bottom: 40px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            padding-top: 30px;
        }

        .sig-line {
            width: 200px;
            border-top: 1px solid #0f172a;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- SCREEN ACTIONS BAR -->
    <div class="screen-toolbar">
        <div style="font-weight: 700;">
            Sale Order Booking Sheet — {{ $order->order_no }}
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('orders.index') }}" class="btn-action btn-back">
                &larr; Back to Orders
            </a>
            <button onclick="window.print()" class="btn-action btn-print">
                &#128438; Print Order (A4)
            </button>
        </div>
    </div>

    <!-- PRINTABLE A4 PAGE -->
    <div class="print-page">
        <div>
            <!-- HEADER -->
            <div class="header-top">
                <div>
                    <div class="company-name">{{ $companyProfile->company_name ?? 'BEE COMFORT' }}</div>
                    <div class="company-subtitle">{{ $companyProfile->tagline ?? 'Packing & Garments Solutions • Hashmi Brothers' }}</div>
                    <div class="company-contacts">
                        {{ $companyProfile->address ?? 'Warehouse • Pakistan' }}<br>
                        Phone: {{ $companyProfile->phone ?? '+92 300 1234567' }} • Email: {{ $companyProfile->email ?? 'orders@beecomfort.com' }}
                    </div>
                </div>
                <div class="voucher-title-box">
                    <div class="voucher-title">Order Booking Sheet</div>
                    <div class="voucher-no">Order No: {{ $order->order_no }}</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                        Date: {{ $order->order_date ? $order->order_date->format('d/m/Y') : date('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- METADATA -->
            <div class="meta-card">
                <div>
                    <div class="meta-label">Customer Information</div>
                    <div class="meta-value">{{ $order->customer->name ?? 'Walk-in Retail' }} ({{ $order->customer->formatted_code ?? 'B000' }})</div>
                    @if(!empty($order->customer->phone))
                        <div style="font-size: 12px; color: #475569; margin-top: 2px;">Phone: {{ $order->customer->phone }}</div>
                    @endif
                    @if(!empty($order->customer->city))
                        <div style="font-size: 12px; color: #475569;">City: {{ $order->customer->city }}</div>
                    @endif
                </div>
                <div>
                    <div class="meta-label">Booking Status & Details</div>
                    <div class="meta-value" style="color: #2563eb;">{{ $order->status }}</div>
                    <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                        Articles Booked: {{ count($groupedArticles) }}
                    </div>
                    @if(!empty($order->notes))
                        <div style="font-size: 12px; color: #d97706; margin-top: 2px; font-style: italic;">
                            Note: {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- MATRIX TABLE -->
            <table class="order-table">
                <thead>
                    <tr>
                        <th style="width: 170px;" class="td-left">Article</th>
                        <th style="width: 50px;">Cup</th>
                        <th style="width: 100px;" class="td-left">Color</th>
                        <th class="th-std">32</th>
                        <th class="th-std">34</th>
                        <th class="th-std">36</th>
                        <th class="th-std">38</th>
                        <th class="th-std">40</th>
                        <th class="th-std">42</th>
                        <th class="th-plus">44</th>
                        <th class="th-plus">46</th>
                        <th class="th-plus">48</th>
                        <th class="th-plus">50</th>
                        <th style="width: 75px;">Total Pcs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedArticles as $articleName => $items)
                        <tr class="article-header-row">
                            <td colspan="14">
                                &#9656; {{ $articleName }}
                            </td>
                        </tr>
                        @foreach($items as $item)
                            <tr>
                                <td class="td-left" style="padding-left: 18px; color: #475569;">{{ $item->article_name }}</td>
                                <td style="font-weight: 700;">{{ $item->cup ?: '-' }}</td>
                                <td class="td-left" style="font-weight: 600;">{{ $item->color ?: 'STANDARD' }}</td>

                                <td class="qty-cell">{{ $item->size_32 > 0 ? $item->size_32 : '-' }}</td>
                                <td class="qty-cell">{{ $item->size_34 > 0 ? $item->size_34 : '-' }}</td>
                                <td class="qty-cell">{{ $item->size_36 > 0 ? $item->size_36 : '-' }}</td>
                                <td class="qty-cell">{{ $item->size_38 > 0 ? $item->size_38 : '-' }}</td>
                                <td class="qty-cell">{{ $item->size_40 > 0 ? $item->size_40 : '-' }}</td>
                                <td class="qty-cell">{{ $item->size_42 > 0 ? $item->size_42 : '-' }}</td>

                                <td class="qty-cell" style="background: #fffbeb;">{{ $item->size_44 > 0 ? $item->size_44 : '-' }}</td>
                                <td class="qty-cell" style="background: #fffbeb;">{{ $item->size_46 > 0 ? $item->size_46 : '-' }}</td>
                                <td class="qty-cell" style="background: #fffbeb;">{{ $item->size_48 > 0 ? $item->size_48 : '-' }}</td>
                                <td class="qty-cell" style="background: #fffbeb;">{{ $item->size_50 > 0 ? $item->size_50 : '-' }}</td>

                                <td class="total-cell">{{ number_format($item->total_qty) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <!-- SUMMARY BOX -->
            <div class="summary-box">
                <div>
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Total Articles</div>
                    <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ count($groupedArticles) }} article types</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Grand Total Quantity</div>
                    <div style="font-size: 22px; font-weight: 800; color: #047857; font-family: monospace;">
                        {{ number_format($order->total_quantity) }} <span style="font-size: 13px; font-weight: 600; color: #64748b;">pieces</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER SIGNATURES -->
        <div class="signatures">
            <div class="sig-line">Prepared / Booked By</div>
            <div class="sig-line">Warehouse Incharge</div>
            <div class="sig-line">Customer Signature / Stamp</div>
        </div>
    </div>

</body>
</html>
