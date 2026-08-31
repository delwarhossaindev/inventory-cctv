@extends('layouts.app')
@section('title', 'Batches')
@section('heading', 'Stock Batches')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       style="flex:1 1 240px; min-width:200px;" placeholder="Search batch no, product, SKU...">
                <select name="status" class="form-select form-select-sm" style="flex:0 1 160px; min-width:140px;">
                    <option value="in" @selected($status === 'in')>In stock</option>
                    <option value="empty" @selected($status === 'empty')>Empty</option>
                    <option value="all" @selected($status === 'all')>All</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.batches.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch No</th>
                    <th>Product</th>
                    <th>Received</th>
                    <th class="text-end">Recv Qty</th>
                    <th class="text-end">Remaining</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $b)
                    <tr class="{{ $b->remaining <= 0 ? 'text-muted' : '' }}">
                        <td class="fw-semibold">{{ $b->batch_no }}</td>
                        <td>
                            @if ($b->product)
                                <a href="{{ route('admin.products.show', $b->product) }}" class="text-decoration-none">{{ $b->product->name }}</a>
                                @if ($b->product->sku)<div class="small text-muted">{{ $b->product->sku }}</div>@endif
                            @else
                                <span class="text-muted">Deleted product</span>
                            @endif
                        </td>
                        <td class="small">{{ $b->received_at?->format('d M Y') }}</td>
                        <td class="text-end">{{ $b->quantity }}</td>
                        <td class="text-end"><span class="badge bg-{{ $b->remaining > 0 ? 'success' : 'secondary' }}">{{ $b->remaining }}</span></td>
                        <td class="text-end">৳{{ number_format($b->unit_cost, 2) }}</td>
                        <td class="text-end text-nowrap">
                            @if ($b->product)
                                <a href="{{ route('admin.products.batch-labels', ['product' => $b->product, 'batch' => $b->id]) }}" target="_blank"
                                   class="btn btn-sm btn-outline-dark" title="Print label"><i class="bi bi-printer"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No batches found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($batches->hasPages())
        <div class="card-footer bg-white">{{ $batches->links() }}</div>
    @endif
</div>
@endsection
