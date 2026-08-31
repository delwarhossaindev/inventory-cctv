@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@push('styles')
<style>
    .dash-title { font-size: 1.6rem; font-weight: 700; }
    .stat-card { padding: 1.25rem; height: 100%; display: block; }
    .stat-link { transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease; cursor: pointer; }
    .stat-link:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(0,0,0,.08); border-color: var(--brand); }
    .stat-card .label { color: var(--muted); font-size: .85rem; }
    .stat-card .value { font-size: 1.6rem; font-weight: 700; margin-top: .35rem; }
    .stat-card .sub { color: var(--muted); font-size: .8rem; }
    .stat-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .trend { font-size: .78rem; font-weight: 600; padding: .12rem .45rem; border-radius: 6px; }
    .trend.up { color: #16a34a; background: rgba(22,163,74,.12); }
    .trend.down { color: #dc2626; background: rgba(220,38,38,.12); }
    .card-title-sm { font-weight: 700; }
    .card-sub { color: var(--muted); font-size: .82rem; }
    .empty-state { color: var(--muted); }
</style>
@endpush

@section('content')
<div class="mb-4">
    <div class="dash-title">Dashboard</div>
    <div class="card-sub">Real-time overview of your inventory, stock movement, and key alerts.</div>
</div>

{{-- ---------- Stat cards ---------- --}}
@php
    $statCards = [
        ['Total products', number_format($stats['products']), 'bi-box-seam', '#2563eb', 'rgba(37,99,235,.12)', $stats['categories'] . ' categories', route('admin.products.index')],
        ['Stock value', '৳ ' . number_format($stats['stock_value'], 0), 'bi-graph-up-arrow', '#16a34a', 'rgba(22,163,74,.12)', 'on-hand inventory', route('admin.stock.index')],
        ['Today\'s sales', '৳ ' . number_format($stats['today_sales'], 0), 'bi-cash-coin', '#0ea5e9', 'rgba(14,165,233,.12)', $stats['today_sales_count'] . ' invoices today', route('admin.sales.index')],
        ['Low stock', number_format($stats['low_stock']), 'bi-exclamation-triangle', '#f59e0b', 'rgba(245,158,11,.14)', 'items need restock', route('admin.stock.index')],
    ];
@endphp
<div class="row g-3 mb-3">
    @foreach ($statCards as [$label, $value, $icon, $color, $bg, $sub, $url])
        <div class="col-6 col-xl-3">
            <a href="{{ $url }}" class="card stat-card stat-link text-decoration-none text-reset">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="label">{{ $label }}</div>
                    <span class="stat-icon" style="background:{{ $bg }};color:{{ $color }}"><i class="bi {{ $icon }}"></i></span>
                </div>
                <div class="value">{{ $value }}</div>
                <div class="sub">{{ $sub }}</div>
            </a>
        </div>
    @endforeach
</div>

{{-- ---------- Charts row ---------- --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title-sm">Stock movement</div>
                <div class="card-sub mb-3">Sales — last 7 days</div>
                <canvas id="salesChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title-sm">Top categories</div>
                <div class="card-sub mb-3">By sales value</div>
                @if (count($pieLabels))
                    <canvas id="catChart" height="200"></canvas>
                @else
                    <div class="text-center py-5 empty-state">No sales data yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ---------- Bottom row ---------- --}}
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-title-sm text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Low stock alerts</div>
                        <div class="card-sub">Items below minimum threshold</div>
                    </div>
                    <a href="{{ route('admin.stock.index') }}" class="small text-decoration-none">View all</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <tbody>
                        @forelse ($lowStock as $p)
                            <tr>
                                <td><a href="{{ route('admin.stock.adjust', $p) }}" class="text-decoration-none">{{ $p->name }}</a></td>
                                <td class="text-end"><span class="badge bg-danger-subtle text-danger">{{ $p->stock_quantity }} left</span></td>
                                <td class="text-end card-sub">min {{ $p->alert_quantity }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center py-5 empty-state">All products in stock 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-title-sm"><i class="bi bi-receipt me-1"></i>Recent sales</div>
                        <div class="card-sub">Latest invoices</div>
                    </div>
                    <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-cart-check me-1"></i>New sale</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr class="card-sub"><th>Invoice</th><th>Customer</th><th class="text-end">Total</th><th class="text-end">Date</th></tr></thead>
                    <tbody>
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('admin.sales.show', $sale) }}" class="text-decoration-none">{{ $sale->invoice_no }}</a></td>
                                <td class="small">{{ optional($sale->customer)->name ?: 'Walk-in' }}</td>
                                <td class="text-end">@money($sale->total)</td>
                                <td class="text-end card-sub">{{ $sale->sale_date?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 empty-state">No sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    var muted = getComputedStyle(document.documentElement).getPropertyValue('--muted') || '#8a93a2';
    var grid = getComputedStyle(document.documentElement).getPropertyValue('--border') || '#eceef1';

    // Stock movement — filled area chart
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Sales (৳)',
                data: @json($chartData),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.12)',
                fill: true, tension: .4, pointRadius: 3, pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: grid }, ticks: { color: muted } },
                x: { grid: { display: false }, ticks: { color: muted } }
            }
        }
    });

    @if (count($pieLabels))
    // Top categories — horizontal bar
    new Chart(document.getElementById('catChart'), {
        type: 'bar',
        data: {
            labels: @json($pieLabels),
            datasets: [{
                data: @json($pieData),
                backgroundColor: '#2563eb', borderRadius: 5, barThickness: 16
            }]
        },
        options: {
            indexAxis: 'y', responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: grid }, ticks: { color: muted } },
                y: { grid: { display: false }, ticks: { color: muted } }
            }
        }
    });
    @endif
})();
</script>
@endpush
