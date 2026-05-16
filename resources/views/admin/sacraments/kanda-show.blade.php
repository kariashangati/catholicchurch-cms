@extends('layouts.admin')

@section('title', $pageTitle ?? db_trans('kanda_sacrament_report'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/sacraments-module-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    @php
        $cards = [
            ['label' => db_trans('total_members'), 'value' => number_format($stats['total_members'] ?? 0), 'icon' => 'fas fa-users', 'tone' => 'primary'],
            ['label' => db_trans('male_members'), 'value' => number_format($stats['male_members'] ?? 0), 'icon' => 'fas fa-mars', 'tone' => 'blue'],
            ['label' => db_trans('female_members'), 'value' => number_format($stats['female_members'] ?? 0), 'icon' => 'fas fa-venus', 'tone' => 'danger'],
            ['label' => db_trans('baptized'), 'value' => number_format($stats['baptized'] ?? 0), 'icon' => 'fas fa-droplet', 'tone' => 'green'],
            ['label' => db_trans('confirmation'), 'value' => number_format($stats['confirmation'] ?? 0), 'icon' => 'fas fa-certificate', 'tone' => 'teal'],
            ['label' => db_trans('receiving_eucharist'), 'value' => number_format($stats['eucharist'] ?? 0), 'icon' => 'fas fa-wine-glass', 'tone' => 'amber'],
        ];

        $mapGender = function ($gender) {
            $value = strtolower((string) $gender);
            return match ($value) {
                'male' => db_trans('male'),
                'female' => db_trans('female'),
                default => $gender ?: '—',
            };
        };
    @endphp

    <div class="sacraments-v3 admin-ui-v3">
        <div class="sacrament-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <span class="sacrament-hero-badge"><i class="fas fa-sitemap"></i>{{ db_trans('kanda_sacrament_report') }}</span>
                    <h1 class="sacrament-title mt-3 mb-2">{{ $kanda->name }}</h1>

                    <div class="sacrament-pill-wrap">
                        <span class="sacrament-pill"><i class="fas fa-code"></i>{{ $kanda->code ?: '—' }}</span>
                        <span class="sacrament-pill"><i class="fas fa-users"></i>{{ number_format($stats['active_members'] ?? 0) }} {{ db_trans('active_members') }}</span>
                        <span class="sacrament-pill"><i class="fas fa-droplet"></i>{{ $rates['baptized_rate'] ?? 0 }}% {{ db_trans('baptism_rate') }}</span>
                        <span class="sacrament-pill sacrament-pill-warning"><i class="fas fa-certificate"></i>{{ $rates['confirmation_rate'] ?? 0 }}% {{ db_trans('confirmation_rate') }}</span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="sacrament-actions-grid">
                        <a href="{{ route('sacraments.kandas') }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-arrow-left"></i></span>
                            <span><strong>{{ db_trans('back') }}</strong></span>
                        </a>

                        <button type="button" class="sacrament-action-card border-0 text-start" data-bs-toggle="modal" data-bs-target="#kandaDetailFilterModal">
                            <span class="sacrament-action-icon"><i class="fas fa-filter"></i></span>
                            <span><strong>{{ db_trans('filter_members') }}</strong></span>
                        </button>

                        <a href="{{ route('sacraments.jumuiyas') }}" class="sacrament-action-card">
                            <span class="sacrament-action-icon"><i class="fas fa-people-group"></i></span>
                            <span><strong>{{ db_trans('jumuiya_summary') }}</strong></span>
                        </a>
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
                                <h5 class="sacrament-panel-title">{{ db_trans('jumuiya_comparison_inside_kanda') }}</h5>
                            </div>
                            <span class="sacrament-panel-icon"><i class="fas fa-chart-column"></i></span>
                        </div>
                        <div class="chart-shell"><canvas id="kandaDetailChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card sacrament-panel border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('sacrament_completion_rate') }}</h5>
                            </div>
                            <span class="sacrament-chip">{{ db_trans('rates') }}</span>
                        </div>

                        <div class="sacrament-rate-grid">
                            @foreach([
                                ['label' => db_trans('baptism_rate'), 'value' => $rates['baptized_rate'] ?? 0],
                                ['label' => db_trans('communion_rate'), 'value' => $rates['communion_rate'] ?? 0],
                                ['label' => db_trans('confirmation_rate'), 'value' => $rates['confirmation_rate'] ?? 0],
                                ['label' => db_trans('marriage_rate'), 'value' => $rates['marriage_rate'] ?? 0],
                                ['label' => db_trans('eucharist_rate'), 'value' => $rates['eucharist_rate'] ?? 0],
                            ] as $rate)
                                <div class="sacrament-rate-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="sacrament-rate-label">{{ $rate['label'] }}</span>
                                        <span class="sacrament-rate-value">{{ $rate['value'] }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ min(100, max(0, (float) $rate['value'])) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-5">
                <div class="card sacrament-table-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('jumuiya_breakdown') }}</h5>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-hover" id="kandaJumuiyaSummaryTable">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('jumuiya') }}</th>
                                        <th>{{ db_trans('members') }}</th>
                                        <th>{{ db_trans('baptized') }}</th>
                                        <th>{{ db_trans('confirmation') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td><strong>{{ $row['name'] }}</strong></td>
                                            <td>{{ number_format($row['members']) }}</td>
                                            <td>{{ number_format($row['baptized']) }}</td>
                                            <td>{{ number_format($row['confirmation']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card sacrament-table-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="sacrament-panel-head">
                            <div>
                                <h5 class="sacrament-panel-title">{{ db_trans('member_sacrament_details') }}</h5>
                            </div>

                            @can('sacraments.kanda.view')
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('pdf.sacraments.kandas.members.export', [$kanda] + request()->query()) }}" class="btn btn-sm btn-danger rounded-pill">
                                        <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                                    </a>

                                    <a href="{{ route('sacraments.kandas.members.export.excel', [$kanda] + request()->query()) }}" class="btn btn-sm btn-success rounded-pill">
                                        <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                                    </a>
                                </div>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-hover" id="kandaMembersTable">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('member') }}</th>
                                        <th>{{ db_trans('familia') }}</th>
                                        <th>{{ db_trans('jumuiya') }}</th>
                                        <th>{{ db_trans('gender') }}</th>
                                        <th>{{ db_trans('baptized') }}</th>
                                        <th>{{ db_trans('communion') }}</th>
                                        <th>{{ db_trans('confirmation') }}</th>
                                        <th>{{ db_trans('married') }}</th>
                                        <th>{{ db_trans('receiving_eucharist') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($members as $member)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="sacrament-avatar">{{ strtoupper(substr($member->first_name ?? 'M', 0, 1)) }}</span>
                                                    <div class="sacrament-row-title">
                                                        <strong>{{ $member->full_name }}</strong>
                                                        <small>{{ $member->member_code ?: '—' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $member->familia?->name ?: '—' }}</td>
                                            <td>{{ $member->familia?->jumuiya?->name ?: '—' }}</td>
                                            <td>{{ $mapGender($member->gender) }}</td>
                                            <td>{!! $member->is_baptized ? '<span class="sacrament-soft-badge success">'.e(db_trans('yes')).'</span>' : '<span class="sacrament-soft-badge">'.e(db_trans('no')).'</span>' !!}</td>
                                            <td>{!! $member->has_communion ? '<span class="sacrament-soft-badge success">'.e(db_trans('yes')).'</span>' : '<span class="sacrament-soft-badge">'.e(db_trans('no')).'</span>' !!}</td>
                                            <td>{!! $member->has_confirmation ? '<span class="sacrament-soft-badge success">'.e(db_trans('yes')).'</span>' : '<span class="sacrament-soft-badge">'.e(db_trans('no')).'</span>' !!}</td>
                                            <td>{!! $member->is_married ? '<span class="sacrament-soft-badge success">'.e(db_trans('yes')).'</span>' : '<span class="sacrament-soft-badge">'.e(db_trans('no')).'</span>' !!}</td>
                                            <td>{!! $member->receives_eucharist ? '<span class="sacrament-soft-badge success">'.e(db_trans('yes')).'</span>' : '<span class="sacrament-soft-badge">'.e(db_trans('no')).'</span>' !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($members, 'links'))
                            <div class="mt-3">
                                {{ $members->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @include('admin.sacraments.partials.filter-modal', [
            'modalId' => 'kandaDetailFilterModal',
            'action' => route('sacraments.kandas.show', $kanda),
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
            if (window.jQuery) {
                if (document.getElementById('kandaJumuiyaSummaryTable')) {
                    $('#kandaJumuiyaSummaryTable').DataTable({
                        paging: false,
                        info: false,
                        responsive: true,
                        searching: false,
                        language: { emptyTable: @json(db_trans('no_data_found')) }
                    });
                }

                if (document.getElementById('kandaMembersTable')) {
                    $('#kandaMembersTable').DataTable({
                        paging: false,
                        info: false,
                        responsive: true,
                        order: [[0, 'asc']],
                        language: { emptyTable: @json(db_trans('no_data_found')) }
                    });
                }
            }

            const chartEl = document.getElementById('kandaDetailChart');
            if (chartEl) {
                new Chart(chartEl, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['labels'] ?? []),
                        datasets: [
                            { label: @json(db_trans('baptized')), data: @json($chartData['baptized'] ?? []), backgroundColor: '#2563eb', borderRadius: 10 },
                            { label: @json(db_trans('communion')), data: @json($chartData['communion'] ?? []), backgroundColor: '#16a34a', borderRadius: 10 },
                            { label: @json(db_trans('confirmation')), data: @json($chartData['confirmation'] ?? []), backgroundColor: '#7c3aed', borderRadius: 10 },
                            { label: @json(db_trans('married')), data: @json($chartData['married'] ?? []), backgroundColor: '#d97706', borderRadius: 10 },
                            { label: @json(db_trans('receiving_eucharist')), data: @json($chartData['eucharist'] ?? []), backgroundColor: '#0f766e', borderRadius: 10 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        },
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        });
    </script>
@endpush