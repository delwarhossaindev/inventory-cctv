@extends('pdf.layout')

@section('content')
@php $cust = $quotation->customer; @endphp

{{-- Document label --}}
<table width="100%" style="margin-bottom:6px;">
    <tr>
        <td style="width:40%; vertical-align:middle;">
            <barcode code="{{ $quotation->quote_no }}" type="C128" height="0.7" />
        </td>
        <td style="width:25%; text-align:center; vertical-align:middle;">
            <span style="background:#dcdcdc; border:1px solid #999; padding:3px 16px; font-weight:bold;">Quotation</span>
        </td>
        <td style="width:35%;"></td>
    </tr>
</table>

{{-- Info grid --}}
<table width="100%" style="border:1px solid #000; font-size:10.5px;" cellpadding="3">
    <tr>
        <td style="width:14%; font-weight:bold;">Quotation No</td>
        <td style="width:36%;">: {{ $quotation->quote_no }}</td>
        <td style="width:14%; font-weight:bold;">Date</td>
        <td style="width:36%;">: {{ $quotation->quote_date?->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Customer Name</td>
        <td>: {{ optional($cust)->name ?: 'Walk-in Customer' }}</td>
        <td style="font-weight:bold;">Valid Until</td>
        <td>: {{ $quotation->valid_until?->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Address</td>
        <td>: {{ optional($cust)->address }}</td>
        <td style="font-weight:bold;">Status</td>
        <td>: {{ ucfirst($quotation->status) }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Mobile</td>
        <td>: {{ optional($cust)->phone }}</td>
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
            <td style="border:1px solid #000; width:14%;">Unit Price</td>
            <td style="border:1px solid #000; width:11%;">Quantity</td>
            <td style="border:1px solid #000; width:15%;">Total Price</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($quotation->items as $i => $item)
            @php $p = $item->product; @endphp
            <tr>
                <td style="border:1px solid #000; text-align:center;">{{ $i + 1 }}</td>
                <td style="border:1px solid #000;">
                    <span style="font-weight:bold;">{{ optional($p)->name ?: '—' }}</span>
                    @if (optional($p)->model)<div style="font-size:9px;">Model : {{ $p->model }}</div>@endif
                </td>
                <td style="border:1px solid #000; text-align:right;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="border:1px solid #000; text-align:center;">{{ $item->quantity }}</td>
                <td style="border:1px solid #000; text-align:right;">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" rowspan="3" style="border:1px solid #000; vertical-align:top;">
                IN WORDS : {{ \App\Support\Pdf::amountInWords($quotation->total) }}
            </td>
            <td style="border:1px solid #000; text-align:right;">Subtotal :</td>
            <td style="border:1px solid #000; text-align:right;">{{ number_format($quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #000; text-align:right;">Discount :</td>
            <td style="border:1px solid #000; text-align:right;">{{ number_format($quotation->discount, 2) }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #000; text-align:right; font-weight:bold;">Total :</td>
            <td style="border:1px solid #000; text-align:right; font-weight:bold;">{{ number_format($quotation->total, 2) }}</td>
        </tr>
    </tfoot>
</table>

@if ($quotation->note)
    <div style="margin-top:8px; font-size:10px;"><span style="font-weight:bold;">Note:</span> {{ $quotation->note }}</div>
@endif

{{-- Signatures --}}
<table width="100%" style="margin-top:55px; font-size:10.5px;">
    <tr>
        <td style="width:50%; text-align:center;">
            <div>.........................................................</div>
            <div style="font-weight:bold;">Customer Signature</div>
        </td>
        <td style="width:50%; text-align:center;">
            <div>.........................................................</div>
            <div style="font-weight:bold;">Authorized's Signature</div>
        </td>
    </tr>
</table>
@endsection
