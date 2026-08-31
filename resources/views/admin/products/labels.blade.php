@extends('layouts.app')
@section('title', 'Barcode Labels')
@section('heading', 'Barcode Labels')

@push('styles')
<style>
    .label-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .label-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .label-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .label-grid.thermal { grid-template-columns: 1fr; max-width: 58mm; }
    .label-grid.thermal-80 { grid-template-columns: 1fr; max-width: 80mm; }
    .bc-label { border: 1px dashed #ccc; border-radius: 4px; padding: 8px; text-align: center; }
    .bc-label .nm { font-size: .72rem; font-weight: 600; line-height: 1.1; height: 2.2em; overflow: hidden; }
    .bc-label .pr { font-size: .72rem; color: #444; }
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
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name, SKU, barcode...">
            </div>
            <div class="col-md-3">
                <select name="main_category_id" class="form-select form-select-sm">
                    <option value="">All Main Categories</option>
                    @foreach ($mains as $m)
                        <option value="{{ $m->id }}" @selected(request('main_category_id') == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="copies" min="1" max="50" value="{{ $copies }}" class="form-control form-control-sm" title="Copies per product">
            </div>
            <div class="col-md-2">
                <select name="layout" class="form-select form-select-sm" id="layout-select">
                    <option value="4" @selected(request('layout','4')==='4')>A4 (4 col)</option>
                    <option value="3" @selected(request('layout')==='3')>A4 (3 col)</option>
                    <option value="2" @selected(request('layout')==='2')>A4 (2 col)</option>
                    <option value="58" @selected(request('layout')==='58')>Thermal 58mm</option>
                    <option value="80" @selected(request('layout')==='80')>Thermal 80mm</option>
                </select>
            </div>
            <div class="col">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i> Apply</button>
                <a href="{{ route('admin.products.labels') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-primary"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if ($products->isEmpty())
            <div class="text-center text-muted py-4">No products with barcodes match.</div>
        @else
            @php
                $layout = request('layout', '4');
                $gridClass = match($layout) { '2' => 'cols-2', '3' => 'cols-3', '58' => 'thermal', '80' => 'thermal-80', default => '' };
            @endphp
            <div class="label-grid {{ $gridClass }}">
                @foreach ($products as $p)
                    @for ($i = 0; $i < $copies; $i++)
                        <div class="bc-label">
                            <div class="nm">{{ $p->name }}</div>
                            @if ($p->sale_price > 0)<div class="pr">@money($p->sale_price)</div>@endif
                            <svg class="bc" data-code="{{ $p->barcode }}"></svg>
                        </div>
                    @endfor
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('svg.bc').forEach(function (el) {
        const code = el.dataset.code;
        const isEan13 = /^\d{13}$/.test(code);
        try {
            JsBarcode(el, code, { format: isEan13 ? 'EAN13' : 'CODE128', width: 1.5, height: 36, fontSize: 11, margin: 2 });
        } catch (e) {
            JsBarcode(el, code, { format: 'CODE128', width: 1.5, height: 36, fontSize: 11, margin: 2 });
        }
    });
});
</script>
@endpush
