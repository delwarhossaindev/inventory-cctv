@extends('layouts.app')
@section('title', 'Sale Returns')
@section('heading', 'Sale Returns')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="text-muted small flex-grow-1">
                <i class="bi bi-info-circle me-1"></i>Returns are made against a sale. Find the invoice, then process the return.
            </div>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-primary ms-auto">
                <i class="bi bi-plus-lg me-1"></i>New Return
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Sale Invoice</th>
                    <th>Customer</th>
                    <th class="text-end">Return Amount</th>
                    <th>Reason</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($returns as $r)
                    <tr>
                        <td class="small">{{ $r->return_date->format('d M Y') }}</td>
                        <td><a href="{{ route('admin.sales.show', $r->sale_id) }}">{{ $r->sale->invoice_no }}</a></td>
                        <td class="small">{{ optional($r->sale->customer)->name ?: 'Walk-in' }}</td>
                        <td class="text-end fw-semibold text-danger">@money($r->total)</td>
                        <td class="small text-muted">{{ $r->reason ?: '—' }}</td>
                        <td><a href="{{ route('admin.returns.show', $r) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No returns yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($returns->hasPages())
        <div class="card-footer bg-white">{{ $returns->links() }}</div>
    @endif
</div>
@endsection
