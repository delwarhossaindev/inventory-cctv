@extends('layouts.app')
@section('title', 'Installment Plan')
@section('heading', 'Installment Plan — ' . optional($installment->sale)->invoice_no)

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.installments.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Total</div><div class="fw-bold fs-5">@money($installment->total_amount)</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Down Payment</div><div class="fw-bold">@money($installment->down_payment)</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Per Installment</div><div class="fw-bold">@money($installment->installment_amount)</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Progress</div><div class="fw-bold">{{ $installment->paidCount() }} / {{ $installment->num_installments }}</div></div></div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Due Date</th><th class="text-end">Amount</th><th>Status</th><th>Paid Date</th><th></th></tr></thead>
            <tbody>
                @foreach ($installment->payments as $p)
                    <tr>
                        <td>{{ $p->installment_no }}</td>
                        <td>{{ $p->due_date->format('d M Y') }}</td>
                        <td class="text-end">@money($p->amount)</td>
                        <td>
                            <span class="badge bg-{{ $p->status === 'paid' ? 'success' : ($p->due_date->isPast() ? 'danger' : 'warning text-dark') }}">
                                {{ $p->status === 'paid' ? 'Paid' : ($p->due_date->isPast() ? 'Overdue' : 'Pending') }}
                            </span>
                        </td>
                        <td class="small">{{ $p->paid_date?->format('d M Y') ?: '—' }}</td>
                        <td>
                            @if ($p->status !== 'paid')
                                <form action="{{ route('admin.installments.pay', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark as paid?')">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
