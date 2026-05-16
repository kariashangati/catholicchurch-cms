@extends('layouts.admin')

@section('title', db_trans('project_finance_dashboard'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
@php
    $stats = $stats ?? [];
    $monthlyChart = $monthlyChart ?? ['labels' => collect(), 'income' => collect(), 'expense' => collect()];
    $categoryChart = $categoryChart ?? ['labels' => collect(), 'amounts' => collect()];
    $topProjects = collect($topProjects ?? []);
    $filters = $filters ?? [];

    $currency = fn ($value) => number_format((float) $value, 2);
    $monthNet = (float) ($stats['month_net'] ?? 0);
    $yearNet = (float) ($stats['year_net'] ?? 0);

    $riskProjects = $topProjects
        ->filter(fn ($project) => ((float) ($project->expense ?? 0)) > ((float) ($project->income ?? 0)))
        ->count();

    $healthyProjects = $topProjects
        ->filter(fn ($project) => ((float) ($project->net ?? 0)) >= 0)
        ->count();

    $summaryCards = [
        ['label' => db_trans('month_income'), 'value' => $currency($stats['month_income'] ?? 0), 'icon' => 'fas fa-arrow-trend-up', 'tone' => 'success'],
        ['label' => db_trans('month_expense'), 'value' => $currency($stats['month_expense'] ?? 0), 'icon' => 'fas fa-arrow-trend-down', 'tone' => 'danger'],
        ['label' => db_trans('month_net'), 'value' => $currency($monthNet), 'icon' => 'fas fa-scale-balanced', 'tone' => $monthNet >= 0 ? 'info' : 'warning'],
        ['label' => db_trans('year_income'), 'value' => $currency($stats['year_income'] ?? 0), 'icon' => 'fas fa-sack-dollar', 'tone' => 'primary'],
        ['label' => db_trans('year_expense'), 'value' => $currency($stats['year_expense'] ?? 0), 'icon' => 'fas fa-file-invoice-dollar', 'tone' => 'warning'],
        ['label' => db_trans('year_net'), 'value' => $currency($yearNet), 'icon' => 'fas fa-wallet', 'tone' => $yearNet >= 0 ? 'success' : 'danger'],
    ];

    $miniCards = [
        ['label' => db_trans('total_projects'), 'value' => number_format((int) ($stats['project_count'] ?? 0)), 'icon' => 'fas fa-diagram-project', 'tone' => 'primary'],
        ['label' => db_trans('active_projects'), 'value' => number_format((int) ($stats['active_projects'] ?? 0)), 'icon' => 'fas fa-bolt', 'tone' => 'success'],
        ['label' => db_trans('completed_projects'), 'value' => number_format((int) ($stats['completed_projects'] ?? 0)), 'icon' => 'fas fa-circle-check', 'tone' => 'info'],
        ['label' => db_trans('pending_transactions'), 'value' => number_format((int) ($stats['pending_transactions'] ?? 0)), 'icon' => 'fas fa-clock', 'tone' => 'warning'],
        ['label' => db_trans('healthy_projects'), 'value' => number_format($healthyProjects), 'icon' => 'fas fa-heart-circle-check', 'tone' => 'secondary'],
        ['label' => db_trans('projects_needing_attention'), 'value' => number_format($riskProjects), 'icon' => 'fas fa-triangle-exclamation', 'tone' => 'danger'],
    ];
@endphp

<div class="admin-ui-v4 project-finance-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>

        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-7">
                <span class="ui-page-badge">
                    <i class="fas fa-diagram-project"></i>{{ db_trans('project_finance_overview') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-0">{{ db_trans('project_finance_dashboard') }}</h1>

                <div class="ui-meta-wrap mt-4">
                    <span class="ui-meta-pill">
                        <i class="fas fa-calendar"></i>{{ $filters['year'] ?? now()->year }}
                    </span>
                    <span class="ui-meta-pill">
                        <i class="fas fa-list-check"></i>{{ number_format((int) ($stats['project_count'] ?? 0)) }} {{ db_trans('projects') }}
                    </span>
                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-bell"></i>{{ number_format((int) ($stats['pending_transactions'] ?? 0)) }} {{ db_trans('pending_transactions') }}
                    </span>
                </div>
            </div>

            <div class="col-xl-5">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label text-white">{{ db_trans('year') }}</label>
                        <select name="year" class="form-select">
                            @for($yr = now()->year; $yr >= 2020; $yr--)
                                <option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>
                                    {{ $yr }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label text-white">{{ db_trans('month') }}</label>
                        <select name="month" class="form-select">
                            <option value="">{{ db_trans('all_months') }}</option>
                            @foreach(range(1, 12) as $monthNumber)
                                <option value="{{ $monthNumber }}" @selected(($filters['month'] ?? '') == $monthNumber)>
                                    {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn ui-btn-light w-100">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </form>

                <div class="ui-actions-grid mt-3">
                    <a href="{{ route('finance.projects.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-table-list"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('manage_projects') }}</span>
                    </a>

                    <a href="{{ route('finance.projects.index') }}#transaction-create-modal" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('add_project_transaction') }}</span>
                    </a>

                    <a href="{{ route('finance.projects.index') }}#project-create-modal" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-folder-plus"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('add_project') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($summaryCards as $card)
            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="card ui-stat-card ui-tone-{{ $card['tone'] }} h-100 border-0">
                    <div class="card-body">
                        <div class="ui-stat-top">
                            <span class="ui-stat-icon"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                        <div class="ui-stat-label">{{ $card['label'] }}</div>
                        <div class="ui-stat-value">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        @foreach($miniCards as $card)
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card ui-mini-card border-0 h-100">
                    <div class="card-body">
                        <div class="ui-mini-icon ui-mini-tone-{{ $card['tone'] }}">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="ui-mini-label">{{ $card['label'] }}</div>
                        <div class="ui-mini-value">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body p-4">
                    <div class="ui-panel-head">
                        <div>
                            <h5 class="ui-panel-title">{{ db_trans('monthly_project_trend') }}</h5>
                        </div>
                        <span class="ui-section-badge">
                            <i class="fas fa-chart-line"></i>{{ $filters['year'] ?? now()->year }}
                        </span>
                    </div>

                    <div class="ui-chart-shell ui-chart-shell-lg">
                        <canvas id="projectMonthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body p-4">
                    <div class="ui-panel-head">
                        <div>
                            <h5 class="ui-panel-title">{{ db_trans('category_income_breakdown') }}</h5>
                        </div>
                        <span class="ui-panel-icon"><i class="fas fa-chart-pie"></i></span>
                    </div>

                    <div class="ui-chart-shell">
                        <canvas id="projectCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="ui-section-title">{{ db_trans('top_projects') }}</h5>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.projects.dashboard.top-projects.export'))
                        <a
                            href="{{ route('pdf.finance.projects.dashboard.top-projects.export', request()->query()) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger rounded-pill px-3"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.projects.dashboard.top-projects.export.excel'))
                        <a
                            href="{{ route('finance.projects.dashboard.top-projects.export.excel', request()->query()) }}"
                            class="btn btn-sm btn-success rounded-pill px-3"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif

                    <a href="{{ route('finance.projects.index') }}" class="btn ui-btn-light btn-sm">
                        {{ db_trans('view_all') }}
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="projectFinanceTopProjectsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('project') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('income') }}</th>
                            <th>{{ db_trans('expense') }}</th>
                            <th>{{ db_trans('balance') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($topProjects as $project)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $project->name }}</div>
                                </td>
                                <td>
                                    <span class="ui-status-pill ui-status-{{ strtolower((string) $project->status) }}">
                                        {{ db_trans($project->status) }}
                                    </span>
                                </td>
                                <td>{{ $currency($project->income) }}</td>
                                <td>{{ $currency($project->expense) }}</td>
                                <td class="fw-bold {{ ($project->net ?? 0) >= 0 ? 'ui-text-success' : 'ui-text-danger' }}">
                                    {{ $currency($project->net) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ui-empty-state my-2">{{ db_trans('no_data_available') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($topProjects->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">{{ db_trans('grand_total') }}</th>
                                <th>{{ $currency($topProjects->sum('income')) }}</th>
                                <th>{{ $currency($topProjects->sum('expense')) }}</th>
                                <th>{{ $currency($topProjects->sum('net')) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthlyTrendEl = document.getElementById('projectMonthlyTrendChart');

    if (monthlyTrendEl) {
        new Chart(monthlyTrendEl, {
            type: 'bar',
            data: {
                labels: @json($monthlyChart['labels']),
                datasets: [
                    {
                        label: @json(db_trans('income')),
                        data: @json($monthlyChart['income']),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(22, 163, 74, 1)',
                        borderWidth: 1,
                        borderRadius: 10
                    },
                    {
                        label: @json(db_trans('expense')),
                        data: @json($monthlyChart['expense']),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgba(220, 38, 38, 1)',
                        borderWidth: 1,
                        borderRadius: 10
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    const categoryChartEl = document.getElementById('projectCategoryChart');

    if (categoryChartEl) {
        new Chart(categoryChartEl, {
            type: 'doughnut',
            data: {
                labels: @json($categoryChart['labels']),
                datasets: [{
                    data: @json($categoryChart['amounts']),
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.85)',
                        'rgba(14, 165, 233, 0.85)',
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(100, 116, 139, 0.85)'
                    ],
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

    if (window.jQuery && document.getElementById('projectFinanceTopProjectsTable')) {
        $('#projectFinanceTopProjectsTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
            language: {
                search: '',
                searchPlaceholder: @json(db_trans('search')) + '...',
                lengthMenu: '_MENU_',
                zeroRecords: @json(db_trans('no_records_found')),
                info: @json(db_trans('showing_records_info')),
                infoEmpty: @json(db_trans('no_records_found')),
                paginate: {
                    previous: @json(db_trans('previous')),
                    next: @json(db_trans('next'))
                }
            }
        });
    }
});
</script>
@endpush