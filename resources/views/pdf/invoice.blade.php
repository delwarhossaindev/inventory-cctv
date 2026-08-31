@extends('pdf.layout')

@section('content')
@php
    $cust = $sale->customer;
    $soldBy = auth()->user()->name ?? '';
@endphp

{{-- Barcode + document label --}}
<table width="100%" style="margin-bottom:6px;">
    <tr>
        <td style="width:40%; vertical-align:middle;">
            <barcode code="{{ $sale->invoice_no }}" type="C128" height="0.7" />
        </td>
        <td style="width:20%; text-align:center; vertical-align:middle;">
            <span style="background:#dcdcdc; border:1px solid #999; padding:3px 16px; font-weight:bold;">Invoice / Bill</span>
        </td>
        <td style="width:40%;"></td>
    </tr>
</table>

{{-- Info grid --}}
<table width="100%" style="border:1px solid #000; font-size:10.5px;" cellpadding="3">
    <tr>
        <td style="width:13%; font-weight:bold;">Invoice No</td>
        <td style="width:37%;">: {{ $sale->invoice_no }}</td>
        <td style="width:13%; font-weight:bold;">Date</td>
        <td style="width:37%;">: {{ $sale->sale_date?->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Customer Name</td>
        <td>: {{ optional($cust)->name ?: 'Walk-in Customer' }}</td>
        <td style="font-weight:bold;">Branch</td>
        <td>: {{ config('company.name') }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Address</td>
        <td>: {{ optional($cust)->address }}</td>
        <td style="font-weight:bold;">P.O.No</td>
        <td>:</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Mobile</td>
        <td>: {{ optional($cust)->phone }}</td>
        <td style="font-weight:bold;">Req. No</td>
        <td>:</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Contact Name</td>
        <td>:</td>
        <td style="font-weight:bold;">Sold By</td>
        <td>: {{ $soldBy }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">E-mail</td>
        <td>: {{ optional($cust)->email }}</td>
        <td style="font-weight:bold;">Print Time</td>
        <td>: {{ now()->format('d-m-Y h:i:s a') }}</td>
    </tr>
</table>

{{-- Items --}}
<table width="100%" style="border:1px solid #000; margin-top:6px; font-size:10.5px;" cellpadding="4">
    <thead>
        <tr style="background:#f0f0f0; font-weight:bold; text-align:center;">
            <td style="border:1px solid #000; width:5%;">SI.</td>
            <td style="border:1px solid #000;">Product Description</td>
            <td style="border:1px solid #000; width:12%;">Warranty</td>
            <td style="border:1px solid #000; width:13%;">Unit Price</td>
            <td style="border:1px solid #000; width:10%;">Quantity</td>
            <td style="border:1px solid #000; width:14%;">Total Price</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($sale->items as $i => $item)
            @php
                $p = $item->product;
                $days = (int) optional($p)->warranty_days;
                $warranty = $days >= 30 ? round($days / 30) . ' Months' : ($days > 0 ? $days . ' Days' : '');
            @endphp
            <tr>
                <td style="border:1px solid #000; text-align:center;">{{ $i + 1 }}</td>
                <td style="border:1px solid #000;">
                    <span style="font-weight:bold;">{{ optional($p)->name ?: 'Deleted product' }}</span>
                    @if (optional($p)->model)<div style="font-size:9px;">Model : {{ $p->model }}</div>@endif
                    @if (optional($p)->sku)<div style="font-size:9px;">SN : {{ $p->sku }}</div>@endif
                </td>
                <td style="border:1px solid #000; text-align:center; font-weight:bold;">{{ $warranty }}</td>
                <td style="border:1px solid #000; text-align:right;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="border:1px solid #000; text-align:center;">{{ $item->quantity }}</td>
                <td style="border:1px solid #000; text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" rowspan="2" style="border:1px solid #000; vertical-align:top;">
                IN WORDS : {{ \App\Support\Pdf::amountInWords($sale->total) }}
            </td>
            <td style="border:1px solid #000; text-align:right; font-weight:bold;">Total Amount :</td>
            <td style="border:1px solid #000; text-align:right; font-weight:bold;">{{ number_format($sale->total, 2) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #000; text-align:right; font-weight:bold;">Due :</td>
            <td style="border:1px solid #000; text-align:right; font-weight:bold;">{{ number_format($sale->due, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- Signatures --}}
<table width="100%" style="margin-top:55px; font-size:10.5px;">
    <tr>
        <td style="width:50%; text-align:center;">
            <div>.........................................................</div>
            <div style="font-weight:bold;">Customer Signature</div>
            <div style="font-size:9px;">(Received the above goods in good condition)</div>
        </td>
        <td style="width:50%; text-align:center;">
            <div>.........................................................</div>
            <div style="font-weight:bold;">Authorized's Signature</div>
        </td>
    </tr>
</table>
@endsection
