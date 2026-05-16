@extends('layouts.admin')

@section('title', $pageTitle ?? db_trans('kanda_sacrament_summary'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/sacraments-module-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    @php
        $cards = [
            ['label' => db_trans('total_kandas'), 'value' => number_format($stats['total_kandas'] ?? 0), 'icon' => 'fas fa-sitemap', 'tone' => 'primary'],
            ['label' => db_trans('total_members'), 'value' => number_format($stats['total_members'] ?? 0), 'icon' => 'fas fa-users', 'tone' => 'blue'],
            ['label' => db_trans('baptized'), 'value' => number_format($stats['baptized'] ?? 0), 'icon' => 'fas fa-droplet', 'tone' => 'green'],
            ['label' => db_trans('confirmation'), 'value' => number_format($stats['confirmation'] ?? 0), 'icon' => 'fas fa-certificate', 'tone' => 'teal'],
            ['label' => db_trans('communion'), 'value' => number_format($stats['communion'] ?? 0), 'icon' => 'fas fa-bread-slice', 'tone' => 'amber'],
            ['label' => db_trans('receiving_eucharist'), 'value' => number_format($stats['eucharist'] ?? 0), 'icon' => 'fas fa-wine-glass', 'tone' => 'danger'],
        ];
    @endphp

    <div class="sacraments-v3 admin-ui-v3">
        <div class="sacrament-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <span class="sacrament-hero-badge"><i class="fas fa-sitemap"></i>{{ db_trans('kanda_sacrament_summary') }}</span>
                    <h1 class="sacrament-title mt-3 mb-2">{{ $pageTitle }}</h1>

                    <div class="sacrament-pill-wrap">
                        <span class="sacrament-pill"><i class="fas fa-users"></i>{{ number_format($stats['total_members'] ?? 0) }} {{ db_trans('members') }}</span>
                        <span class="sacrament-pill"><i class="fas fa-droplet"></i>{{ number_format($stats['baptized'] ?? 0) }} {{ db_trans('baptized') }}</span>
                        <span class="sacrament-pill sacrament-pill-warning"><i class="fas fa-certificate"></i>{{ number_format($stats['confirmation'] ?? 0) }} {{ db_trans('confirmation') }}</span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="sacrament-actions-grid">
                        <a href="{{ route('sacraments.index') }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-chart-line"></i></span>
                            <span><strong>{{ db_trans('dashboard') }}</strong></span>
                        </a>

                        <a href="{{ route('sacraments.jumuiyas') }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-people-group"></i></span>
                            <span><strong>{{ db_trans('jumuiya_summary') }}</strong></span>
                        </a>

                        <button type="button" class="sacrament-action-card border-0 text-start" data-bs-toggle="modal" data-bs-target="#kandaFilterModal">
                            <span class="sacrament-action-icon"><i class="fas fa-filter"></i></span>
                            <span><strong>{{ db_trans('filter_report') }}</strong></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($cards as $card)
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

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card sacrament-panel border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('kanda_performance_chart') }}</h5>
                            </div>
                            <span class="sacrament-panel-icon"><i class="fas fa-chart-bar"></i></span>
                        </div>
                        <div class="chart-shell"><canvas id="kandaIndexChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card sacrament-panel border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('quick_insight') }}</h5>
                            </div>
                        </div>

                        <div class="sacrament-summary-grid">
                            <div class="sacrament-summary-item">
                                <div class="sacrament-kpi-label">{{ db_trans('average_members_per_kanda') }}</div>
                                <div class="sacrament-kpi-value">{{ ($stats['total_kandas'] ?? 0) > 0 ? number_format(($stats['total_members'] ?? 0) / max(1, $stats['total_kandas']), 1) : '0.0' }}</div>
                            </div>

                            <div class="sacrament-summary-item">
                                <div class="sacrament-kpi-label">{{ db_trans('baptism_coverage') }}</div>
                                <div class="sacrament-kpi-value">{{ ($stats['total_members'] ?? 0) > 0 ? number_format((($stats['baptized'] ?? 0) / max(1, $stats['total_members'])) * 100, 1) : '0.0' }}%</div>
                            </div>

                            <div class="sacrament-summary-item">
                                <div class="sacrament-kpi-label">{{ db_trans('confirmation_coverage') }}</div>
                                <div class="sacrament-kpi-value">{{ ($stats['total_members'] ?? 0) > 0 ? number_format((($stats['confirmation'] ?? 0) / max(1, $stats['total_members'])) * 100, 1) : '0.0' }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card sacrament-table-card border-0">
            <div class="card-body p-4">
                <div class="sacrament-panel-head">
                    <div>
                        <h5 class="sacrament-panel-title">{{ db_trans('kanda_sacrament_summary_table') }}</h5>
                    </div>

                    @can('sacraments.kanda.view')
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('pdf.sacraments.kandas.export', request()->query()) }}" class="btn btn-sm btn-danger rounded-pill">
                                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                            </a>

                            <a href="{{ route('sacraments.kandas.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill">
                                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                            </a>
                        </div>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="kandaSummaryTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('kanda') }}</th>
                                <th>{{ db_trans('code') }}</th>
                                <th>{{ db_trans('jumuiyas') }}</th>
                                <th>{{ db_trans('familias') }}</th>
                                <th>{{ db_trans('members') }}</th>
                                <th>{{ db_trans('baptized') }}</th>
                                <th>{{ db_trans('communion') }}</th>
                                <th>{{ db_trans('confirmation') }}</th>
                                <th>{{ db_trans('married') }}</th>
                                <th>{{ db_trans('receiving_eucharist') }}</th>
                                <th>{{ db_trans('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td><strong>{{ $row['name'] }}</strong></td>
                                    <td>{{ $row['code'] ?: '—' }}</td>
                                    <td>{{ number_format($row['jumuiyas']) }}</td>
                                    <td>{{ number_format($row['familias']) }}</td>
                                    <td>{{ number_format($row['members']) }}</td>
                                    <td>{{ number_format($row['baptized']) }}</td>
                                    <td>{{ number_format($row['communion']) }}</td>
                                    <td>{{ number_format($row['confirmation']) }}</td>
                                    <td>{{ number_format($row['married']) }}</td>
                                    <td>{{ number_format($row['eucharist']) }}</td>
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

        @include('admin.sacraments.partials.filter-modal', [
            'modalId' => 'kandaFilterModal',
            'action' => route('sacraments.kandas'),
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
            if (window.jQuery && document.getElementById('kandaSummaryTable')) {
                $('#kandaSummaryTable').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    responsive: true,
                    language: {
                        emptyTable: @json(db_trans('no_data_found')),
                    }
                });
            }

            const chartEl = document.getElementById('kandaIndexChart');
            if (chartEl) {
                new Chart(chartEl, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['labels'] ?? []),
                        datasets: [
                            { label: @json(db_trans('members')), data: @json($chartData['members'] ?? []), backgroundColor: '#2563eb', borderRadius: 10 },
                            { label: @json(db_trans('baptized')), data: @json($chartData['baptized'] ?? []), backgroundColor: '#16a34a', borderRadius: 10 },
                            { label: @json(db_trans('confirmation')), data: @json($chartData['confirmation'] ?? []), backgroundColor: '#7c3aed', borderRadius: 10 }
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