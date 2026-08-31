@extends('layouts.app')
@section('title', 'Activity Log')
@section('heading', 'Activity Log')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    @php
                        $colors = ['sale_created' => 'success', 'sale_deleted' => 'danger', 'purchase_created' => 'primary', 'stock_adjusted' => 'warning', 'login' => 'info'];
                    @endphp
                    <tr>
                        <td class="small text-muted text-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="small fw-semibold">{{ optional($log->user)->name ?: 'System' }}</td>
                        <td><span class="badge bg-{{ $colors[$log->action] ?? 'secondary' }}">{{ str_replace('_', ' ', $log->action) }}</span></td>
                        <td class="small">{{ $log->description }}</td>
                        <td class="small text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer bg-white">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
