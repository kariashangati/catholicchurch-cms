@extends('layouts.admin')

@section('title', db_trans('jumuiya_members'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/jumuiya-module-v3.css') }}">
@endpush

@section('content')
    <div class="admin-ui-v3 jumuiya-module-v3">
        <div class="ui-page-hero jumuiya-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="ui-page-hero-badge">{{ db_trans('jumuiya_members') }}</span>
                    <h1 class="ui-page-hero-title">{{ $jumuiya->name }}</h1>

                    <div class="ui-page-hero-pills mt-3">
                        <span class="ui-hero-pill">
                            <i class="fas fa-sitemap"></i>{{ $jumuiya->kanda?->name ?? '—' }}
                        </span>
                        <span class="ui-hero-pill">
                            <i class="fas fa-users"></i>{{ number_format($stats['total_members']) }} {{ db_trans('members') }}
                        </span>
                        <span class="ui-hero-pill">
                            <i class="fas fa-check-circle"></i>{{ number_format($stats['active_members']) }} {{ db_trans('active_members') }}
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                        @can('jumuiyas.members.view')
                            <a href="{{ route('jumuiyas.members.index', $jumuiya) }}" class="btn btn-light btn-lg ui-hero-action-btn jumuiya-hero-action">
                                <i class="fas fa-users me-2"></i>{{ db_trans('members') }}
                            </a>
                        @endcan

                        @can('members.create')
                            <a href="{{ route('members.index', ['open' => 'create']) }}" class="btn btn-primary btn-lg ui-hero-action-btn jumuiya-hero-action">
                                <i class="fas fa-user-plus me-2"></i>{{ db_trans('create_member') }}
                            </a>
                        @endcan

                        @can('jumuiya-reports.view')
                            <a href="{{ route('jumuiya-reports.show', $jumuiya) }}" class="btn btn-success btn-lg ui-hero-action-btn jumuiya-hero-action">
                                <i class="fas fa-chart-line me-2"></i>{{ db_trans('view_report') }}
                            </a>
                        @endcan

                        @can('jumuiyas.members.view')
                            <a href="{{ route('pdf.jumuiyas.members.pdf', $jumuiya) }}" class="btn btn-danger btn-lg ui-hero-action-btn jumuiya-hero-action">
                                <i class="fas fa-file-pdf me-2"></i>{{ db_trans('export_pdf') }}
                            </a>

                            <a href="{{ route('jumuiyas.members.excel', $jumuiya) }}" class="btn btn-success btn-lg ui-hero-action-btn jumuiya-hero-action">
                                <i class="fas fa-file-excel me-2"></i>{{ db_trans('export_excel') }}
                            </a>
                        @endcan

                        <a href="{{ route('jumuiyas.show', $jumuiya) }}" class="btn btn-outline-light btn-lg jumuiya-hero-action">
                            <i class="fas fa-arrow-left me-2"></i>{{ db_trans('back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-stat-card--primary h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-users"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('total_members') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['total_members']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-stat-card--success h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('active_members') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['active_members']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-stat-card--info h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-mars"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('male_members') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['male_members']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-stat-card--warning h-100">
                    <div class="ui-stat-card__icon"><i class="fas fa-venus"></i></div>
                    <div class="ui-stat-card__body">
                        <div class="ui-stat-card__label">{{ db_trans('female_members') }}</div>
                        <div class="ui-stat-card__value">{{ number_format($stats['female_members']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card dashboard-panel h-100">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-1">{{ db_trans('gender_distribution') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 320px;">
                            <canvas id="jumuiyaMemberGenderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card dashboard-panel h-100">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-1">{{ db_trans('top_familias_by_members') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 320px;">
                            <canvas id="jumuiyaMemberFamiliesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-panel">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="fw-bold text-dark mb-1">{{ db_trans('jumuiya_members') }}</h5>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="search" class="form-control form-control-sm admin-table-search" data-table-target="#jumuiyaMembersTable" placeholder="{{ db_trans('search_members_table') }}">

                        <select class="form-select form-select-sm admin-table-length" data-table-target="#jumuiyaMembersTable">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0 admin-data-table" id="jumuiyaMembersTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('member_code') }}</th>
                                <th>{{ db_trans('gender') }}</th>
                                <th>{{ db_trans('phone') }}</th>
                                <th>{{ db_trans('familia') }}</th>
                                <th>{{ db_trans('family_role') }}</th>
                                <th>{{ db_trans('joined') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="member-avatar-sm me-3">
                                                {{ strtoupper(substr($member->first_name ?? 'M', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $member->full_name }}</div>
                                                <div class="small text-muted">
                                                    {{ $member->is_active ? db_trans('active') : db_trans('inactive') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $member->member_code ?? '—' }}</td>
                                    <td>{{ $member->gender ?? '—' }}</td>
                                    <td>{{ $member->phone ?? '—' }}</td>
                                    <td>{{ $member->familia?->name ?? '—' }}</td>
                                    <td>{{ $member->family_role ?? '—' }}</td>
                                    <td>{{ optional($member->created_at)->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ db_trans('actions') }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4">
                                                @can('members.view')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('members.show', $member) }}">
                                                            <i class="fas fa-eye me-2 text-primary"></i>{{ db_trans('view') }}
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('members.update')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('members.edit', $member) }}">
                                                            <i class="fas fa-pen me-2 text-warning"></i>{{ db_trans('edit') }}
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('members.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('members.destroy', $member) }}" method="POST" class="js-confirm-delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash me-2"></i>{{ db_trans('delete') }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">{{ db_trans('no_members_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent border-0 p-4">
                <div class="admin-table-pagination" data-table-target="#jumuiyaMembersTable"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const genderCtx = document.getElementById('jumuiyaMemberGenderChart');
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

            const familiaCtx = document.getElementById('jumuiyaMemberFamiliesChart');
            if (familiaCtx) {
                new Chart(familiaCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['familiaLabels']),
                        datasets: [{
                            label: '{{ db_trans('members') }}',
                            data: @json($chartData['familiaMemberCounts']),
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1.5,
                            borderRadius: 10,
                            maxBarThickness: 36
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10,
                                    color: '#334155'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#6b7280', font: { size: 11 } },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#6b7280', font: { size: 11 }, precision: 0 },
                                grid: { color: 'rgba(148, 163, 184, 0.14)' }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush