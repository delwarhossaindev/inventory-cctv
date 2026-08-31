@extends('layouts.app')
@section('title', 'Login History')
@section('heading', 'Login History')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>User</th><th>Time</th><th>IP Address</th><th>Browser</th></tr>
            </thead>
            <tbody>
                @forelse ($logins as $l)
                    <tr>
                        <td class="fw-semibold">{{ optional($l->user)->name ?: '—' }}</td>
                        <td class="small">{{ $l->logged_in_at->format('d M Y H:i:s') }}</td>
                        <td class="small text-muted">{{ $l->ip_address }}</td>
                        <td class="small text-muted text-truncate" style="max-width:300px">{{ \Illuminate\Support\Str::limit($l->user_agent, 80) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No login records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logins->hasPages())<div class="card-footer bg-white">{{ $logins->links() }}</div>@endif
</div>
@endsection
