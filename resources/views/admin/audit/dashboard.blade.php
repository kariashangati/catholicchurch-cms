@extends('layouts.admin')

@section('title', db_trans('audit_access_center'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/auditdashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/audit-access-fixes.css') }}">
@endpush

@section('content')
@php
    $riskHighlights = collect($riskHighlights ?? []);
@endphp

<div class="admin-ui-v4 audit-shell">
    <div class="audit-hero audit-hero-standard">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="audit-hero-badge">
                    <i class="fas fa-shield-alt"></i>
                    {{ db_trans('system') }}
                </span>

                <h2 class="audit-hero-title">{{ $page['title'] ?? db_trans('audit_access_center') }}</h2>

                <div class="audit-hero-meta mt-3">
                    <span class="audit-hero-pill">
                        <i class="fas fa-clock"></i>
                        {{ db_trans('last_updated') }}:
                        {{ optional($page['updated_at'] ?? now())->format('d M Y, h:i A') }}
                    </span>

                    <span class="audit-hero-pill">
                        <i class="fas fa-user-shield"></i>
                        {{ db_trans('logged_in_as') }}: {{ auth()->user()->name }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('audit.logs.view')
                        <a href="{{ route('system.activity-logs.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-stream"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('activity_logs') }}</span>
                        </a>
                    @endcan

                    @can('audit.logins.view')
                        <a href="{{ route('system.login-history.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-sign-in-alt"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('login_history') }}</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach($kpis as $card)
            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="audit-kpi-card card">
                    <div class="card-body">
                        <div class="audit-kpi-top">
                            <span class="audit-kpi-icon tone-{{ $card['tone'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </span>
                            <span class="audit-kpi-chip">{{ db_trans('summary') }}</span>
                        </div>

                        <div class="audit-kpi-title">{{ $card['title'] }}</div>
                        <div class="audit-kpi-value">{{ number_format((int) $card['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($riskHighlights->isNotEmpty())
        <div class="row g-4">
            @foreach($riskHighlights as $item)
                <div class="col-xl-3 col-md-6">
                    <div class="audit-highlight-card h-100">
                        <div class="audit-highlight-label">{{ $item['label'] }}</div>
                        <div class="audit-highlight-value">{{ $item['value'] }}</div>
                        <div class="audit-highlight-meta">{{ $item['meta'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="audit-panel card">
                <div class="card-body">
                    <div class="audit-panel-head">
                        <h4 class="audit-panel-title">{{ db_trans('activity_trend') }}</h4>
                        <span class="audit-panel-badge">{{ db_trans('activities_today') }}</span>
                    </div>

                    <div class="audit-chart-shell audit-chart-shell-md">
                        <canvas id="auditActivityTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="audit-panel card">
                <div class="card-body">
                    <div class="audit-panel-head">
                        <h4 class="audit-panel-title">{{ db_trans('login_risk_breakdown') }}</h4>
                        <span class="audit-panel-badge">{{ db_trans('logins_24h') }}</span>
                    </div>

                    <div class="audit-chart-shell audit-chart-shell-sm">
                        <canvas id="auditRiskBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="audit-panel card">
                <div class="card-body">
                    <div class="audit-panel-head">
                        <h4 class="audit-panel-title">{{ db_trans('login_trend') }}</h4>
                        <span class="audit-panel-badge">{{ db_trans('login_history') }}</span>
                    </div>

                    <div class="audit-chart-shell audit-chart-shell-sm">
                        <canvas id="auditLoginTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="audit-panel card">
                <div class="card-body">
                    <div class="audit-panel-head">
                        <h4 class="audit-panel-title">{{ db_trans('module_breakdown') }}</h4>
                        <span class="audit-panel-badge">{{ db_trans('activity_logs') }}</span>
                    </div>

                    <div class="audit-chart-shell audit-chart-shell-sm">
                        <canvas id="auditModuleBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.auditDashboardData = {
            activityTrendChart: @json($activityTrendChart ?? []),
            loginTrendChart: @json($loginTrendChart ?? []),
            moduleBreakdownChart: @json($moduleBreakdownChart ?? []),
            riskBreakdownChart: @json($riskBreakdownChart ?? []),
            labels: {
                success: @json(db_trans('success')),
                failed: @json(db_trans('failed')),
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            const data = window.auditDashboardData || {};
            const palette = ['#7c3aed', '#10b981', '#f59e0b', '#0ea5e9', '#ef4444', '#64748b', '#14b8a6', '#a855f7'];

            const commonOptions = {
                maintainAspectRatio: false,
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: true, position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            };

            const makeLineChart = (id, labels, datasets) => {
                const canvas = document.getElementById(id);
                if (!canvas) return;

                new Chart(canvas, {
                    type: 'line',
                    data: { labels, datasets },
                    options: commonOptions
                });
            };

            const makeBarChart = (id, labels, values, horizontal = false) => {
                const canvas = document.getElementById(id);
                if (!canvas) return;

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: @json(db_trans('records')),
                            data: values,
                            backgroundColor: labels.map((_, index) => palette[index % palette.length]),
                            borderRadius: 10,
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: horizontal ? 'y' : 'x',
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { ticks: { precision: 0 } }
                        }
                    }
                });
            };

            const makeDoughnutChart = (id, labels, values) => {
                const canvas = document.getElementById(id);
                if (!canvas) return;

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: labels.map((_, index) => palette[index % palette.length]),
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: { legend: { display: true, position: 'bottom' } }
                    }
                });
            };

            makeLineChart(
                'auditActivityTrendChart',
                data.activityTrendChart?.labels || [],
                [{
                    label: @json(db_trans('activity_logs')),
                    data: data.activityTrendChart?.values || [],
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, .14)',
                    pointBackgroundColor: '#7c3aed',
                    tension: .35,
                    fill: true,
                    borderWidth: 3,
                }]
            );

            makeLineChart(
                'auditLoginTrendChart',
                data.loginTrendChart?.labels || [],
                [
                    {
                        label: data.labels?.success || 'Success',
                        data: data.loginTrendChart?.success || [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, .12)',
                        pointBackgroundColor: '#10b981',
                        tension: .35,
                        fill: true,
                        borderWidth: 3,
                    },
                    {
                        label: data.labels?.failed || 'Failed',
                        data: data.loginTrendChart?.failed || [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, .10)',
                        pointBackgroundColor: '#ef4444',
                        tension: .35,
                        fill: true,
                        borderWidth: 3,
                    }
                ]
            );

            makeBarChart(
                'auditModuleBreakdownChart',
                data.moduleBreakdownChart?.labels || [],
                data.moduleBreakdownChart?.values || [],
                true
            );

            makeDoughnutChart(
                'auditRiskBreakdownChart',
                data.riskBreakdownChart?.labels || [],
                data.riskBreakdownChart?.values || []
            );
        });
    </script>
@endpush
