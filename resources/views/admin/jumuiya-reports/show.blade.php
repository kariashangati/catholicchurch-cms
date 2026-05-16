@extends('layouts.admin')

@section('title', $jumuiya->name)
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        .jumuiya-monthly-chart-wrap {
            height: 360px;
            min-height: 360px;
            max-height: 360px;
        }

        @media (max-width: 768px) {
            .jumuiya-monthly-chart-wrap {
                height: 300px;
                min-height: 300px;
                max-height: 300px;
            }
        }
    </style>
@endpush

@section('content')
@php
    $contributionTypes = collect($contributionTypes ?? []);
    $familyBreakdown = collect($familyBreakdown ?? []);
    $recentTransactions = collect($recentTransactions ?? []);
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
                    {{ db_trans('jumuiya_financial_report') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-2">{{ $jumuiya->name }}</h1>

                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill">
                        <i class="fas fa-calendar"></i>
                        {{ db_trans('year') }}: {{ $selectedYear }}
                        @if($selectedMonth)
                            / {{ \Carbon\Carbon::create(null, $selectedMonth, 1)->translatedFormat('F') }}
                        @endif
                    </span>

                    <span class="ui-meta-pill">
                        <i class="fas fa-map"></i>
                        {{ $jumuiya->kanda?->name ?? '—' }}
                    </span>

                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-wallet"></i>
                        {{ $currency($stats['grand_total'] ?? 0) }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('jumuiya-reports.index', ['year' => $selectedYear, 'month' => $selectedMonth]) }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('back') }}</span>
                    </a>

                    <a href="{{ Route::has('jumuiya.show') ? route('jumuiya.show', $jumuiya) : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-eye"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('view') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('jumuiya-reports.show', $jumuiya) }}" class="card ui-filter-card border-0 mb-4">
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
            <div class="ui-stat-card ui-tone-info p-4 h-100">
                <div class="ui-stat-top">
                    <span class="ui-stat-icon"><i class="fas fa-home"></i></span>
                    <span class="ui-chip">{{ db_trans('familias') }}</span>
                </div>
                <div class="ui-stat-label">{{ db_trans('familias') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($stats['familias_count'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('members') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($stats['members_count'] ?? 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-primary">
                    <i class="fas fa-cross"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_tithes') }}</div>
                <div class="ui-stat-value">{{ $currency($stats['tithe_total'] ?? 0) }}</div>
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
                <div class="ui-mini-icon ui-mini-tone-danger">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="ui-stat-label">{{ db_trans('total_offerings') }}</div>
                <div class="ui-stat-value">{{ $currency($stats['offering_total'] ?? 0) }}</div>
            </div>
        </div>

        @foreach($contributionTypes->take(7) as $type)
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
                    <h5 class="mb-0">{{ db_trans('monthly_trends') }}</h5>
                    <span class="ui-panel-icon">
                        <i class="fas fa-chart-line"></i>
                    </span>
                </div>

                <div class="ui-chart-shell jumuiya-monthly-chart-wrap">
                    <canvas id="jumuiyaMonthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="ui-table-card p-4">
                <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">{{ db_trans('familias') }}</h5>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="ui-section-badge">
                            {{ number_format($familyBreakdown->count()) }} {{ db_trans('records') }}
                        </span>

                        <a href="{{ route('pdf.jumuiya-reports.show.export', array_merge(['jumuiya' => $jumuiya->id], $exportQuery)) }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>

                        <a href="{{ route('jumuiya-reports.show.export.excel', array_merge(['jumuiya' => $jumuiya->id], $exportQuery)) }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle" id="jumuiyaFamilyTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('familia') }}</th>
                                <th>{{ db_trans('members') }}</th>
                                <th>{{ db_trans('total_tithes') }}</th>

                                @foreach($contributionTypes as $type)
                                    <th>{{ $type->name }}</th>
                                @endforeach

                                <th>{{ db_trans('grand_total') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($familyBreakdown as $familia)
                                <tr>
                                    <td class="fw-bold">{{ $familia->name }}</td>
                                    <td>{{ number_format($familia->members_count ?? 0) }}</td>
                                    <td>{{ $currency($familia->tithe_total ?? 0) }}</td>

                                    @foreach($contributionTypes as $type)
                                        <td>{{ $currency(data_get($familia->contribution_type_totals ?? [], $type->id, 0)) }}</td>
                                    @endforeach

                                    <td class="fw-bold text-success">{{ $currency($familia->grand_total ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th>{{ db_trans('grand_total') }}</th>
                                <th>{{ number_format((int) ($stats['members_count'] ?? 0)) }}</th>
                                <th>{{ $currency($stats['tithe_total'] ?? 0) }}</th>

                                @foreach($contributionTypes as $type)
                                    <th>{{ $currency(data_get($stats, 'contribution_type_totals.' . $type->id, 0)) }}</th>
                                @endforeach

                                <th class="text-success">{{ $currency($stats['grand_total'] ?? 0) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ db_trans('recent_transactions') }}</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="ui-section-badge">
                    {{ number_format($recentTransactions->count()) }} {{ db_trans('records') }}
                </span>

                <a href="{{ route('pdf.jumuiya-reports.show.export', array_merge(['jumuiya' => $jumuiya->id], $exportQuery)) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>

                <a href="{{ route('jumuiya-reports.show.export.excel', array_merge(['jumuiya' => $jumuiya->id], $exportQuery)) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="jumuiyaRecentTransactionsTable">
                <thead>
                    <tr>
                        <th>{{ db_trans('source') }}</th>
                        <th>{{ db_trans('type') }}</th>
                        <th>{{ db_trans('member') }}</th>
                        <th>{{ db_trans('familia') }}</th>
                        <th>{{ db_trans('amount') }}</th>
                        <th>{{ db_trans('date') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->source }}</td>
                            <td>{{ $transaction->category }}</td>
                            <td class="fw-bold">{{ $transaction->member_name }}</td>
                            <td>{{ $transaction->familia_name }}</td>
                            <td class="fw-bold text-success">{{ $currency($transaction->amount) }}</td>
                            <td data-order="{{ optional($transaction->date)->format('Y-m-d') }}">
                                {{ optional($transaction->date)->format('M d, Y') ?: '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
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
    ['#jumuiyaFamilyTable', '#jumuiyaRecentTransactionsTable'].forEach(function (selector) {
        if (window.jQuery && $.fn.DataTable && $(selector).length) {
            $(selector).DataTable({
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
    });

    const ctx = document.getElementById('jumuiyaMonthlyTrendChart');

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

        const contributionSeries = @json(data_get($monthlyTrends ?? [], 'contributions', []));

        const datasets = [];

        const addDataset = function (label, data, colorIndex) {
            const color = chartColors[colorIndex % chartColors.length];

            datasets.push({
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: transparentize(color, 0.14),
                pointBackgroundColor: color,
                pointBorderColor: '#ffffff',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: color,
                borderWidth: 3,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: false,
                type: 'line',
                tension: 0.35
            });
        };

        addDataset(
            @json(db_trans('total_tithes')),
            @json(data_get($monthlyTrends ?? [], 'tithes', [])),
            0
        );

        addDataset(
            @json(db_trans('total_offerings')),
            @json(data_get($monthlyTrends ?? [], 'offerings', [])),
            1
        );

        contributionTypes.forEach(function (type, index) {
            addDataset(
                type.name,
                contributionSeries[type.id] || [],
                index + 2
            );
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json(data_get($monthlyTrends ?? [], 'labels', [])),
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
                            color: 'rgba(15, 23, 42, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 12
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