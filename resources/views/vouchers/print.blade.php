<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Journal Voucher — {{ $voucher->voucher_no }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: #ffffff;
            padding: 20px;
            font-size: 13px;
        }
        .voucher-box {
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 20px;
            max-width: 700px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-name {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .voucher-title {
            text-align: right;
        }
        .voucher-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: #2563eb;
        }
        .grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .info-item {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 13px;
            font-weight: 700;
        }
        .amount-box {
            background: #f1f5f9;
            border: 2px dashed #94a3b8;
            border-radius: 6px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .amount-val {
            font-size: 22px;
            font-weight: 900;
            font-family: monospace;
            color: #0f172a;
        }
        .notes-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 30px;
            min-height: 50px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding: 0 20px;
        }
        .sig-line {
            text-align: center;
            width: 180px;
            border-top: 1px solid #0f172a;
            padding-top: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width:700px; margin:0 auto 15px auto; display:flex; justify-content:space-between;">
    <button onclick="window.print()" style="padding:8px 16px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">
        Print Voucher
    </button>
    <button onclick="window.close()" style="padding:8px 16px; background:#64748b; color:#fff; border:none; border-radius:6px; cursor:pointer;">
        Close Window
    </button>
</div>

<div class="voucher-box">
    <div class="header">
        <div>
            <div class="company-name">{{ $company->name ?? 'BEE COMFORT' }}</div>
            <div style="font-size:11px; color:#64748b;">{{ $company->address ?? 'Main Warehouse' }}</div>
            <div style="font-size:11px; color:#64748b;">Phone: {{ $company->phone ?? '—' }}</div>
        </div>
        <div class="voucher-title">
            <h2>JOURNAL VOUCHER</h2>
            <div style="font-family:monospace; font-weight:bold; font-size:14px;">{{ $voucher->voucher_no }}</div>
            <div style="font-size:11px; color:#64748b;">Date: {{ $voucher->voucher_date->format('d M Y') }}</div>
        </div>
    </div>

    <div class="grid-info">
        <div class="info-item">
            <div class="info-label">Account / Customer</div>
            <div class="info-value">
                {{ $voucher->customer ? $voucher->customer->name : 'General / Owner Capital' }}
                @if($voucher->customer && $voucher->customer->city)
                    ({{ $voucher->customer->city }})
                @endif
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Voucher Type</div>
            <div class="info-value" style="color: {{ $voucher->type === 'debit' ? '#b45309' : '#0369a1' }};">
                {{ strtoupper($voucher->type) }} ({{ $voucher->type === 'debit' ? 'DEBIT +' : 'CREDIT -' }})
            </div>
        </div>
        @if($voucher->reference_no)
        <div class="info-item" style="grid-column: span 2;">
            <div class="info-label">Reference #</div>
            <div class="info-value" style="font-family:monospace;">{{ $voucher->reference_no }}</div>
        </div>
        @endif
    </div>

    <div class="amount-box">
        <span style="font-size:14px; font-weight:bold; text-transform:uppercase;">Adjusted Amount:</span>
        <span class="amount-val">Rs. {{ number_format($voucher->amount, 2) }}</span>
    </div>

    <div class="notes-box">
        <div class="info-label">Narration / Remarks:</div>
        <div style="font-size:12px; margin-top:2px;">{{ $voucher->notes ?: 'Adjustment entry recorded in customer ledger.' }}</div>
    </div>

    <div class="signatures">
        <div class="sig-line">Prepared By ({{ $voucher->user ? $voucher->user->name : 'Staff' }})</div>
        <div class="sig-line">Accountant / Authorized</div>
    </div>
</div>

<script>
    if (window.location.search.includes('autoprint=1')) {
        window.print();
    }
</script>
</body>
</html>
