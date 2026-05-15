<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document['title'] ?? 'Print Preview' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }
        .page {
            width: 80mm;
            min-height: 100vh;
            margin: 24px auto;
            background: #fff;
            padding: 12px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .16);
        }
        .center { text-align: center; }
        .muted { color: #64748b; }
        .title {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .subtitle {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .danger {
            color: #dc2626;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
            line-height: 1.35;
        }
        .label {
            color: #64748b;
            white-space: nowrap;
        }
        .value {
            text-align: right;
            font-weight: 800;
        }
        .rule {
            margin: 10px 0;
            border-top: 1px dashed #94a3b8;
        }
        .item {
            margin-bottom: 8px;
            break-inside: avoid;
        }
        .item-main {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            font-size: 13px;
            font-weight: 800;
        }
        .qty {
            white-space: nowrap;
            text-align: right;
        }
        .note {
            margin-top: 2px;
            padding-left: 8px;
            color: #047857;
            font-size: 11px;
        }
        .totals .row {
            font-size: 12px;
            margin: 3px 0;
        }
        .grand {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #111827;
            font-size: 15px !important;
        }
        .actions {
            width: 80mm;
            margin: 16px auto 24px;
            display: flex;
            gap: 8px;
        }
        button {
            flex: 1;
            border: 0;
            border-radius: 8px;
            background: #007882;
            color: #fff;
            cursor: pointer;
            font-weight: 800;
            padding: 10px 12px;
        }
        button.secondary {
            background: #334155;
        }
        @media print {
            body { background: #fff; }
            .page {
                width: 80mm;
                margin: 0;
                min-height: 0;
                box-shadow: none;
            }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    @php
        $isSlip = ($document['type'] ?? 'slip') === 'slip';
        $isCancel = (bool) ($document['isCancel'] ?? false);
        $money = fn ($value) => '$'.number_format((float) ($value ?? 0), 2);
        $khr = fn ($value) => number_format((int) floor((float) ($value ?? 0))).' KHR';
        $branch = $document['branch'] ?? [];
    @endphp

    <main class="page">
        <header class="center">
            @if(!$isSlip && !empty($branch['logoUrl']))
                <img
                    src="{{ $branch['logoUrl'] }}"
                    alt="Branch logo"
                    style="max-width: 46mm; max-height: 24mm; object-fit: contain; margin: 0 auto 8px; display: block;"
                >
            @endif
            <div class="title {{ $isCancel ? 'danger' : '' }}">{{ $document['title'] ?? 'Print' }}</div>
            @unless($isSlip)
                <div class="subtitle muted">{{ $document['subtitle'] ?? 'Browser Preview' }}</div>
            @endunless
            @if(!empty($branch['name']))
                <div class="subtitle">{{ $branch['name'] }}</div>
            @endif
            @unless($isSlip)
                @if(!empty($branch['phone']))
                    <div class="subtitle muted">Phone: {{ $branch['phone'] }}</div>
                @endif
                @if(!empty($branch['vatNumber']))
                    <div class="subtitle muted">VAT: {{ $branch['vatNumber'] }}</div>
                @endif
            @endunless
            @if(!empty($document['printerName']))
                <div class="subtitle">Printer: {{ $document['printerName'] }}</div>
            @endif
        </header>

        <div class="rule"></div>

        <section>
            @if(!empty($document['no']))
                <div class="row"><span class="label">No</span><span class="value">{{ $document['no'] }}</span></div>
            @endif
            @if(!empty($document['jobNo']))
                <div class="row"><span class="label">Job</span><span class="value">{{ $document['jobNo'] }}</span></div>
            @endif
            @if(!empty($document['seat']))
                <div class="row"><span class="label">Seat</span><span class="value">{{ $document['seat'] }}</span></div>
            @endif
            @if(!empty($document['billName']))
                <div class="row"><span class="label">Bill</span><span class="value">{{ $document['billName'] }}</span></div>
            @endif
            @if(!empty($document['date']))
                <div class="row"><span class="label">Time</span><span class="value">{{ $document['date'] }}</span></div>
            @endif
            @if(!empty($document['staff']))
                <div class="row"><span class="label">Staff</span><span class="value">{{ $document['staff'] }}</span></div>
            @endif
            @if(!empty($document['paymentMethod']))
                <div class="row"><span class="label">Payment</span><span class="value">{{ $document['paymentMethod'] }}</span></div>
            @endif
            @if(!empty($document['paymentCurrency']))
                <div class="row"><span class="label">Currency</span><span class="value">{{ $document['paymentCurrency'] }}</span></div>
            @endif
            @if(!empty($document['paidAt']))
                <div class="row"><span class="label">Paid At</span><span class="value">{{ $document['paidAt'] }}</span></div>
            @endif
            @if(!empty($document['isReprint']))
                <div class="row danger"><span class="label danger">Note</span><span class="value">RE-PRINT</span></div>
            @endif
        </section>

        <div class="rule"></div>

        <section>
            @foreach(($document['lines'] ?? []) as $line)
                <div class="item">
                    <div class="item-main">
                        <span>{{ $line['name'] ?? 'Menu item' }}</span>
                        <span class="qty">
                            x{{ number_format((float) ($line['quantity'] ?? 0), 2) }}
                            @unless($isSlip)
                                {{ $money($line['total'] ?? 0) }}
                            @endunless
                        </span>
                    </div>
                    @if(!empty($line['unitPrice']) && !$isSlip)
                        <div class="row muted"><span>Unit</span><span>{{ $money($line['unitPrice']) }}</span></div>
                    @endif
                    @if(!empty($line['note']))
                        <div class="note">* {{ $line['note'] }}</div>
                    @endif
                </div>
            @endforeach
        </section>

        @unless($isSlip)
            <div class="rule"></div>
            <section class="totals">
                <div class="row"><span>Subtotal</span><span>{{ $money($document['subtotal'] ?? 0) }}</span></div>
                <div class="row"><span>Discount</span><span>{{ $money($document['discount'] ?? 0) }}</span></div>
                <div class="row"><span>Tax</span><span>{{ $money($document['tax'] ?? 0) }}</span></div>
                <div class="row grand"><span>Grand Total</span><span>{{ $money($document['grandTotal'] ?? 0) }}</span></div>
                <div class="row"><span>Paid</span><span>{{ $money($document['paidAmount'] ?? 0) }}</span></div>
                <div class="row"><span>Balance</span><span>{{ $money($document['balanceAmount'] ?? 0) }}</span></div>
                @if(array_key_exists('receivedAmount', $document) && $document['receivedAmount'] !== null)
                    <div class="row"><span>Received</span><span>
                        {{ ($document['paymentCurrency'] ?? 'USD') === 'KHR' ? $khr($document['receivedAmount']) : $money($document['receivedAmount']) }}
                    </span></div>
                @endif
                @if(array_key_exists('changeUsdAmount', $document) && $document['changeUsdAmount'] !== null)
                    <div class="row"><span>Change USD</span><span>${{ number_format((int) floor((float) $document['changeUsdAmount'])) }}</span></div>
                @endif
                @if(array_key_exists('changeKhrAmount', $document) && $document['changeKhrAmount'] !== null)
                    <div class="row"><span>Change KHR</span><span>{{ $khr($document['changeKhrAmount']) }}</span></div>
                @endif
            </section>
        @endunless

        @if(!$isSlip && !empty($branch['paymentQrcodeUrl']))
            <div class="rule"></div>
            <section class="center">
                <div class="subtitle muted">Payment QR Code</div>
                <img
                    src="{{ $branch['paymentQrcodeUrl'] }}"
                    alt="Payment QR code"
                    style="width: 42mm; height: 42mm; object-fit: contain; margin: 8px auto 0; display: block;"
                >
            </section>
        @endif

        <div class="rule"></div>
        <footer class="center muted" style="font-size: 11px;">
            Development browser print preview
        </footer>
    </main>

    <div class="actions">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" class="secondary" onclick="window.close()">Close</button>
    </div>

    <script>
        window.addEventListener('load', () => {
            window.setTimeout(() => window.print(), 250);
        });
    </script>
</body>
</html>
