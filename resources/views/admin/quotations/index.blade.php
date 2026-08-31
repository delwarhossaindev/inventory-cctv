@extends('layouts.app')
@section('title', 'Quotations')
@section('heading', 'Quotations')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.quotations.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>New Quotation</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Quote #</th><th>Date</th><th>Customer</th><th class="text-end">Total</th><th>Valid Until</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($quotations as $q)
                    <tr>
                        <td><a href="{{ route('admin.quotations.show', $q) }}">{{ $q->quote_no }}</a></td>
                        <td class="small">{{ $q->quote_date->format('d M Y') }}</td>
                        <td class="small">{{ optional($q->customer)->name ?: 'Walk-in' }}</td>
                        <td class="text-end fw-semibold">@money($q->total)</td>
                        <td class="small text-muted">{{ $q->valid_until?->format('d M Y') ?: '—' }}</td>
                        <td>
                            @php $sc = ['draft' => 'secondary', 'sent' => 'primary', 'accepted' => 'success', 'declined' => 'danger']; @endphp
                            <span class="badge bg-{{ $sc[$q->status] ?? 'secondary' }}">{{ ucfirst($q->status) }}</span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.quotations.show', $q) }}" class="btn btn-sm btn-outline-secondary p-0 px-1"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.quotations.pdf', $q) }}" target="_blank" class="btn btn-sm btn-outline-primary p-0 px-1" title="PDF"><i class="bi bi-file-pdf"></i></a>
                            <form action="{{ route('admin.quotations.destroy', $q) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger p-0 px-1"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No quotations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($quotations->hasPages())
        <div class="card-footer bg-white">{{ $quotations->links() }}</div>
    @endif
</div>
@endsection
