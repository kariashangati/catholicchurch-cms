@extends('layouts.admin')

@section('title', db_trans('financial_summary'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $filters = $filters ?? [];
    $filterData = $filterData ?? [];
    $incomeItems = collect($incomeItems ?? []);
    $expenseItems = collect($expenseItems ?? []);
    $stats = $stats ?? [];
    $monthlyTrend = $monthlyTrend ?? ['labels' => [], 'income' => [], 'expenses' => [], 'balances' => []];
    $contributionTypeBreakdown = $contributionTypeBreakdown ?? ['rows' => collect(), 'labels' => [], 'amounts' => []];
    $breakdownRows = collect($contributionTypeBreakdown['rows'] ?? []);
    $currency = fn ($amount) => number_format((float) $amount, 2);
    $balance = (float) data_get($stats, 'balance', 0);
    $exportQuery = array_filter($filters, fn ($value) => filled($value) || $value === 0 || $value === '0');
@endphp

<div class="admin-ui-v4 contributions-page-v4 finance-summary-report-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-file-invoice-dollar"></i>{{ db_trans('financial_summary') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('financial_summary') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-calendar"></i>{{ optional($startDate)->format('d M Y') }} - {{ optional($endDate)->format('d M Y') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-arrow-trend-up"></i>{{ db_trans('total_income') }}: {{ $currency(data_get($stats, 'income_total', 0)) }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-scale-balanced"></i>{{ db_trans('balance') }}: {{ $currency($balance) }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('pdf.finance.reports.financial-summary.export', $exportQuery) }}" target="_blank" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-file-pdf"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('export_pdf') }}</span>
                    </a>

                    <a href="{{ route('finance.reports.financial-summary.export.excel', $exportQuery) }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-file-excel"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('export_excel') }}</span>
                    </a>

                    <a href="{{ Route::has('finance.contributions.dashboard') ? route('finance.contributions.dashboard') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('michango_dashboard') }}</span>
                    </a>

                    <a href="{{ Route::has('finance.budgets.dashboard') ? route('finance.budgets.dashboard') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('budgets') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('finance.reports.financial-summary') }}" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <input type="number" min="2000" max="2100" name="year" class="form-control" value="{{ $filters['year'] ?? now()->year }}">
                </div>

                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select">
                        <option value="">{{ db_trans('all_months') }}</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" @selected((string) ($filters['month'] ?? '') === (string) $month)>
                                {{ \Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" id="financeReportKanda" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach(collect($filterData['kandas'] ?? []) as $kanda)
                            <option value="{{ $kanda->id }}" @selected((string) ($filters['kanda_id'] ?? '') === (string) $kanda->id)>{{ $kanda->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" id="financeReportJumuiya" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach(collect($filterData['jumuiyas'] ?? []) as $jumuiya)
                            <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}" @selected((string) ($filters['jumuiya_id'] ?? '') === (string) $jumuiya->id)>{{ $jumuiya->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('contribution_type') }}</label>
                    <select name="contribution_type_id" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach(collect($filterData['contributionTypes'] ?? []) as $type)
                            <option value="{{ $type->id }}" @selected((string) ($filters['contribution_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn ui-btn-primary w-100"><i class="fas fa-filter me-1"></i>{{ db_trans('filter_records') }}</button>
                    <a href="{{ route('finance.reports.financial-summary') }}" class="btn ui-btn-light w-100">{{ db_trans('reset') }}</a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-success p-4 h-100">
                <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-arrow-trend-up"></i></span><span class="ui-chip">{{ db_trans('income') }}</span></div>
                <div class="ui-stat-label">{{ db_trans('total_income') }}</div>
                <div class="ui-stat-value">{{ $currency(data_get($stats, 'income_total', 0)) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-danger p-4 h-100">
                <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-arrow-trend-down"></i></span><span class="ui-chip">{{ db_trans('expense') }}</span></div>
                <div class="ui-stat-label">{{ db_trans('total_expense') }}</div>
                <div class="ui-stat-value">{{ $currency(data_get($stats, 'expense_total', 0)) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card {{ $balance >= 0 ? 'ui-tone-primary' : 'ui-tone-warning' }} p-4 h-100">
                <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-scale-balanced"></i></span><span class="ui-chip">{{ db_trans('balance') }}</span></div>
                <div class="ui-stat-label">{{ db_trans('balance') }}</div>
                <div class="ui-stat-value">{{ $currency($balance) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-info p-4 h-100">
                <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-receipt"></i></span><span class="ui-chip">{{ db_trans('records') }}</span></div>
                <div class="ui-stat-label">{{ db_trans('records') }}</div>
                <div class="ui-stat-value">{{ number_format((int) data_get($stats, 'income_records', 0) + (int) data_get($stats, 'expense_records', 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="ui-panel p-4 h-100">
                <div class="ui-panel-head">
                    <div><h5 class="mb-1">{{ db_trans('monthly_finance_trend') }}</h5></div>
                    <span class="ui-panel-icon"><i class="fas fa-chart-line"></i></span>
                </div>
                <div class="ui-chart-shell"><canvas id="financeSummaryTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="ui-panel p-4 h-100">
                <div class="ui-panel-head">
                    <div><h5 class="mb-1">{{ db_trans('income_breakdown') }}</h5></div>
                    <span class="ui-panel-icon"><i class="fas fa-chart-pie"></i></span>
                </div>
                <div class="ui-chart-shell ui-chart-shell-sm"><canvas id="financeIncomeChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><h5 class="mb-1">{{ db_trans('income_breakdown') }}</h5></div>
                <div class="table-responsive">
                    <table class="table align-middle" id="incomeBreakdownTable">
                        <thead><tr><th>{{ db_trans('source') }}</th><th class="text-end">{{ db_trans('amount') }}</th></tr></thead>
                        <tbody>
                            @foreach($incomeItems as $item)
                                <tr><td>{{ $item['label'] }}</td><td class="text-end ui-amount">{{ $currency($item['amount']) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><th>{{ db_trans('total_income') }}</th><th class="text-end">{{ $currency(data_get($stats, 'income_total', 0)) }}</th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><h5 class="mb-1">{{ db_trans('expense_breakdown') }}</h5></div>
                <div class="table-responsive">
                    <table class="table align-middle" id="expenseBreakdownTable">
                        <thead><tr><th>{{ db_trans('source') }}</th><th class="text-end">{{ db_trans('amount') }}</th></tr></thead>
                        <tbody>
                            @foreach($expenseItems as $item)
                                <tr><td>{{ $item['label'] }}</td><td class="text-end ui-amount">{{ $currency($item['amount']) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><th>{{ db_trans('total_expense') }}</th><th class="text-end">{{ $currency(data_get($stats, 'expense_total', 0)) }}</th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4 mb-4">
        <div class="ui-section-heading">
            <div><h5 class="mb-1">{{ db_trans('contribution_type_breakdown') }}</h5></div>
            <span class="ui-section-badge">{{ number_format($breakdownRows->count()) }} {{ db_trans('records') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle" id="contributionTypeBreakdownTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('contribution_type') }}</th>
                        <th class="text-end">{{ db_trans('cash') }}</th>
                        <th class="text-end">{{ db_trans('bank') }}</th>
                        <th class="text-end">{{ db_trans('total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($breakdownRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ data_get($row, 'name', '—') }}</td>
                            <td class="text-end ui-amount">{{ $currency(data_get($row, 'cash_total', 0)) }}</td>
                            <td class="text-end ui-amount">{{ $currency(data_get($row, 'bank_total', 0)) }}</td>
                            <td class="text-end ui-amount">{{ $currency(data_get($row, 'total', 0)) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kandaSelect = document.getElementById('financeReportKanda');
    const jumuiyaSelect = document.getElementById('financeReportJumuiya');

    const syncJumuiyas = function () {
        if (!kandaSelect || !jumuiyaSelect) return;
        const kanda = kandaSelect.value;
        Array.from(jumuiyaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = !!kanda && option.dataset.kanda !== kanda;
        });
        const selected = jumuiyaSelect.selectedOptions[0];
        if (selected && selected.hidden) jumuiyaSelect.value = '';
    };

    syncJumuiyas();
    if (kandaSelect) kandaSelect.addEventListener('change', syncJumuiyas);

    if (window.jQuery && $.fn.DataTable) {
        ['#incomeBreakdownTable', '#expenseBreakdownTable', '#contributionTypeBreakdownTable'].forEach(function (selector) {
            if ($(selector).length) {
                $(selector).DataTable({
                    paging: true,
                    info: true,
                    searching: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                    order: [],
                    autoWidth: false,
                    language: {
                        search: @json(db_trans('search')) + ':',
                        lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                        info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
                        infoEmpty: @json(db_trans('no_records_found')),
                        zeroRecords: @json(db_trans('no_records_found')),
                        paginate: { previous: '‹', next: '›' }
                    }
                });
            }
        });
    }

    if (typeof Chart !== 'undefined') {
        const trendCanvas = document.getElementById('financeSummaryTrendChart');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'bar',
                data: {
                    labels: @json($monthlyTrend['labels'] ?? []),
                    datasets: [
                        { label: @json(db_trans('income')), data: @json($monthlyTrend['income'] ?? []), borderWidth: 2, borderRadius: 8, type: 'bar' },
                        { label: @json(db_trans('expense')), data: @json($monthlyTrend['expenses'] ?? []), borderWidth: 2, borderRadius: 8, type: 'bar' },
                        { label: @json(db_trans('balance')), data: @json($monthlyTrend['balances'] ?? []), borderWidth: 3, tension: 0.35, type: 'line' }
                    ]
                },
                options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } }
            });
        }

        const incomeCanvas = document.getElementById('financeIncomeChart');
        if (incomeCanvas) {
            new Chart(incomeCanvas, {
                type: 'doughnut',
                data: {
                    labels: @json($incomeItems->pluck('label')->values()),
                    datasets: [{ data: @json($incomeItems->pluck('amount')->values()), borderWidth: 2 }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }
    }
});
</script>
@endpush