@extends('layouts.app')
@section('title', 'Return Details')
@section('heading', 'Return Details — ' . $return->sale->invoice_no)

@section('content')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.returns.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    <a href="{{ route('admin.sales.show', $return->sale_id) }}" class="btn btn-sm btn-outline-primary">View Sale</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-6">
                <div class="text-muted small">Original Sale</div>
                <div class="fw-bold">{{ $return->sale->invoice_no }}</div>
                <div class="text-muted small">Return Date: {{ $return->return_date->format('d M Y') }}</div>
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Customer</div>
                <div class="fw-semibold">{{ optional($return->sale->customer)->name ?: 'Walk-in' }}</div>
                @if ($return->reason)
                    <span class="badge bg-warning text-dark mt-1">{{ $return->reason }}</span>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead class="table-light"><tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($return->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?: 'Deleted product' }}</td>
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
                <div class="d-flex justify-content-between py-2 border-top fw-bold fs-5 text-danger">
                    <span>Total Refund</span>
                    <span>@money($return->total)</span>
                </div>
            </div>
        </div>

        @if ($return->note)
            <div class="mt-3 text-muted small"><strong>Note:</strong> {{ $return->note }}</div>
        @endif
    </div>
</div>
@endsection
