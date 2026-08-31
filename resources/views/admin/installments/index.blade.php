@extends('layouts.app')
@section('title', 'Installment Plans')
@section('heading', 'Installment / EMI Plans')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Sale</th><th>Customer</th><th class="text-end">Total</th><th class="text-end">Down</th><th>Installments</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($plans as $p)
                    <tr>
                        <td><a href="{{ route('admin.sales.show', $p->sale_id) }}">{{ optional($p->sale)->invoice_no }}</a></td>
                        <td class="small">{{ optional($p->customer)->name }}</td>
                        <td class="text-end">@money($p->total_amount)</td>
                        <td class="text-end">@money($p->down_payment)</td>
                        <td>{{ $p->paidCount() }}/{{ $p->num_installments }}</td>
                        <td><span class="badge bg-{{ $p->status === 'completed' ? 'success' : 'warning text-dark' }}">{{ ucfirst($p->status) }}</span></td>
                        <td><a href="{{ route('admin.installments.show', $p) }}" class="btn btn-sm btn-outline-secondary p-0 px-1"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No installment plans.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($plans->hasPages())<div class="card-footer bg-white">{{ $plans->links() }}</div>@endif
</div>
@endsection
