<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 5mm; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 4px 0; }
        .row { display: flex; justify-content: space-between; }
        .small { font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .r { text-align: right; }
        .total-row { font-size: 14px; font-weight: bold; }
        .no-print { text-align: center; margin: 10px 0; }
        .no-print button { padding: 8px 20px; font-size: 14px; cursor: pointer; }
        @media print {
            body { width: 80mm; }
            .no-print { display: none !important; }
        }
        @page { size: 80mm auto; margin: 0; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print Receipt</button>
        <button onclick="document.body.style.width='58mm'; window.print()">Print 58mm</button>
        <a href="{{ route('admin.sales.show', $sale) }}">Back</a>
    </div>

    <div class="center bold" style="font-size:14px">{{ $settings['company_name'] ?? 'My Business' }}</div>
    @if (!empty($settings['company_address']))<div class="center small">{{ $settings['company_address'] }}</div>@endif
    @if (!empty($settings['company_phone']))<div class="center small">Tel: {{ $settings['company_phone'] }}</div>@endif

    <div class="line"></div>

    <div class="row"><span>Invoice:</span><span class="bold">{{ $sale->invoice_no }}</span></div>
    <div class="row"><span>Date:</span><span>{{ $sale->sale_date?->format('d/m/Y') }}</span></div>
    <div class="row"><span>Customer:</span><span>{{ optional($sale->customer)->name ?: 'Walk-in' }}</span></div>
    <div class="row"><span>Payment:</span><span>{{ ucfirst($sale->payment_method) }}</span></div>

    <div class="line"></div>

    <table>
        <tr class="bold"><td>Item</td><td class="r">Qty</td><td class="r">Price</td><td class="r">Total</td></tr>
    </table>
    <div class="line"></div>
    <table>
        @foreach ($sale->items as $item)
            <tr>
                <td colspan="4">{{ optional($item->product)->name ?: '—' }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="r">{{ $item->quantity }}</td>
                <td class="r">{{ number_format($item->unit_price, 0) }}</td>
                <td class="r">{{ number_format($item->subtotal, 0) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>

    @php $cs = $settings['currency_symbol'] ?? '৳'; @endphp
    <div class="row"><span>Subtotal:</span><span>{{ $cs }} {{ number_format($sale->subtotal, 2) }}</span></div>
    @if ($sale->discount > 0)
        <div class="row"><span>Discount:</span><span>-{{ $cs }} {{ number_format($sale->discount, 2) }}</span></div>
    @endif
    @if ($sale->tax > 0)
        <div class="row"><span>Tax:</span><span>+{{ $cs }} {{ number_format($sale->tax, 2) }}</span></div>
    @endif
    <div class="line"></div>
    <div class="row total-row"><span>TOTAL:</span><span>{{ $cs }} {{ number_format($sale->total, 2) }}</span></div>
    <div class="row"><span>Paid:</span><span>{{ $cs }} {{ number_format($sale->paid, 2) }}</span></div>
    @if ($sale->due > 0)
        <div class="row bold"><span>DUE:</span><span>{{ $cs }} {{ number_format($sale->due, 2) }}</span></div>
    @else
        <div class="row"><span>Change:</span><span>{{ $cs }} {{ number_format($sale->paid - $sale->total, 2) }}</span></div>
    @endif

    <div class="line"></div>
    <div class="center small" style="margin-top:4px">{{ $settings['invoice_footer'] ?? 'Thank you!' }}</div>
    <div class="center small">{{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
