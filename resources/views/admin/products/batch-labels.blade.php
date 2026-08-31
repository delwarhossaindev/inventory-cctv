@extends('layouts.app')
@section('title', 'Batch Labels — ' . $product->name)
@section('heading', 'Batch Barcode Labels')

@push('styles')
<style>
    .label-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .label-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .label-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .label-grid.thermal { grid-template-columns: 1fr; max-width: 58mm; }
    .label-grid.thermal-80 { grid-template-columns: 1fr; max-width: 80mm; }
    .bc-label { border: 1px dashed #ccc; border-radius: 4px; padding: 8px; text-align: center; }
    .bc-label .nm { font-size: .72rem; font-weight: 600; line-height: 1.1; height: 2.2em; overflow: hidden; }
    .bc-label .meta { font-size: .68rem; color: #555; }
    .bc-label svg { max-width: 100%; height: 42px; }
    @media print {
        .no-print { display: none !important; }
        .content, body { background: #fff !important; }
        .bc-label { border-color: #ddd; page-break-inside: avoid; }
        .label-grid.thermal, .label-grid.thermal-80 { margin: 0; }
    }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <span class="fw-semibold">{{ $product->name }}</span>
            <span class="badge bg-secondary">{{ $batches->count() }} batch{{ $batches->count() === 1 ? '' : 'es' }}</span>
        </div>
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="form-label small mb-0">Copies / batch</label>
                <input type="number" name="copies" min="1" max="50" value="{{ $copies }}" class="form-control form-control-sm" style="width:90px;">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Layout</label>
                <select name="layout" class="form-select form-select-sm" id="layout-select" style="width:160px;">
                    <option value="4" @selected(request('layout','4')==='4')>A4 (4 col)</option>
                    <option value="3" @selected(request('layout')==='3')>A4 (3 col)</option>
                    <option value="2" @selected(request('layout')==='2')>A4 (2 col)</option>
                    <option value="58" @selected(request('layout')==='58')>Thermal 58mm</option>
                    <option value="80" @selected(request('layout')==='80')>Thermal 80mm</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="form-check mt-4">
                    <input type="checkbox" name="all" value="1" id="all" class="form-check-input" @checked($showAll)>
                    <label for="all" class="form-check-label small">Include empty batches</label>
                </div>
            </div>
            <div class="col-auto mt-4">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-dark"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
        </form>
    </div>
</div>

@php
    $layout = request('layout', '4');
    $gridClass = match ($layout) {
        '2' => 'cols-2', '3' => 'cols-3', '58' => 'thermal', '80' => 'thermal-80', default => '',
    };
@endphp

@if ($batches->isEmpty())
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-4">No batches to print for this product.</div></div>
@else
<div class="label-grid {{ $gridClass }}">
    @foreach ($batches as $batch)
        @for ($i = 0; $i < $copies; $i++)
            <div class="bc-label">
                <div class="nm">{{ $product->name }}</div>
                <svg class="bc" data-code="{{ $batch->batch_no }}"></svg>
                <div class="meta">Batch: {{ $batch->batch_no }}</div>
                <div class="meta">Qty: {{ $batch->remaining }} &middot; Cost: ৳{{ number_format($batch->unit_cost, 2) }}</div>
                <div class="meta">Recv: {{ $batch->received_at?->format('d M Y') }}</div>
            </div>
        @endfor
    @endforeach
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.querySelectorAll('svg.bc').forEach(function (el) {
        var code = el.getAttribute('data-code');
        if (!code) return;
        try {
            JsBarcode(el, code, { format: 'CODE128', width: 1.5, height: 36, fontSize: 11, margin: 2 });
        } catch (e) {}
    });
</script>
@endpush
