@extends('layouts.app')
@section('title', $quotation->quote_no)
@section('heading', 'Quotation ' . $quotation->quote_no)

@section('content')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    <a href="{{ route('admin.quotations.pdf', $quotation) }}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-file-pdf me-1"></i>PDF</a>
    <a href="{{ route('admin.quotations.pdf', ['quotation' => $quotation, 'download' => 1]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-6">
                <h5 class="fw-bold mb-1">{{ $settings['company_name'] ?? 'Quotation' }}</h5>
                @if (!empty($settings['company_address']))<div class="text-muted small">{{ $settings['company_address'] }}</div>@endif
                @if (!empty($settings['company_phone']))<div class="text-muted small">{{ $settings['company_phone'] }}</div>@endif
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Quotation</div>
                <div class="fw-bold fs-5">{{ $quotation->quote_no }}</div>
                <div class="small text-muted">Date: {{ $quotation->quote_date->format('d M Y') }}</div>
                @if ($quotation->valid_until)
                    <div class="small text-muted">Valid until: {{ $quotation->valid_until->format('d M Y') }}</div>
                @endif
            </div>
        </div>

        <div class="mb-3">
            <div class="text-muted small">Customer</div>
            <div class="fw-semibold">{{ optional($quotation->customer)->name ?: 'Walk-in Customer' }}</div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead class="table-light"><tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($quotation->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?: '—' }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">@money($item->unit_price)</td>
                            <td class="text-end">@money($item->subtotal)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Subtotal</span><span>@money($quotation->subtotal)</span></div>
                @if ($quotation->discount > 0)
                    <div class="d-flex justify-content-between py-1"><span class="text-muted">Discount</span><span>- @money($quotation->discount)</span></div>
                @endif
                @if ($quotation->tax > 0)
                    <div class="d-flex justify-content-between py-1"><span class="text-muted">Tax</span><span>+ @money($quotation->tax)</span></div>
                @endif
                <div class="d-flex justify-content-between py-1 border-top fw-bold fs-5"><span>Total</span><span>@money($quotation->total)</span></div>
            </div>
        </div>

        @if ($quotation->note)
            <div class="mt-3 text-muted small"><strong>Note:</strong> {{ $quotation->note }}</div>
        @endif

        <div class="mt-3 text-center text-muted small">{{ $settings['invoice_footer'] ?? '' }}</div>
    </div>
</div>
@endsection
