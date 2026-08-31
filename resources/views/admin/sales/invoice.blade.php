<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $sale->invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; background: #f0f0f0; }

        .invoice-wrap { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,.1); }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #1f2937; }
        .company-info h1 { font-size: 22px; color: #1f2937; margin-bottom: 4px; }
        .company-info p { color: #666; font-size: 12px; line-height: 1.6; }
        .company-logo img { max-height: 70px; }

        .invoice-meta { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .invoice-meta .block { }
        .invoice-meta .label { font-size: 11px; text-transform: uppercase; color: #999; letter-spacing: .5px; }
        .invoice-meta .value { font-size: 14px; font-weight: 600; }
        .invoice-no { font-size: 18px; font-weight: 700; color: #1f2937; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1f2937; color: #fff; padding: 10px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        tbody tr:last-child td { border-bottom: none; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        .totals { display: flex; justify-content: flex-end; }
        .totals-table { width: 280px; }
        .totals-table .row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; }
        .totals-table .row.grand { border-top: 2px solid #1f2937; margin-top: 5px; padding-top: 10px; font-size: 16px; font-weight: 700; }
        .totals-table .row.due { color: #dc3545; font-weight: 600; }

        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 12px; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-due { background: #fee2e2; color: #991b1b; }
        .badge-method { background: #e0e7ff; color: #3730a3; }

        .no-print { margin-bottom: 15px; text-align: center; }
        .no-print button, .no-print a { padding: 8px 20px; margin: 0 5px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; font-size: 13px; text-decoration: none; color: #333; display: inline-block; }
        .no-print button:hover, .no-print a:hover { background: #f5f5f5; }
        .no-print .btn-print { background: #1f2937; color: #fff; border-color: #1f2937; }
        .no-print .btn-print:hover { background: #374151; }

        @media print {
            body { background: #fff; }
            .invoice-wrap { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; }
            .no-print { display: none !important; }
            thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:800px;margin:20px auto 0">
        <button class="btn-print" onclick="window.print()">&#128424; Print Invoice</button>
        <a href="{{ route('admin.sales.show', $sale) }}">&#8592; Back to Sale</a>
        <a href="{{ route('admin.pos.index') }}">+ New Sale</a>
    </div>

    <div class="invoice-wrap">
        <div class="header">
            <div class="company-info">
                <h1>{{ $settings['company_name'] ?? 'My Business' }}</h1>
                @if (!empty($settings['company_address']))<p>{{ $settings['company_address'] }}</p>@endif
                @if (!empty($settings['company_phone']))<p>Phone: {{ $settings['company_phone'] }}</p>@endif
                @if (!empty($settings['company_email']))<p>Email: {{ $settings['company_email'] }}</p>@endif
            </div>
            @if (!empty($settings['company_logo']))
                <div class="company-logo"><img src="{{ $settings['company_logo'] }}" alt="Logo"></div>
            @endif
        </div>

        <div class="invoice-meta">
            <div class="block">
                <div class="label">Invoice No</div>
                <div class="invoice-no">{{ $sale->invoice_no }}</div>
            </div>
            <div class="block">
                <div class="label">Date</div>
                <div class="value">{{ $sale->sale_date?->format('d M Y') }}</div>
            </div>
            <div class="block">
                <div class="label">Customer</div>
                <div class="value">{{ optional($sale->customer)->name ?: 'Walk-in Customer' }}</div>
                @if (optional($sale->customer)->phone)<div style="color:#666;font-size:12px">{{ $sale->customer->phone }}</div>@endif
            </div>
            <div class="block">
                <div class="label">Payment</div>
                <div class="value">
                    <span class="badge badge-method">{{ ucfirst($sale->payment_method) }}</span>
                    @if ($sale->due > 0)
                        <span class="badge badge-due">Due</span>
                    @else
                        <span class="badge badge-paid">Paid</span>
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ optional($item->product)->name ?: 'Deleted product' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ ($settings['currency_symbol'] ?? '৳') . ' ' . number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ ($settings['currency_symbol'] ?? '৳') . ' ' . number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-table">
                @php $cs = $settings['currency_symbol'] ?? '৳'; @endphp
                <div class="row"><span>Subtotal</span><span>{{ $cs }} {{ number_format($sale->subtotal, 2) }}</span></div>
                @if ($sale->discount > 0)
                    <div class="row"><span>Discount</span><span>- {{ $cs }} {{ number_format($sale->discount, 2) }}</span></div>
                @endif
                @if ($sale->tax > 0)
                    <div class="row"><span>Tax</span><span>+ {{ $cs }} {{ number_format($sale->tax, 2) }}</span></div>
                @endif
                <div class="row grand"><span>Total</span><span>{{ $cs }} {{ number_format($sale->total, 2) }}</span></div>
                <div class="row"><span>Paid</span><span>{{ $cs }} {{ number_format($sale->paid, 2) }}</span></div>
                @if ($sale->due > 0)
                    <div class="row due"><span>Due</span><span>{{ $cs }} {{ number_format($sale->due, 2) }}</span></div>
                @endif
            </div>
        </div>

        @if ($sale->note)
            <div style="margin-top:20px;padding:10px;background:#f9fafb;border-radius:4px;font-size:12px;color:#666">
                <strong>Note:</strong> {{ $sale->note }}
            </div>
        @endif

        <div class="footer">
            {{ $settings['invoice_footer'] ?? 'Thank you for your business!' }}
        </div>
    </div>
</body>
</html>
