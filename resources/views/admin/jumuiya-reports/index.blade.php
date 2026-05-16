@extends('layouts.admin')

@section('title', db_trans('jumuiya_financial_reports'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        .jumuiya-report-chart-wrap {
            height: 340px;
            min-height: 340px;
            max-height: 340px;
        }

        @media (max-width: 768px) {
            .jumuiya-report-chart-wrap {
                height: 300px;
                min-height: 300px;
                max-height: 300px;
            }
        }
    </style>
@endpush

@section('content')
@php
    $reports = collect($reports ?? []);
    $contributionTypes = collect($contributionTypes ?? []);
    $stats = $stats ?? [];
    $currency = fn ($amount) => number_format((float) $amount, 2);

    $exportQuery = array_filter([
        'year' => $selectedYear ?? null,
        'month' => $selectedMonth ?? null,
    ], fn ($value) => filled($value));
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>

        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-layer-group"></i>
                    {{ db_trans('jumuiya_financial_reports') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('jumuiya_financial_reports') }}</h1>

                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill">
                        <i class="fas fa-calendar"></i>
                        {{ $selectedYear }}
                        @if($selectedMonth)
                            / {{ \Carbon\Carbon::create(null, $selectedMonth, 1)->translatedFormat('F') }}
                        @endif
                    </span>

                    <span class="ui-meta-pill">
                        <i class="fas fa-layer-group"></i>
                        {{ number_format((int) ($stats['total_jumuiyas'] ?? 0)) }} {{ db_trans('jumuiyas') }}
                    </span>

                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-wallet"></i>
                        {{ $currency($stats['grand_total'] ?? 0) }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ Route::has('kanda-reports.index') ? route('kanda-reports.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </span>
                        <span class="ui-hero-action-text">{{ db_trans('kanda_financial_reports') }}</span>
                    </a>

                    <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        <span class="ui-hero-action-text">{{ db_trans('reports_dashboard') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('jumuiya-reports.index') }}" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <select name="year" class="form-select">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select">
                        <option value="">{{ db_trans('all_months') }}</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" @selected((int) $selectedMonth === (int) $month)>
                                {{ \Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn ui-btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>{{ db_trans('filter_records') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-primary p-4 h-100">
                <div class="ui-stat-top">
                    <span class="ui-stat-icon"><i class="fas fa-layer-group"></i></span>
                    <span class="ui-chip">{{ db_trans('summary') }}</span>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_jumuiyas') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($stats['total_jumuiyas'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-info">
                    <i class="fas fa-home"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_familias') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($stats['total_familias'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_members') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($stats['total_members'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-success">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('grand_total') }}</div>
                <div class="ui-stat-value">{{ $currency($stats['grand_total'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-primary">
                    <i class="fas fa-cross"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_tithes') }}</div>
                <div class="ui-stat-value">{{ $currency($stats['total_tithes'] ?? 0) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-danger">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_offerings') }}</div>
                <div class="ui-stat-value">{{ $currency($stats['total_offerings'] ?? 0) }}</div>
            </div>
        </div>

        @foreach($contributionTypes->take(6) as $type)
            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-success">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="ui-stat-label">{{ $type->name }}</div>
                    <div class="ui-stat-value">
                        {{ $currency(data_get($stats, 'contribution_type_totals.' . $type->id, 0)) }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="ui-panel p-4 h-100">
                <div class="ui-panel-head">
                    <h5 class="mb-0">{{ db_trans('jumuiya_financial_reports') }}</h5>
                    <span class="ui-panel-icon">
                        <i class="fas fa-chart-column"></i>
                    </span>
                </div>

                <div class="ui-chart-shell jumuiya-report-chart-wrap">
                    <canvas id="jumuiyaReportFinanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ db_trans('jumuiya_financial_reports') }}</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="ui-section-badge">
                    {{ number_format($reports->count()) }} {{ db_trans('records') }}
                </span>

                <a href="{{ route('pdf.jumuiya-reports.export', $exportQuery) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>

                <a href="{{ route('jumuiya-reports.export.excel', $exportQuery) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="jumuiyaReportTable">
                <thead>
                    <tr>
                        <th>{{ db_trans('jumuiya') }}</th>
                        <th>{{ db_trans('kanda') }}</th>
                        <th>{{ db_trans('familias') }}</th>
                        <th>{{ db_trans('members') }}</th>
                        <th>{{ db_trans('total_tithes') }}</th>
                        <th>{{ db_trans('total_offerings') }}</th>

                        @foreach($contributionTypes as $type)
                            <th>{{ $type->name }}</th>
                        @endforeach

                        <th>{{ db_trans('grand_total') }}</th>
                        <th>{{ db_trans('actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->name }}</strong>
                                <div class="small text-muted">{{ $report->slug }}</div>
                            </td>

                            <td>{{ $report->kanda?->name ?? '—' }}</td>
                            <td>{{ number_format($report->familias_count ?? 0) }}</td>
                            <td>{{ number_format($report->members_count ?? 0) }}</td>
                            <td>{{ $currency($report->tithe_total ?? 0) }}</td>
                            <td>{{ $currency($report->offering_total ?? 0) }}</td>

                            @foreach($contributionTypes as $type)
                                <td>{{ $currency(data_get($report->contribution_type_totals ?? [], $type->id, 0)) }}</td>
                            @endforeach

                            <td class="fw-bold text-success">{{ $currency($report->grand_total ?? 0) }}</td>

                            <td>
                                <a href="{{ route('jumuiya-reports.show', ['jumuiya' => $report->id, 'year' => $selectedYear, 'month' => $selectedMonth]) }}" class="btn btn-sm btn-outline-primary">
                                    {{ db_trans('view_report') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th>{{ db_trans('grand_total') }}</th>
                        <th>—</th>
                        <th>{{ number_format((int) ($stats['total_familias'] ?? 0)) }}</th>
                        <th>{{ number_format((int) ($stats['total_members'] ?? 0)) }}</th>
                        <th>{{ $currency($stats['total_tithes'] ?? 0) }}</th>
                        <th>{{ $currency($stats['total_offerings'] ?? 0) }}</th>

                        @foreach($contributionTypes as $type)
                            <th>{{ $currency(data_get($stats, 'contribution_type_totals.' . $type->id, 0)) }}</th>
                        @endforeach

                        <th class="text-success">{{ $currency($stats['grand_total'] ?? 0) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#jumuiyaReportTable').length) {
        $('#jumuiyaReportTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, 100, -1],
                [5, 10, 25, 50, 100, @json(db_trans('all'))]
            ],
            order: [],
            autoWidth: false,
            language: {
                search: @json(db_trans('search')) + ':',
                lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
                infoEmpty: @json(db_trans('no_records_found')),
                zeroRecords: @json(db_trans('no_records_found')),
                paginate: {
                    previous: '‹',
                    next: '›'
                }
            }
        });
    }

    const ctx = document.getElementById('jumuiyaReportFinanceChart');

    if (ctx && typeof Chart !== 'undefined') {
        const chartColors = [
            '#22c55e',
            '#8b5cf6',
            '#f59e0b',
            '#0ea5e9',
            '#ef4444',
            '#14b8a6',
            '#6366f1',
            '#f97316',
            '#84cc16',
            '#ec4899',
            '#06b6d4',
            '#a855f7',
            '#10b981',
            '#eab308',
            '#3b82f6'
        ];

        const transparentize = function (hex, alpha) {
            const clean = hex.replace('#', '');
            const r = parseInt(clean.substring(0, 2), 16);
            const g = parseInt(clean.substring(2, 4), 16);
            const b = parseInt(clean.substring(4, 6), 16);

            return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
        };

        const contributionTypes = @json(
            $contributionTypes
                ->map(fn($type) => ['id' => $type->id, 'name' => $type->name])
                ->values()
        );

        const contributionSeries = @json(data_get($chartData ?? [], 'contributions', []));

        const datasets = [];

        const addBarDataset = function (label, data, colorIndex) {
            const color = chartColors[colorIndex % chartColors.length];

            datasets.push({
                label: label,
                data: data,
                backgroundColor: transparentize(color, 0.78),
                borderColor: color,
                hoverBackgroundColor: transparentize(color, 0.92),
                hoverBorderColor: color,
                borderWidth: 1.5,
                borderRadius: 8,
                type: 'bar'
            });
        };

        const addLineDataset = function (label, data, colorIndex) {
            const color = chartColors[colorIndex % chartColors.length];

            datasets.push({
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: transparentize(color, 0.12),
                pointBackgroundColor: color,
                pointBorderColor: '#ffffff',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: color,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: false,
                type: 'line',
                tension: 0.35,
                yAxisID: 'y'
            });
        };

        addBarDataset(
            @json(db_trans('total_tithes')),
            @json(data_get($chartData ?? [], 'tithes', [])),
            0
        );

        addBarDataset(
            @json(db_trans('total_offerings')),
            @json(data_get($chartData ?? [], 'offerings', [])),
            1
        );

        contributionTypes.forEach(function (type, index) {
            addBarDataset(
                type.name,
                contributionSeries[type.id] || [],
                index + 2
            );
        });

        addLineDataset(
            @json(db_trans('grand_total')),
            @json(data_get($chartData ?? [], 'grand_total', [])),
            contributionTypes.length + 2
        );

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json(data_get($chartData ?? [], 'labels', [])),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(15, 23, 42, 0.08)'
                        },
                        ticks: {
                            callback: function (value) {
                                return Number(value).toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = Number(context.parsed.y || 0).toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });

                                return context.dataset.label + ': ' + value;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush