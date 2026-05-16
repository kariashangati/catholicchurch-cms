@extends('layouts.admin')

@section('title', db_trans('financial_summary'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/reports-v4-polish.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 finance-reports-v4">
    <div class="ui-page-hero">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-file-invoice-dollar"></i>{{ db_trans('financial_summary') }}</span>
                <h1 class="ui-page-title mt-3">{{ db_trans('financial_summary') }}</h1>
                <p class="ui-page-subtitle">{{ db_trans('income_vs_expense_and_budget_view') }}</p>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-calendar-alt"></i>{{ db_trans('year') }}: {{ $year }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-sack-dollar"></i>{{ db_trans('income') }}: {{ number_format($totals['income'], 2) }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-scale-balanced"></i>{{ db_trans('balance') }}: {{ number_format($totals['balance'], 2) }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('finance.tithes.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-coins"></i></span><span class="ui-hero-action-text">{{ db_trans('tithes') }}<small>{{ db_trans('open') }}</small></span></a>
                    <a href="{{ route('finance.offerings.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-hand-holding-heart"></i></span><span class="ui-hero-action-text">{{ db_trans('offerings') }}<small>{{ db_trans('open') }}</small></span></a>
                    <a href="{{ route('finance.contributions.cash.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-wallet"></i></span><span class="ui-hero-action-text">{{ db_trans('cash_contributions') }}<small>{{ db_trans('open') }}</small></span></a>
                    <a href="{{ route('finance.contributions.bank.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-university"></i></span><span class="ui-hero-action-text">{{ db_trans('bank_contributions') }}<small>{{ db_trans('open') }}</small></span></a>
                </div>
            </div>
        </div>
    </div>

    @include('admin.finance.reports.partials.filter-bar', ['filters' => ['year' => $year]])

    <div class="row g-4 mb-4 ui-kpi-grid">
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-success border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-arrow-trend-up"></i></div><span class="ui-chip">{{ db_trans('finance') }}</span></div><div class="ui-stat-label">{{ db_trans('income') }}</div><div class="ui-stat-value">{{ number_format($totals['income'], 2) }}</div><div class="ui-stat-meta">{{ db_trans('current_year_breakdown') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-danger border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-arrow-trend-down"></i></div><span class="ui-chip">{{ db_trans('finance') }}</span></div><div class="ui-stat-label">{{ db_trans('expense') }}</div><div class="ui-stat-value">{{ number_format($totals['expense'], 2) }}</div><div class="ui-stat-meta">{{ db_trans('current_year_breakdown') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-primary border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-scale-balanced"></i></div><span class="ui-chip">{{ db_trans('analytics') }}</span></div><div class="ui-stat-label">{{ db_trans('balance') }}</div><div class="ui-stat-value">{{ number_format($totals['balance'], 2) }}</div><div class="ui-stat-meta">{{ db_trans('financial_overview') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-info border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-chart-pie"></i></div><span class="ui-chip">{{ db_trans('budget') }}</span></div><div class="ui-stat-label">{{ db_trans('budget_overview') }}</div><div class="ui-stat-value">{{ number_format(($budget['income_budget'] ?? 0) - ($budget['expense_budget'] ?? 0), 2) }}</div><div class="ui-stat-meta">{{ db_trans('budget_vs_actual') }}</div></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div>
                            <h5 class="ui-panel-title">{{ db_trans('monthly_flow') }}</h5>
                            <p class="ui-panel-subtitle mb-0">{{ db_trans('income_expense_balance') }}</p>
                        </div>
                        <div class="ui-panel-icon"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <div class="ui-chart-shell ui-chart-shell-lg"><canvas id="financialSummaryChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div>
                            <h5 class="ui-panel-title">{{ db_trans('budget_snapshot') }}</h5>
                            <p class="ui-panel-subtitle mb-0">{{ db_trans('budget_vs_actual') }}</p>
                        </div>
                        <div class="ui-panel-icon"><i class="fas fa-wallet"></i></div>
                    </div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('income_budget') }}</span><strong>{{ number_format($budget['income_budget'], 2) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('expense_budget') }}</span><strong>{{ number_format($budget['expense_budget'], 2) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('income_actual_total') }}</span><strong>{{ number_format($totals['income'], 2) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('expense_actual_total') }}</span><strong>{{ number_format($totals['expense'], 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card ui-table-card border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="ui-panel-title mb-1">{{ db_trans('income_breakdown') }}</h5><p class="ui-panel-subtitle mb-0">{{ db_trans('financial_reports') }}</p></div><span class="ui-soft-badge">{{ $income->count() }}</span></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle">
                        <tbody>
                        @foreach($income as $row)
                            <tr>
                                <td><div class="fw-semibold">{{ $row['label'] }}</div></td>
                                <td class="text-end fw-semibold">{{ number_format($row['amount'], 2) }}</td>
                                <td class="text-end"><a href="{{ $row['route'] }}" class="btn btn-sm btn-light">{{ db_trans('open') }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ui-table-card border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="ui-panel-title mb-1">{{ db_trans('expense_breakdown') }}</h5><p class="ui-panel-subtitle mb-0">{{ db_trans('budget_overview') }}</p></div><span class="ui-soft-badge">{{ $expense->count() }}</span></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle">
                        <tbody>
                        @foreach($expense as $row)
                            <tr>
                                <td><div class="fw-semibold">{{ $row['label'] }}</div></td>
                                <td class="text-end fw-semibold">{{ number_format($row['amount'], 2) }}</td>
                                <td class="text-end"><a href="{{ $row['route'] }}" class="btn btn-sm btn-light">{{ db_trans('open') }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="ui-panel-title mb-1">{{ db_trans('monthly_flow') }}</h5><p class="ui-panel-subtitle mb-0">{{ db_trans('financial_summary') }}</p></div><span class="ui-soft-badge">{{ collect($monthly)->count() }}</span></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>{{ db_trans('month') }}</th><th class="text-end">{{ db_trans('income') }}</th><th class="text-end">{{ db_trans('expense') }}</th><th class="text-end">{{ db_trans('balance') }}</th></tr></thead>
                <tbody>
                    @foreach($monthly as $row)
                    <tr>
                        <td>{{ $row['month'] }}</td>
                        <td class="text-end">{{ number_format($row['income'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['expense'], 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) return;
    const ctx = document.getElementById('financialSummaryChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(collect($monthly)->pluck('month')->values()),
            datasets: [
                { label: @json(db_trans('income')), data: @json(collect($monthly)->pluck('income')->map(fn($v)=>(float)$v)->values()), borderWidth: 0 },
                { label: @json(db_trans('expense')), data: @json(collect($monthly)->pluck('expense')->map(fn($v)=>(float)$v)->values()), borderWidth: 0 },
                { label: @json(db_trans('balance')), data: @json(collect($monthly)->pluck('balance')->map(fn($v)=>(float)$v)->values()), type: 'line', tension: .35 }
            ]
        },
        options: { maintainAspectRatio: false, responsive: true }
    });
});
</script>
@endpush
