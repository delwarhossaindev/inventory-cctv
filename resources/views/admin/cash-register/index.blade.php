@extends('layouts.app')
@section('title', 'Cash Register')
@section('heading', 'Cash Register')

@section('content')
@if (!$current)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('admin.cash-register.open') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small">Opening Balance</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" value="0" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button class="btn btn-success"><i class="bi bi-unlock me-1"></i>Open Register</button>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm border-start border-success border-4 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-success">Register Open</span>
                    <span class="ms-2 text-muted small">Opened: {{ $current->opened_at->format('d M Y H:i') }}</span>
                    <span class="ms-2">Opening: <strong>@money($current->opening_balance)</strong></span>
                </div>
                <form action="{{ route('admin.cash-register.close', $current) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="number" step="0.01" min="0" name="closing_balance" placeholder="Closing balance" class="form-control form-control-sm" style="width:150px" required>
                    <input type="text" name="note" placeholder="Note" class="form-control form-control-sm" style="width:150px">
                    <button class="btn btn-sm btn-danger"><i class="bi bi-lock me-1"></i>Close</button>
                </form>
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Register History</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>User</th><th>Opened</th><th>Closed</th><th class="text-end">Opening</th><th class="text-end">Closing</th><th class="text-end">Diff</th><th>Note</th></tr>
            </thead>
            <tbody>
                @forelse ($registers as $r)
                    @php $diff = $r->closing_balance !== null ? $r->closing_balance - $r->opening_balance : null; @endphp
                    <tr>
                        <td class="small fw-semibold">{{ optional($r->user)->name }}</td>
                        <td class="small">{{ $r->opened_at->format('d M H:i') }}</td>
                        <td class="small">{{ $r->closed_at ? $r->closed_at->format('d M H:i') : '—' }}</td>
                        <td class="text-end">@money($r->opening_balance)</td>
                        <td class="text-end">{{ $r->closing_balance !== null ? '৳ '.number_format($r->closing_balance,2) : '—' }}</td>
                        <td class="text-end {{ $diff !== null ? ($diff >= 0 ? 'text-success' : 'text-danger') : '' }}">{{ $diff !== null ? '৳ '.number_format($diff,2) : '—' }}</td>
                        <td class="small text-muted">{{ $r->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No register history.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($registers->hasPages())<div class="card-footer bg-white">{{ $registers->links() }}</div>@endif
</div>
@endsection
