@extends('layouts.admin')

@section('title', $pageTitle)

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="admin-ui-v4 tithe-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <span class="ui-page-badge">{{ $hero['eyebrow'] }}</span>
                <h1 class="ui-page-title">{{ $hero['title'] }}</h1>
                <p class="ui-page-subtitle">{{ $hero['subtitle'] }}</p>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-compass"></i>{{ $hero['scope_badge'] }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-bell"></i>{{ $hero['pending_items'] }} {{ db_trans('pending_finance_records') }}</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('finance.tithes.index') }}?open_create=1" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('record_tithe') }}<small>{{ db_trans('single_entry') }}</small></span>
                    </a>
                    <a href="{{ route('finance.tithes.bulk.entry') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('bulk_tithe_entry') }}<small>{{ db_trans('bulk_entry_workspace') }}</small></span>
                    </a>
                    <a href="{{ route('finance.tithes.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-table"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('manage_tithes') }}<small>{{ db_trans('records_and_updates') }}</small></span>
                    </a>
                    <a href="{{ route('finance.tithes.duplicates.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-shield-halved"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('duplicate_review') }}<small>{{ db_trans('restricted_review_page') }}</small></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($summaryCards as $card)
            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="card ui-stat-card ui-tone-{{ $card['tone'] }} h-100 border-0">
                    <div class="card-body p-4">
                        <div class="ui-stat-top">
                            <div class="ui-stat-icon"><i class="{{ $card['icon'] }}"></i></div>
                            <span class="ui-chip">{{ db_trans('overview') }}</span>
                        </div>
                        <div class="ui-stat-label">{{ $card['title'] }}</div>
                        <div class="ui-stat-value">{{ $card['value'] }}</div>
                        <div class="ui-stat-meta">{{ $card['meta'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body p-4">
                    <div class="ui-section-heading">
                        <div>
                            <h5 class="ui-section-title">{{ db_trans('tithe_trend') }}</h5>
                            <p class="ui-section-subtitle">{{ db_trans('monthly_tithe_amount_and_members') }}</p>
                        </div>
                        <span class="ui-section-badge">{{ now()->year }}</span>
                    </div>
                    <div class="ui-chart-shell ui-chart-shell-lg tithe-chart-wrap">
                        <canvas id="titheTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body p-4">
                    <div class="ui-section-heading">
                        <div>
                            <h5 class="ui-section-title">{{ db_trans('status_breakdown') }}</h5>
                            <p class="ui-section-subtitle">{{ db_trans('analytics') }}</p>
                        </div>
                    </div>
                    <div class="ui-chart-shell tithe-chart-wrap tithe-chart-wrap-sm">
                        <canvas id="titheStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card ui-table-card border-0 h-100"><div class="card-body p-4">
                <div class="ui-section-heading"><div><h5 class="ui-section-title">{{ db_trans('top_jumuiyas') }} — {{ db_trans('year') }}</h5></div></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>{{ db_trans('jumuiya') }}</th><th>{{ db_trans('amount') }}</th></tr></thead>
                        <tbody>
                        @forelse($topJumuiyasYear as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_amount, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
        <div class="col-xl-6">
            <div class="card ui-table-card border-0 h-100"><div class="card-body p-4">
                <div class="ui-section-heading"><div><h5 class="ui-section-title">{{ db_trans('top_kandas') }}</h5></div></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>{{ db_trans('kanda') }}</th><th>{{ db_trans('amount') }}</th></tr></thead>
                        <tbody>
                        @forelse($topKandas as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_amount, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card ui-table-card border-0"><div class="card-body p-4">
                <div class="ui-section-heading"><div><h5 class="ui-section-title">{{ db_trans('recent_tithes') }}</h5></div></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="recentTithesTable">
                        <thead><tr><th>{{ db_trans('date') }}</th><th>{{ db_trans('member') }}</th><th>{{ db_trans('jumuiya') }}</th><th>{{ db_trans('amount') }}</th><th>{{ db_trans('status') }}</th></tr></thead>
                        <tbody>
                        @foreach($recentTithes as $item)
                            <tr>
                                <td data-order="{{ optional($item->contribution_date)->format('Y-m-d') }}">{{ optional($item->contribution_date)->format('d/m/Y') }}</td>
                                <td>{{ $item->member?->full_name ?? '—' }}</td>
                                <td>{{ $item->jumuiya?->name ?? '—' }}</td>
                                <td>{{ number_format($item->amount,2) }}</td>
                                <td><span class="ui-status-pill ui-status-{{ $item->status }}">{{ $item->status }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
        <div class="col-xl-4">
            <div class="card ui-side-card border-0"><div class="card-body p-4">
                <div class="ui-section-heading"><div><h5 class="ui-section-title">{{ db_trans('important') }}</h5></div></div>
                <div class="ui-attention-item">
                    <div class="ui-attention-icon ui-mini-tone-warning"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="ui-attention-copy">
                        <div class="ui-attention-label">{{ db_trans('duplicate_review') }}</div>
                        <div class="ui-attention-value">{{ $stats['duplicate_monthly_members'] }}</div>
                        <small>{{ db_trans('duplicate_monthly_entries_need_review') }}</small>
                    </div>
                </div>
                <a href="{{ route('finance.tithes.duplicates.index') }}" class="ui-quick-link mt-3">
                    <span class="ui-quick-link-left"><span class="ui-quick-link-icon"><i class="fas fa-shield-halved"></i></span><span class="ui-quick-link-label">{{ db_trans('open_duplicate_review') }}</span></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && document.getElementById('recentTithesTable')) {
        $('#recentTithesTable').DataTable({pageLength:8,lengthChange:false,order:[[0,'desc']]});
    }

    if (window.Chart) {
        const trendCtx = document.getElementById('titheTrendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthlyChart['labels']),
                    datasets: [
                        { label: '{{ db_trans('amount') }}', data: @json($monthlyChart['amounts']), backgroundColor: '#0284c7', borderRadius: 10, maxBarThickness: 36 },
                        { label: '{{ db_trans('members_paid') }}', data: @json($monthlyChart['members']), type: 'line', borderColor: '#16a34a', backgroundColor: '#16a34a', tension: 0.35, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: { beginAtZero: true },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                    }
                }
            });
        }

        const statusCtx = document.getElementById('titheStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($statusChart['labels']),
                    datasets: [{
                        data: @json($statusChart['amounts']),
                        backgroundColor: ['#16a34a', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }
    }
});
</script>
@endpush
