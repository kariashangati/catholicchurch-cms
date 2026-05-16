@extends('layouts.admin')

@section('title', db_trans('jumuiya_details'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/jumuiya-module-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    <div class="admin-ui-v3 jumuiya-module-v3">
        <div class="ui-page-hero jumuiya-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="ui-page-hero-badge">{{ db_trans('jumuiya_details') }}</span>
                    <h1 class="ui-page-hero-title">{{ $jumuiya->name }}</h1>

                    <div class="ui-page-hero-pills mt-3">
                        <span class="ui-hero-pill">
                            <i class="fas fa-sitemap"></i>{{ $jumuiya->kanda?->name ?? '—' }}
                        </span>
                        <span class="ui-hero-pill">
                            <i class="fas fa-calendar-alt"></i>{{ optional($jumuiya->created_at)->format('M d, Y') }}
                        </span>
                        <span class="ui-hero-pill">
                            <i class="fas fa-users"></i>{{ number_format($stats['members_count']) }} {{ db_trans('members') }}
                        </span>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="hero-filter-card bg-white bg-opacity-10 rounded-4 p-3">
                        <form method="GET" action="{{ route('jumuiyas.show', $jumuiya) }}">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small mb-1 text-white">{{ db_trans('year') }}</label>
                                    <select name="year" class="form-select form-select-sm">
                                        @foreach($filterOptions['years'] as $year)
                                            <option value="{{ $year }}" @selected((int) $filters['year'] === (int) $year)>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="form-label small mb-1 text-white">{{ db_trans('month') }}</label>
                                    <select name="month" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_months') }}</option>
                                        @foreach($filterOptions['months'] as $month)
                                            <option value="{{ $month['value'] }}" @selected((string) $filters['month'] === (string) $month['value'])>
                                                {{ $month['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small mb-1 text-white">{{ db_trans('familia') }}</label>
                                    <select name="familia_id" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_familias') }}</option>
                                        @foreach($filterOptions['familias'] as $optionFamilia)
                                            <option value="{{ $optionFamilia->id }}" @selected((string) $filters['familia_id'] === (string) $optionFamilia->id)>
                                                {{ $optionFamilia->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-light btn-sm w-100">
                                        <i class="fas fa-filter me-1"></i>{{ db_trans('apply_filters') }}
                                    </button>
                                </div>

                                <div class="col-12 d-flex gap-2">
                                    @if(!empty($showActions['report']))
                                        <a href="{{ $showActions['report'] }}" class="btn btn-outline-light btn-sm flex-fill">
                                            <i class="fas fa-chart-line me-1"></i>{{ db_trans('view_report') }}
                                        </a>
                                    @endif

                                    <a href="{{ $showActions['back'] ?? route('jumuiyas.index') }}" class="btn btn-outline-light btn-sm flex-fill">
                                        <i class="fas fa-arrow-left me-1"></i>{{ db_trans('back') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                @if(!empty($showActions['members']))
                    <a href="{{ $showActions['members'] }}" class="text-decoration-none">
                @endif
                <div class="ui-stat-card ui-stat-card--info h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-home"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('familias') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['familias_count']) }}</div>
                    </div>
                </div>
                @if(!empty($showActions['members']))
                    </a>
                @endif
            </div>

            <div class="col-xl-4 col-md-6">
                @if(!empty($showActions['members']))
                    <a href="{{ $showActions['members'] }}" class="text-decoration-none">
                @endif
                <div class="ui-stat-card ui-stat-card--warning h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-users"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('members') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['members_count']) }}</div>
                    </div>
                </div>
                @if(!empty($showActions['members']))
                    </a>
                @endif
            </div>

            <div class="col-xl-4 col-md-12">
                @if(!empty($showActions['report']))
                    <a href="{{ $showActions['report'] }}" class="text-decoration-none">
                @endif
                <div class="ui-stat-card ui-stat-card--primary h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-chart-line"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('grand_total') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['grand_total'], 2) }}</div>
                    </div>
                </div>
                @if(!empty($showActions['report']))
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                @if(!empty($showActions['report']))
                    <a href="{{ $showActions['report'] }}" class="text-decoration-none">
                @endif
                <div class="card dashboard-panel h-100">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-1 text-dark">{{ db_trans('financial_trend') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-shell">
                            <canvas id="jumuiyaFinancialTrendChart"></canvas>
                        </div>
                    </div>
                </div>
                @if(!empty($showActions['report']))
                    </a>
                @endif
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-panel h-100">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-1">{{ db_trans('members_distribution') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-shell">
                            <canvas id="jumuiyaGenderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-panel">
            <div class="card-header bg-transparent border-0 p-4 d-flex flex-wrap gap-3 justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ db_trans('familias') }}</h5>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(!empty($showActions['familias_pdf']))
                        <a
                            href="{{ $showActions['familias_pdf'] . '?' . http_build_query([
                                'familia_id' => $filters['familia_id'] ?? null,
                            ]) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(!empty($showActions['familias_excel']))
                        <a
                            href="{{ $showActions['familias_excel'] . '?' . http_build_query([
                                'familia_id' => $filters['familia_id'] ?? null,
                            ]) }}"
                            class="btn btn-sm btn-success"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="jumuiyaFamiliasTable" class="table align-middle table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ db_trans('familia') }}</th>
                                <th>{{ db_trans('phone') }}</th>
                                <th>{{ db_trans('members') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($familiaRows as $familia)
                                <tr>
                                    <td class="fw-bold">{{ $familia['name'] }}</td>
                                    <td>{{ $familia['phone'] }}</td>
                                    <td>{{ number_format($familia['members_count']) }}</td>
                                    <td>
                                        <a href="{{ $familia['details_url'] }}" class="btn btn-sm btn-outline-primary">
                                            {{ db_trans('view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        {{ db_trans('no_data_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const genderCtx = document.getElementById('jumuiyaGenderChart');
    if (genderCtx) {
        new Chart(genderCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($chartData['genderLabels']),
                datasets: [{
                    data: @json($chartData['genderData']),
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.90)',
                        'rgba(16, 185, 129, 0.90)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            color: '#334155'
                        }
                    }
                },
                cutout: '64%'
            }
        });
    }

    const financeTrendCtx = document.getElementById('jumuiyaFinancialTrendChart');
    if (financeTrendCtx) {
        new Chart(financeTrendCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($financialTrendChart['labels'] ?? []),
                datasets: [
                    {
                        label: @json(db_trans('tithes')),
                        data: @json($financialTrendChart['tithes'] ?? []),
                        backgroundColor: 'rgba(22, 163, 74, 0.75)',
                        borderColor: '#16a34a',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: @json(db_trans('offerings')),
                        data: @json($financialTrendChart['offerings'] ?? []),
                        backgroundColor: 'rgba(124, 58, 237, 0.75)',
                        borderColor: '#7c3aed',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: @json(db_trans('projects') ?: 'Projects'),
                        data: @json($financialTrendChart['projects'] ?? []),
                        backgroundColor: 'rgba(245, 158, 11, 0.75)',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    @php
                        $seriesColors = [
                            ['bg' => 'rgba(14, 165, 233, 0.75)', 'border' => '#0ea5e9'],
                            ['bg' => 'rgba(236, 72, 153, 0.75)', 'border' => '#ec4899'],
                            ['bg' => 'rgba(99, 102, 241, 0.75)', 'border' => '#6366f1'],
                            ['bg' => 'rgba(234, 88, 12, 0.75)', 'border' => '#ea580c'],
                            ['bg' => 'rgba(20, 184, 166, 0.75)', 'border' => '#14b8a6'],
                            ['bg' => 'rgba(168, 85, 247, 0.75)', 'border' => '#a855f7'],
                        ];
                    @endphp

                    @foreach(($financialTrendChart['contributions'] ?? collect()) as $index => $series)
                        {
                            label: @json($series['label']),
                            data: @json($series['data']),
                            backgroundColor: @json($seriesColors[$index % count($seriesColors)]['bg']),
                            borderColor: @json($seriesColors[$index % count($seriesColors)]['border']),
                            borderWidth: 1,
                            borderRadius: 6
                        },
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 16
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    if (typeof window.jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
        $('#jumuiyaFamiliasTable').DataTable({
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            responsive: true,
            autoWidth: false,
            language: {
                search: '',
                searchPlaceholder: @json(db_trans('search') ?: 'Search'),
                lengthMenu: @json((db_trans('show') ?: 'Show') . ' _MENU_ ' . (db_trans('entries') ?: 'entries')),
                info: @json(db_trans('showing_records') ?: 'Showing _START_ to _END_ of _TOTAL_ records'),
                infoEmpty: @json(db_trans('no_records_available') ?: 'No records available'),
                zeroRecords: @json(db_trans('no_matching_records') ?: 'No matching records found'),
                paginate: {
                    next: @json(db_trans('next') ?: 'Next'),
                    previous: @json(db_trans('previous') ?: 'Previous')
                }
            },
            order: [[0, 'asc']]
        });
    }
});
</script>
@endpush