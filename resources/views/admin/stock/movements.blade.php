@extends('layouts.app')
@section('title', 'Stock Movements')
@section('heading', 'Stock Movements')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach (['purchase', 'sale', 'adjustment'] as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.stock.movements') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Product</th><th>Type</th><th class="text-end">Change</th><th class="text-end">Balance</th><th>Note</th></tr></thead>
            <tbody>
                @forelse ($movements as $m)
                    <tr>
                        <td class="small text-muted">{{ $m->created_at?->format('d M Y, h:i A') }}</td>
                        <td>{{ optional($m->product)->name ?: '—' }}</td>
                        <td>
                            @php $colors = ['purchase' => 'success', 'sale' => 'primary', 'adjustment' => 'warning text-dark']; @endphp
                            <span class="badge bg-{{ $colors[$m->type] ?? 'secondary' }}">{{ ucfirst($m->type) }}</span>
                        </td>
                        <td class="text-end fw-semibold {{ $m->quantity >= 0 ? 'text-success' : 'text-danger' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                        <td class="text-end">{{ $m->balance }}</td>
                        <td class="small text-muted">{{ $m->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No movements recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $movements->links() }}</div>
</div>
@endsection
