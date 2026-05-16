@extends('layouts.admin')

@section('title', db_trans('finance_dashboard'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/dashboard-v3.css') }}">
    <style>
        .dashboard-v3 .filter-panel-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            box-shadow: var(--db-shadow-soft);
        }

        .dashboard-v3 .filter-panel-card .form-label {
            font-size: .8rem;
            font-weight: 700;
            color: var(--db-muted);
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .dashboard-v3 .filter-panel-card .form-control,
        .dashboard-v3 .filter-panel-card .form-select {
            min-height: 48px;
            border-radius: 14px;
            border-color: var(--db-border);
            box-shadow: none;
        }

        .dashboard-v3 .filter-panel-card .form-control:focus,
        .dashboard-v3 .filter-panel-card .form-select:focus {
            border-color: rgba(124, 58, 237, .45);
            box-shadow: 0 0 0 .2rem rgba(124, 58, 237, .10);
        }

        .dashboard-v3 .btn-filter-primary {
            min-height: 48px;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
            box-shadow: 0 10px 22px rgba(124, 58, 237, .18);
        }

        .dashboard-v3 .btn-filter-primary:hover,
        .dashboard-v3 .btn-filter-primary:focus {
            color: #fff;
            transform: translateY(-1px);
        }

        .dashboard-v3 .hero-action-subtext {
            display: block;
            font-size: .76rem;
            font-weight: 600;
            color: rgba(255,255,255,.78);
            margin-top: .2rem;
        }

        .dashboard-v3 .finance-health-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .9rem;
        }

        .dashboard-v3 .health-item {
            padding: 1rem;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            border: 1px solid #edf2f7;
        }

        .dashboard-v3 .health-item span {
            display: block;
            font-size: .78rem;
            color: var(--db-muted);
            font-weight: 700;
            margin-bottom: .45rem;
        }

        .dashboard-v3 .health-item strong {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--db-text);
        }

        .dashboard-v3 .bank-health-shell {
            margin-top: 1rem;
            padding: 1rem 1.1rem;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(124,58,237,.08), rgba(14,165,233,.08));
            border: 1px solid rgba(124,58,237,.1);
        }

        .dashboard-v3 .bank-health-title {
            font-weight: 800;
            margin-bottom: .75rem;
            color: #4c1d95;
        }

        .dashboard-v3 .bank-health-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .dashboard-v3 .bank-health-grid div {
            padding: .85rem;
            border-radius: 16px;
            background: rgba(255,255,255,.82);
            border: 1px solid rgba(255,255,255,.65);
        }

        .dashboard-v3 .bank-health-grid span {
            display: block;
            font-size: .76rem;
            color: var(--db-muted);
            font-weight: 700;
            margin-bottom: .25rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .dashboard-v3 .bank-health-grid strong {
            font-size: 1rem;
            font-weight: 800;
            color: var(--db-text);
        }

        .dashboard-v3 .activity-type-badge,
        .dashboard-v3 .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: .35rem .75rem;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .dashboard-v3 .activity-type-badge {
            color: #fff;
        }

        .dashboard-v3 .status-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            text-transform: capitalize;
        }

        .dashboard-v3 .status-approved,
        .dashboard-v3 .status-completed,
        .dashboard-v3 .status-active {
            background: rgba(22, 163, 74, .10);
            border-color: rgba(22, 163, 74, .18);
            color: #15803d;
        }

        .dashboard-v3 .status-pending {
            background: rgba(245, 158, 11, .12);
            border-color: rgba(245, 158, 11, .22);
            color: #b45309;
        }

        .dashboard-v3 .status-rejected,
        .dashboard-v3 .status-cancelled,
        .dashboard-v3 .status-inactive {
            background: rgba(239, 68, 68, .10);
            border-color: rgba(239, 68, 68, .18);
            color: #dc2626;
        }

        .dashboard-v3 .attention-item {
            display: flex;
            gap: .85rem;
            padding: 1rem;
            border-radius: 18px;
            border: 1px solid #edf2f7;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            margin-bottom: .85rem;
        }

        .dashboard-v3 .attention-item:last-child {
            margin-bottom: 0;
        }

        .dashboard-v3 .attention-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .dashboard-v3 .attention-copy {
            min-width: 0;
        }

        .dashboard-v3 .attention-label {
            font-weight: 800;
            color: var(--db-text);
            margin-bottom: .15rem;
        }

        .dashboard-v3 .attention-value {
            font-size: 1rem;
            font-weight: 800;
            color: var(--db-text);
            margin-bottom: .2rem;
        }

        .dashboard-v3 .attention-copy small {
            color: var(--db-muted);
            font-size: .8rem;
        }

        .dashboard-v3 .table-panel .table {
            margin-bottom: 0;
        }

        .dashboard-v3 .table-panel .table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .dashboard-v3 .table-panel .table td {
            vertical-align: middle;
        }

        .dashboard-v3 .chart-shell canvas,
        .dashboard-v3 .chart-shell-lg canvas {
            width: 100% !important;
            height: 100% !important;
        }

        @media (max-width: 767.98px) {
            .dashboard-v3 .bank-health-grid,
            .dashboard-v3 .finance-health-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $year = $filters['year'] ?? now()->year;
        $monthValue = $filters['month'] ?? '';

        $quickLinks = [
            ['label' => db_trans('manage_offerings'), 'route' => Route::has('finance.offerings.index') ? route('finance.offerings.index') : '#', 'icon' => 'fas fa-hand-holding-heart'],
            ['label' => db_trans('tithes_dashboard'), 'route' => Route::has('finance.tithes.dashboard') ? route('finance.tithes.dashboard') : '#', 'icon' => 'fas fa-sack-dollar'],
            ['label' => db_trans('contributions_dashboard'), 'route' => Route::has('finance.contributions.dashboard') ? route('finance.contributions.dashboard') : '#', 'icon' => 'fas fa-coins'],
            ['label' => db_trans('project_finance_dashboard'), 'route' => Route::has('finance.projects.dashboard') ? route('finance.projects.dashboard') : '#', 'icon' => 'fas fa-diagram-project'],
            ['label' => db_trans('budget_estimates'), 'route' => Route::has('finance.budgets.dashboard') ? route('finance.budgets.dashboard') : '#', 'icon' => 'fas fa-scale-balanced'],
            ['label' => db_trans('finance_reports'), 'route' => Route::has('reports.finance.index') ? route('reports.finance.index') : '#', 'icon' => 'fas fa-file-chart-column'],
        ];

        $primaryStats = [
            ['label' => db_trans('total_inflow_this_month'), 'value' => number_format($stats['total_inflow_month'], 2), 'help' => db_trans('all_income_streams_combined'), 'icon' => 'fas fa-calendar-days', 'tone' => 'primary'],
            ['label' => db_trans('total_inflow_this_year'), 'value' => number_format($stats['total_inflow_year'], 2), 'help' => db_trans('parish_wide_financial_strength'), 'icon' => 'fas fa-chart-line', 'tone' => 'info'],
            ['label' => db_trans('approved_total'), 'value' => number_format($stats['approved_total'], 2), 'help' => db_trans('validated_and_closed_records'), 'icon' => 'fas fa-circle-check', 'tone' => 'success'],
            ['label' => db_trans('pending_finance_actions'), 'value' => number_format($stats['pending_count']), 'help' => db_trans('requires_finance_attention'), 'icon' => 'fas fa-hourglass-half', 'tone' => 'warning'],
        ];

        $sourceToneMap = [
            'purple' => 'primary',
            'blue' => 'info',
            'green' => 'success',
            'amber' => 'warning',
            'teal' => 'secondary',
            'rose' => 'danger',
            'red' => 'danger',
            'slate' => 'dark',
            'gray' => 'secondary',
        ];

        $attentionToneMap = [
            'purple' => 'primary',
            'blue' => 'info',
            'green' => 'success',
            'amber' => 'warning',
            'teal' => 'secondary',
            'rose' => 'danger',
            'red' => 'danger',
            'warning' => 'warning',
            'danger' => 'danger',
            'success' => 'success',
            'info' => 'info',
            'primary' => 'primary',
            'secondary' => 'secondary',
            'dark' => 'dark',
        ];
    @endphp

    <div class="dashboard-v3">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>

            <div class="row align-items-center g-4 position-relative">
                <div class="col-xl-8">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="dashboard-hero-badge">{{ db_trans('finance_command_center') }}</span>
                        <span class="dashboard-hero-badge">{{ db_trans('unified_finance_overview') }}</span>
                    </div>

                    <h1 class="dashboard-title mb-2">{{ db_trans('finance_dashboard') }}</h1>

                    <p class="dashboard-subtitle mb-4">
                        {{ db_trans('finance_command_center_description') }}
                    </p>

                    <div class="hero-meta-wrap">
                        <span class="hero-pill">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ db_trans('year') }}: <strong class="ms-1">{{ $hero['current_year'] }}</strong>
                        </span>

                        <span class="hero-pill hero-pill-warning">
                            <i class="fas fa-bell me-2"></i>
                            {{ db_trans('pending_finance_actions') }}:
                            <strong class="ms-1">{{ number_format($hero['pending_items']) }}</strong>
                        </span>

                        <span class="hero-pill">
                            <i class="fas fa-compass me-2"></i>
                            {{ db_trans('selected_scope') }}:
                            <strong class="ms-1">{{ $hero['selected_scope'] }}</strong>
                        </span>

                        <span class="hero-pill">
                            <i class="fas fa-wallet me-2"></i>
                            {{ db_trans('current_month_net') }}:
                            <strong class="ms-1">{{ number_format($hero['current_month_net'], 2) }}</strong>
                        </span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="hero-actions-grid">
                        @foreach($quickLinks as $item)
                            <a href="{{ $item['route'] }}" class="hero-action-btn">
                                <span class="hero-action-icon">
                                    <i class="{{ $item['icon'] }}"></i>
                                </span>
                                <span class="hero-action-text">
                                    {{ $item['label'] }}
                                    <small class="hero-action-subtext">{{ db_trans('open_module') }}</small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-panel-card mb-4">
            <div class="card-body p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3 col-xl-2">
                        <label class="form-label">{{ db_trans('year') }}</label>
                        <input type="number" min="2020" max="2100" class="form-control" name="year" value="{{ $year }}">
                    </div>

                    <div class="col-md-3 col-xl-2">
                        <label class="form-label">{{ db_trans('month') }}</label>
                        <select class="form-select" name="month">
                            <option value="">{{ db_trans('all_months') }}</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected((string) $monthValue === (string) $m)>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3 col-xl-2">
                        <label class="form-label">{{ db_trans('kanda') }}</label>
                        <select class="form-select" name="kanda_id">
                            <option value="">{{ db_trans('all_kandas') }}</option>
                            @foreach($kandas as $kanda)
                                <option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? '') == $kanda->id)>
                                    {{ $kanda->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-xl-2">
                        <label class="form-label">{{ db_trans('jumuiya') }}</label>
                        <select class="form-select" name="jumuiya_id">
                            <option value="">{{ db_trans('all_jumuiyas') }}</option>
                            @foreach($jumuiyas as $jumuiya)
                                <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>
                                    {{ $jumuiya->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-xl-2">
                        <label class="form-label">{{ db_trans('collection_scope') }}</label>
                        <select class="form-select" name="collection_scope">
                            <option value="">{{ db_trans('all_scopes') }}</option>
                            <option value="parish" @selected(($filters['collection_scope'] ?? '') === 'parish')>{{ db_trans('parish') }}</option>
                            <option value="kanda" @selected(($filters['collection_scope'] ?? '') === 'kanda')>{{ db_trans('kanda') }}</option>
                            <option value="jumuiya" @selected(($filters['collection_scope'] ?? '') === 'jumuiya')>{{ db_trans('jumuiya') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-xl-2">
                        <button class="btn btn-filter-primary w-100" type="submit">
                            <i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($primaryStats as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0">
                        <div class="card-body">
                            <div class="stat-top-row">
                                <div class="stat-icon">
                                    <i class="{{ $card['icon'] }}"></i>
                                </div>
                                <span class="stat-chip">{{ db_trans('overview') }}</span>
                            </div>

                            <div class="stat-label">{{ $card['label'] }}</div>
                            <div class="stat-number">{{ $card['value'] }}</div>
                            <div class="stat-meta">{{ $card['help'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            @foreach($sourceCards as $source)
                @php
                    $tone = $sourceToneMap[$source['tone']] ?? 'secondary';
                @endphp
                <div class="col-md-6 col-xl-4 col-xxl-2">
                    <div class="card finance-card finance-card-{{ $tone }} h-100 border-0">
                        <div class="card-body">
                            <div class="finance-card-top">
                                <div class="finance-card-icon">
                                    <i class="{{ $source['icon'] }}"></i>
                                </div>
                                <span class="finance-chip">{{ db_trans('finance') }}</span>
                            </div>

                            <div class="finance-card-title">{{ $source['label'] }}</div>
                            <div class="finance-card-value">{{ number_format($source['month'], 2) }}</div>
                            <div class="finance-card-meta">{{ db_trans('month_total') }}</div>

                            <div class="mt-3 pt-3 border-top">
                                <div class="stat-label mb-1">{{ db_trans('year_total') }}</div>
                                <div class="stat-number fs-4">{{ number_format($source['year'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card analytics-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('monthly_finance_trend') }}</h5>
                                <p class="panel-subtitle">{{ db_trans('compare_all_major_finance_streams_by_month') }}</p>
                            </div>
                            <span class="section-badge">{{ $year }}</span>
                        </div>

                        <div class="chart-shell chart-shell-lg">
                            <canvas id="financeMonthlyTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card analytics-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('income_streams') }}</h5>
                                <p class="panel-subtitle">{{ db_trans('yearly_mix_of_income_sources') }}</p>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>

                        <div class="chart-shell">
                            <canvas id="financeMixChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="card analytics-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('budget_vs_actual') }}</h5>
                                <p class="panel-subtitle">{{ db_trans('compare_budget_targets_to_actual_results') }}</p>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-column"></i>
                            </div>
                        </div>

                        <div class="chart-shell">
                            <canvas id="budgetVsActualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card quick-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('finance_health_score') }}</h5>
                                <p class="panel-subtitle">{{ db_trans('central_command_snapshot') }}</p>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-heart-pulse"></i>
                            </div>
                        </div>

                        <div class="finance-health-grid">
                            <div class="health-item">
                                <span>{{ db_trans('average_record') }}</span>
                                <strong>{{ number_format($stats['average_record'], 2) }}</strong>
                            </div>
                            <div class="health-item">
                                <span>{{ db_trans('project_expense') }}</span>
                                <strong>{{ number_format($stats['project_expense_year'], 2) }}</strong>
                            </div>
                            <div class="health-item">
                                <span>{{ db_trans('budgeted_income') }}</span>
                                <strong>{{ number_format($stats['budget_income_year'], 2) }}</strong>
                            </div>
                            <div class="health-item">
                                <span>{{ db_trans('budgeted_expense') }}</span>
                                <strong>{{ number_format($stats['budget_expense_year'], 2) }}</strong>
                            </div>
                        </div>

                        <div class="bank-health-shell">
                            <div class="bank-health-title">{{ db_trans('bank_account_health') }}</div>
                            <div class="bank-health-grid">
                                <div>
                                    <span>{{ db_trans('active') }}</span>
                                    <strong>{{ $bankHealth['active_accounts'] }}</strong>
                                </div>
                                <div>
                                    <span>{{ db_trans('inactive') }}</span>
                                    <strong>{{ $bankHealth['inactive_accounts'] }}</strong>
                                </div>
                                <div>
                                    <span>{{ db_trans('closed') }}</span>
                                    <strong>{{ $bankHealth['closed_accounts'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="card table-panel h-100 border-0">
                    <div class="card-header table-panel-header border-0 p-4">
                        <div>
                            <h5 class="panel-title mb-1">{{ db_trans('top_finance_kandas') }}</h5>
                            <div class="panel-subtitle">{{ db_trans('highest_combined_income_totals') }}</div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive dashboard-table-wrap">
                            <table class="table align-middle table-hover mb-0 dashboard-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('kanda') }}</th>
                                        <th class="text-end">{{ db_trans('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topKandas as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card table-panel h-100 border-0">
                    <div class="card-header table-panel-header border-0 p-4">
                        <div>
                            <h5 class="panel-title mb-1">{{ db_trans('top_finance_jumuiyas') }}</h5>
                            <div class="panel-subtitle">{{ db_trans('highest_combined_income_totals') }}</div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive dashboard-table-wrap">
                            <table class="table align-middle table-hover mb-0 dashboard-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('jumuiya') }}</th>
                                        <th class="text-end">{{ db_trans('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topJumuiyas as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-side-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head mb-4">
                            <div>
                                <h5 class="panel-title">{{ db_trans('finance_attention_items') }}</h5>
                                <p class="panel-subtitle">{{ db_trans('actionable_items_for_finance_team') }}</p>
                            </div>
                        </div>

                        <div class="attention-list">
                            @foreach($attentionItems as $item)
                                @php
                                    $tone = $attentionToneMap[$item['tone']] ?? 'secondary';
                                @endphp
                                <div class="attention-item">
                                    <span class="attention-icon tone-{{ $tone }}">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </span>

                                    <div class="attention-copy">
                                        <div class="attention-label">{{ $item['label'] }}</div>

                                        <div class="attention-value">
                                            @if(is_numeric($item['value']))
                                                {{ str_contains((string) $item['value'], '.') ? number_format($item['value'], 2) : number_format($item['value']) }}
                                            @else
                                                {{ $item['value'] }}
                                            @endif
                                        </div>

                                        <small>{{ $item['hint'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card table-panel h-100 border-0">
                    <div class="card-header table-panel-header border-0 p-4">
                        <div>
                            <h5 class="panel-title mb-1">{{ db_trans('unified_finance_activity') }}</h5>
                            <div class="panel-subtitle">{{ db_trans('latest_income_and_transaction_records') }}</div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive dashboard-table-wrap">
                            <table class="table align-middle table-hover mb-0 dashboard-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('type') }}</th>
                                        <th>{{ db_trans('description') }}</th>
                                        <th>{{ db_trans('location') }}</th>
                                        <th>{{ db_trans('date') }}</th>
                                        <th>{{ db_trans('status') }}</th>
                                        <th class="text-end">{{ db_trans('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivities as $item)
                                        <tr>
                                            <td>
                                                @php
                                                    $tone = $attentionToneMap[$item['tone']] ?? 'secondary';
                                                @endphp
                                                <span class="activity-type-badge tone-{{ $tone }}">
                                                    {{ $item['type'] }}
                                                </span>
                                            </td>
                                            <td>{{ $item['title'] }}</td>
                                            <td>{{ $item['meta'] }}</td>
                                            <td>{{ optional($item['date'])->format('M d, Y') ?? '—' }}</td>
                                            <td>
                                                <span class="status-pill status-{{ strtolower((string) $item['status']) }}">
                                                    {{ db_trans((string) $item['status']) }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ number_format($item['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card table-panel h-100 border-0">
                    <div class="card-header table-panel-header border-0 p-4">
                        <div>
                            <h5 class="panel-title mb-1">{{ db_trans('offering_type_summary') }}</h5>
                            <div class="panel-subtitle">{{ db_trans('offering_breakdown_supporting_the_command_center') }}</div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive dashboard-table-wrap">
                            <table class="table align-middle table-hover mb-0 dashboard-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('offering_type') }}</th>
                                        <th class="text-end">{{ db_trans('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($typeBreakdown as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const palette = {
            blue: '#3b82f6',
            green: '#22c55e',
            purple: '#7c3aed',
            amber: '#f59e0b',
            teal: '#14b8a6',
            rose: '#f43f5e',
            slate: '#94a3b8'
        };

        const monthlyCtx = document.getElementById('financeMonthlyTrendChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthlyChart['labels']),
                    datasets: [
                        { label: @json(db_trans('offerings')), data: @json($monthlyChart['datasets']['offerings']), backgroundColor: palette.purple, borderRadius: 10 },
                        { label: @json(db_trans('tithes')), data: @json($monthlyChart['datasets']['tithes']), backgroundColor: palette.blue, borderRadius: 10 },
                        { label: @json(db_trans('cash_contributions')), data: @json($monthlyChart['datasets']['cash']), backgroundColor: palette.green, borderRadius: 10 },
                        { label: @json(db_trans('bank_contributions')), data: @json($monthlyChart['datasets']['bank']), backgroundColor: palette.amber, borderRadius: 10 },
                        { label: @json(db_trans('project_income')), data: @json($monthlyChart['datasets']['projects']), backgroundColor: palette.teal, borderRadius: 10 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => Number(value).toLocaleString()
                            }
                        }
                    }
                }
            });
        }

        const mixCtx = document.getElementById('financeMixChart');
        if (mixCtx) {
            new Chart(mixCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($sourceMixChart['labels']),
                    datasets: [{
                        data: @json($sourceMixChart['amounts']),
                        backgroundColor: [palette.purple, palette.blue, palette.green, palette.amber, palette.teal],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        const budgetCtx = document.getElementById('budgetVsActualChart');
        if (budgetCtx) {
            new Chart(budgetCtx, {
                type: 'bar',
                data: {
                    labels: @json($budgetVsActualChart['labels']),
                    datasets: [
                        { label: @json(db_trans('budget')), data: @json($budgetVsActualChart['budget']), backgroundColor: palette.slate, borderRadius: 10 },
                        { label: @json(db_trans('actual')), data: @json($budgetVsActualChart['actual']), backgroundColor: palette.blue, borderRadius: 10 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => Number(value).toLocaleString()
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush