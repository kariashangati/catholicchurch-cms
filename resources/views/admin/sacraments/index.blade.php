@extends('layouts.admin')

@section('title', $pageTitle ?? db_trans('sacraments'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/sacraments-module-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    @php
        $primaryStats = [
            ['label' => db_trans('total_members'), 'value' => number_format($stats['total_members'] ?? 0), 'icon' => 'fas fa-users', 'tone' => 'primary'],
            ['label' => db_trans('baptized'), 'value' => number_format($stats['baptized'] ?? 0), 'icon' => 'fas fa-droplet', 'tone' => 'blue'],
            ['label' => db_trans('communion'), 'value' => number_format($stats['communion'] ?? 0), 'icon' => 'fas fa-bread-slice', 'tone' => 'amber'],
            ['label' => db_trans('confirmation'), 'value' => number_format($stats['confirmation'] ?? 0), 'icon' => 'fas fa-certificate', 'tone' => 'green'],
            ['label' => db_trans('married'), 'value' => number_format($stats['married'] ?? 0), 'icon' => 'fas fa-ring', 'tone' => 'teal'],
            ['label' => db_trans('receiving_eucharist'), 'value' => number_format($stats['eucharist'] ?? 0), 'icon' => 'fas fa-wine-glass', 'tone' => 'danger'],
        ];

        $rateCards = [
            ['label' => db_trans('baptism_rate'), 'value' => $rates['baptized_rate'] ?? 0],
            ['label' => db_trans('communion_rate'), 'value' => $rates['communion_rate'] ?? 0],
            ['label' => db_trans('confirmation_rate'), 'value' => $rates['confirmation_rate'] ?? 0],
            ['label' => db_trans('marriage_rate'), 'value' => $rates['marriage_rate'] ?? 0],
            ['label' => db_trans('eucharist_rate'), 'value' => $rates['eucharist_rate'] ?? 0],
        ];

        $alertCards = [
            ['label' => db_trans('missing_date_of_birth'), 'value' => $alerts['missing_date_of_birth'] ?? 0, 'tone' => 'warning', 'icon' => 'fas fa-calendar-xmark'],
            ['label' => db_trans('missing_family_role'), 'value' => $alerts['missing_family_role'] ?? 0, 'tone' => 'danger', 'icon' => 'fas fa-people-arrows-left-right'],
            ['label' => db_trans('baptized_without_parish'), 'value' => $alerts['baptized_without_parish'] ?? 0, 'tone' => 'info', 'icon' => 'fas fa-church'],
            ['label' => db_trans('married_without_type'), 'value' => $alerts['married_without_type'] ?? 0, 'tone' => 'warning', 'icon' => 'fas fa-heart-circle-xmark'],
        ];
    @endphp

    <div class="sacraments-v3 admin-ui-v3">
        <div class="sacrament-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <span class="sacrament-hero-badge">
                        <i class="fas fa-chart-pie"></i>{{ db_trans('sacrament_analytics') }}
                    </span>

                    <h1 class="sacrament-title mt-3 mb-3">{{ $pageTitle ?? db_trans('sacraments') }}</h1>

                    <div class="sacrament-pill-wrap">
                        <span class="sacrament-pill"><i class="fas fa-users"></i>{{ number_format($stats['active_members'] ?? 0) }} {{ db_trans('active_members') }}</span>
                        <span class="sacrament-pill"><i class="fas fa-layer-group"></i>{{ number_format($stats['kandas'] ?? 0) }} {{ db_trans('kandas') }}</span>
                        <span class="sacrament-pill"><i class="fas fa-map"></i>{{ number_format($stats['jumuiyas'] ?? 0) }} {{ db_trans('jumuiyas') }}</span>
                        <span class="sacrament-pill sacrament-pill-warning"><i class="fas fa-house-user"></i>{{ number_format($stats['familias'] ?? 0) }} {{ db_trans('familias') }}</span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="sacrament-actions-grid">
                        <a href="{{ route('sacraments.kandas') }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-sitemap"></i></span>
                            <span><strong>{{ db_trans('open_kanda_summary') }}</strong></span>
                        </a>

                        <a href="{{ route('sacraments.jumuiyas') }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-people-group"></i></span>
                            <span><strong>{{ db_trans('open_jumuiya_summary') }}</strong></span>
                        </a>

                        <button type="button" class="sacrament-action-card border-0 text-start" data-bs-toggle="modal" data-bs-target="#sacramentDashboardFilterModal">
                            <span class="sacrament-action-icon"><i class="fas fa-sliders"></i></span>
                            <span><strong>{{ db_trans('apply_filters') }}</strong></span>
                        </button>

                        <a href="{{ url()->current() }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-rotate-right"></i></span>
                            <span><strong>{{ db_trans('refresh') }}</strong></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($primaryStats as $card)
                <div class="col-xxl-2 col-lg-4 col-md-6">
                    <div class="card sacrament-stat-card tone-{{ $card['tone'] }} border-0">
                        <div class="card-body p-4">
                            <div class="sacrament-stat-top">
                                <span class="sacrament-stat-icon"><i class="{{ $card['icon'] }}"></i></span>
                            </div>
                            <div class="sacrament-stat-label">{{ $card['label'] }}</div>
                            <div class="sacrament-stat-value">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card sacrament-panel border-0 mb-4">
            <div class="card-body p-4">
                <div class="sacrament-panel-head">
                    <div>
                        <h5 class="sacrament-panel-title">{{ db_trans('sacrament_completion_rate') }}</h5>
                    </div>
                    <span class="sacrament-chip">{{ db_trans('rates') }}</span>
                </div>

                <div class="sacrament-rate-grid">
                    @foreach($rateCards as $rateCard)
                        <div class="sacrament-rate-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="sacrament-rate-label">{{ $rateCard['label'] }}</span>
                                <span class="sacrament-rate-value">{{ $rateCard['value'] }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ min(100, max(0, (float) $rateCard['value'])) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card sacrament-panel border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('sacrament_status_breakdown') }}</h5>
                            </div>
                            <span class="sacrament-panel-icon"><i class="fas fa-chart-donut"></i></span>
                        </div>
                        <div class="chart-shell"><canvas id="sacramentDistributionChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card sacrament-panel border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('area_sacrament_performance') }}</h5>
                            </div>
                            <span class="sacrament-panel-icon"><i class="fas fa-chart-column"></i></span>
                        </div>
                        <div class="chart-shell"><canvas id="kandaPerformanceChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="card sacrament-table-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('kanda_sacrament_summary') }}</h5>
                            </div>
                            <a href="{{ route('sacraments.kandas') }}" class="sacrament-chip text-decoration-none">{{ db_trans('view_all') }}</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-hover" id="dashboardKandaTable">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('kanda') }}</th>
                                        <th>{{ db_trans('members') }}</th>
                                        <th>{{ db_trans('baptized') }}</th>
                                        <th>{{ db_trans('confirmation') }}</th>
                                        <th>{{ db_trans('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kandaRows as $row)
                                        <tr>
                                            <td>
                                                <div class="sacrament-row-title">
                                                    <strong>{{ $row['name'] }}</strong>
                                                    <small>{{ $row['code'] ?: '—' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ number_format($row['members']) }}</td>
                                            <td><span class="sacrament-soft-badge info">{{ number_format($row['baptized']) }}</span></td>
                                            <td><span class="sacrament-soft-badge success">{{ number_format($row['confirmation']) }}</span></td>
                                            <td>
                                                <a href="{{ route('sacraments.kandas.show', $row['id']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    {{ db_trans('open') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card sacrament-table-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('jumuiya_sacrament_summary') }}</h5>
                            </div>
                            <a href="{{ route('sacraments.jumuiyas') }}" class="sacrament-chip text-decoration-none">{{ db_trans('view_all') }}</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-hover" id="dashboardJumuiyaTable">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('jumuiya') }}</th>
                                        <th>{{ db_trans('members') }}</th>
                                        <th>{{ db_trans('communion') }}</th>
                                        <th>{{ db_trans('eucharist') }}</th>
                                        <th>{{ db_trans('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jumuiyaRows as $row)
                                        <tr>
                                            <td>
                                                <div class="sacrament-row-title">
                                                    <strong>{{ $row['name'] }}</strong>
                                                    <small>{{ $row['kanda_name'] ?? '—' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ number_format($row['members']) }}</td>
                                            <td><span class="sacrament-soft-badge warning">{{ number_format($row['communion']) }}</span></td>
                                            <td><span class="sacrament-soft-badge success">{{ number_format($row['eucharist']) }}</span></td>
                                            <td>
                                                <a href="{{ route('sacraments.jumuiyas.show', $row['id']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    {{ db_trans('open') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.sacraments.partials.filter-modal', [
            'modalId' => 'sacramentDashboardFilterModal',
            'action' => route('sacraments.index'),
        ])
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableConfig = {
                paging: false,
                info: false,
                searching: false,
                ordering: true,
                responsive: true,
                language: {
                    emptyTable: @json(db_trans('no_data_found')),
                }
            };

            if (window.jQuery) {
                if (document.getElementById('dashboardKandaTable')) {
                    $('#dashboardKandaTable').DataTable(tableConfig);
                }
                if (document.getElementById('dashboardJumuiyaTable')) {
                    $('#dashboardJumuiyaTable').DataTable(tableConfig);
                }
            }

            const distEl = document.getElementById('sacramentDistributionChart');
            if (distEl) {
                new Chart(distEl, {
                    type: 'doughnut',
                    data: {
                        labels: @json($distributionChart['labels'] ?? []),
                        datasets: [{
                            data: @json($distributionChart['data'] ?? []),
                            backgroundColor: ['#2563eb', '#16a34a', '#7c3aed', '#d97706', '#0f766e'],
                            borderWidth: 0,
                            hoverOffset: 8
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

            const kandaEl = document.getElementById('kandaPerformanceChart');
            if (kandaEl) {
                new Chart(kandaEl, {
                    type: 'bar',
                    data: {
                        labels: @json($kandaChart['labels'] ?? []),
                        datasets: [
                            { label: @json(db_trans('baptized')), data: @json($kandaChart['baptized'] ?? []), backgroundColor: '#2563eb', borderRadius: 10 },
                            { label: @json(db_trans('communion')), data: @json($kandaChart['communion'] ?? []), backgroundColor: '#16a34a', borderRadius: 10 },
                            { label: @json(db_trans('confirmation')), data: @json($kandaChart['confirmation'] ?? []), backgroundColor: '#7c3aed', borderRadius: 10 },
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        },
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        });
    </script>
@endpush