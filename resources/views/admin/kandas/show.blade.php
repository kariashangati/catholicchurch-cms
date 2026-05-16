@extends('layouts.admin')

@section('title', db_trans('kanda_details'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/kanda-pages.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    <div class="kanda-pages">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                    <span class="dashboard-hero-badge">{{ $hero['eyebrow'] }}</span>
                    <h2 class="dashboard-title mb-2">{{ $hero['title'] }}</h2>
                    <p class="dashboard-subtitle mb-3">{{ $hero['subtitle'] }}</p>

                    <div class="hero-meta-wrap">
                        <span class="hero-pill">
                            <i class="fas fa-compass me-2"></i>{{ $hero['scope_badge'] }}
                        </span>
                        <span class="hero-pill">
                            <i class="fas fa-calendar-day me-2"></i>{{ $hero['today'] }}
                        </span>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="hero-filter-card">
                        <form method="GET" action="{{ route('kandas.show', $kanda) }}">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small mb-1">{{ db_trans('year') }}</label>
                                    <select name="year" class="form-select form-select-sm">
                                        @foreach($filterOptions['years'] as $year)
                                            <option value="{{ $year }}" @selected((int) $filters['year'] === (int) $year)>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="form-label small mb-1">{{ db_trans('month') }}</label>
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
                                    <label class="form-label small mb-1">{{ db_trans('jumuiya') }}</label>
                                    <select name="jumuiya_id" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_jumuiyas') }}</option>
                                        @foreach($filterOptions['jumuiyas'] as $optionJumuiya)
                                            <option value="{{ $optionJumuiya->id }}" @selected((string) $filters['jumuiya_id'] === (string) $optionJumuiya->id)>
                                                {{ $optionJumuiya->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-light btn-sm w-100">
                                        <i class="fas fa-filter me-1"></i>{{ db_trans('apply_filters') }}
                                    </button>
                                </div>

                                <div class="col-12 d-flex gap-2 mt-1">
                                    <a href="{{ $showActions['back'] ?? '#' }}" class="btn btn-outline-light btn-sm flex-fill">
                                        <i class="fas fa-arrow-left me-1"></i>{{ db_trans('back') }}
                                    </a>

                                    @if(!empty($showActions['edit']))
                                        <a href="{{ $showActions['edit'] }}" class="btn btn-outline-light btn-sm flex-fill">
                                            <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($summaryCards as $card)
                <div class="col-xl-3 col-md-6">
                    @if(!empty($card['route']))
                        <a href="{{ $card['route'] }}" class="text-decoration-none">
                    @endif

                    <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0 {{ !empty($card['route']) ? 'cursor-pointer' : '' }}">
                        <div class="card-body">
                            <div class="stat-top-row">
                                <div class="stat-icon">
                                    <i class="{{ $card['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="stat-label">{{ $card['title'] }}</div>
                            <div class="stat-number">{{ $card['value'] }}</div>
                        </div>
                    </div>

                    @if(!empty($card['route']))
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card analytics-panel border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('monthly_finance_trends') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="chart-shell chart-shell-lg">
                            <canvas id="kandaMonthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card analytics-panel border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('members_by_jumuiya') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>
                        <div class="chart-shell chart-shell-lg">
                            <canvas id="membersByJumuiyaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-panel border-0 mb-4">
            <div class="card-header table-panel-header border-0 p-4 d-flex flex-column gap-3">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-3">
                    <div>
                        <h5 class="panel-title mb-1">{{ db_trans('jumuiya_breakdown') }}</h5>
                    </div>

                    <form
                        action="{{ $showActions['pdf_export'] ?? '#' }}"
                        method="GET"
                        target="_blank"
                        class="d-flex flex-column flex-md-row align-items-md-end gap-2"
                    >
                        <input type="hidden" name="from_date" value="{{ $dateFilters['from_date'] ?? '' }}">
                        <input type="hidden" name="to_date" value="{{ $dateFilters['to_date'] ?? '' }}">

                        <div class="small text-muted me-md-2">
                            {{ db_trans('period') }}: {{ $dateFilters['period_label'] ?? db_trans('all_time') }}
                        </div>

                        <div class="d-flex gap-2">
                            @if(!empty($showActions['pdf_export']))
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                                </button>
                            @endif

                            @if(!empty($showActions['excel_export']))
                                <a
                                    href="{{ $showActions['excel_export'] ? $showActions['excel_export'].'?'.http_build_query([
                                        'from_date' => $dateFilters['from_date'] ?? null,
                                        'to_date' => $dateFilters['to_date'] ?? null,
                                    ]) : '#' }}"
                                    class="btn btn-sm btn-success"
                                >
                                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div style="min-width: 260px;">
                    <input
                        type="text"
                        id="kandaJumuiyaSearch"
                        class="form-control"
                        placeholder="{{ db_trans('search') ?: 'Search' }}"
                    >
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive dashboard-table-wrap">
                    <table id="kandaJumuiyaTable" class="table align-middle table-hover mb-0 dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('jumuiya') }}</th>
                                <th>{{ db_trans('familias') }}</th>
                                <th>{{ db_trans('members') }}</th>
                                <th>{{ db_trans('offerings') }}</th>
                                <th>{{ db_trans('tithes') }}</th>

                                @foreach($breakdownContributionTypes as $type)
                                    <th>{{ $type['name'] }}</th>
                                @endforeach

                                <th>{{ db_trans('grand_total') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jumuiyas as $jumuiya)
                                <tr>
                                    <td class="fw-semibold">{{ $jumuiya['name'] }}</td>
                                    <td>{{ number_format($jumuiya['familias_count']) }}</td>
                                    <td>{{ number_format($jumuiya['members_count']) }}</td>
                                    <td>{{ number_format($jumuiya['offering_total'], 2) }}</td>
                                    <td>{{ number_format($jumuiya['tithe_total'], 2) }}</td>

                                    @foreach($breakdownContributionTypes as $type)
                                        <td>{{ number_format($jumuiya['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}</td>
                                    @endforeach

                                    <td class="fw-bold text-success">{{ number_format($jumuiya['grand_total'], 2) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ $jumuiya['details_url'] ?? '#' }}" class="btn btn-sm btn-outline-primary btn-modern">
                                                {{ db_trans('open') }}
                                            </a>
                                            <a href="{{ $jumuiya['report_url'] ?? '#' }}" class="btn btn-sm btn-outline-secondary btn-modern">
                                                {{ db_trans('report') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 7 + $breakdownContributionTypes->count() }}" class="text-center py-5 text-muted">
                                        {{ db_trans('no_data_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card table-panel border-0">
            <div class="card-header table-panel-header border-0 p-4 d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-3">
                <div>
                    <h5 class="panel-title mb-1">{{ db_trans('recent_members') }}</h5>
                </div>

                <div class="d-flex gap-2">
                    @if(!empty($showActions['recent_members_pdf_export']))
                        <a
                            href="{{ $showActions['recent_members_pdf_export'].'?'.http_build_query([
                                'year' => $filters['year'] ?? null,
                                'month' => $filters['month'] ?? null,
                                'jumuiya_id' => $filters['jumuiya_id'] ?? null,
                            ]) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(!empty($showActions['recent_members_excel_export']))
                        <a
                            href="{{ $showActions['recent_members_excel_export'].'?'.http_build_query([
                                'year' => $filters['year'] ?? null,
                                'month' => $filters['month'] ?? null,
                                'jumuiya_id' => $filters['jumuiya_id'] ?? null,
                            ]) }}"
                            class="btn btn-sm btn-success"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive dashboard-table-wrap">
                    <table id="recentMembersTable" class="table align-middle table-hover mb-0 dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('jumuiya') }}</th>
                                <th>{{ db_trans('gender') }}</th>
                                <th>{{ db_trans('phone') }}</th>
                                <th>{{ db_trans('joined') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMembers as $member)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $member->full_name }}</div>
                                        <div class="small text-muted">{{ $member->familia?->name }}</div>
                                    </td>
                                    <td>{{ $member->familia?->jumuiya?->name ?? '—' }}</td>
                                    <td>{{ $member->gender ?? '—' }}</td>
                                    <td>{{ $member->phone ?? '—' }}</td>
                                    <td>{{ $member->created_at?->format('M d, Y') }}</td>
                                    <td>
                                        @if(Route::has('members.show'))
                                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline-primary btn-modern">
                                                {{ db_trans('view') ?: db_trans('open') }}
                                            </a>
                                        @else
                                            <a href="{{ url('/members/'.$member->id) }}" class="btn btn-sm btn-outline-primary btn-modern">
                                                {{ db_trans('view') ?: db_trans('open') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        {{ db_trans('no_members_found') }}
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
    const monthlyCtx = document.getElementById('kandaMonthlyChart');
    if (monthlyCtx) {
        const baseGrid = '#eef2f7';
        const baseTick = '#64748b';

        new Chart(monthlyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($monthlyChart['labels'] ?? []),
                datasets: [
                    {
                        label: @json(db_trans('tithes')),
                        data: @json($monthlyChart['tithes'] ?? []),
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    },
                    {
                        label: @json(db_trans('offerings')),
                        data: @json($monthlyChart['offerings'] ?? []),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    },
                    @foreach(($monthlyChart['contributions'] ?? collect()) as $series)
                        {
                            label: @json($series['label']),
                            data: @json($series['data']),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.08)',
                            fill: false,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5
                        },
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 18
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: baseGrid, drawBorder: false },
                        ticks: { color: baseTick, padding: 10 }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: baseTick, padding: 8 }
                    }
                }
            }
        });
    }

    const membersPieCtx = document.getElementById('membersByJumuiyaChart');
    if (membersPieCtx) {
        new Chart(membersPieCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: @json($membersByJumuiyaChart['labels'] ?? []),
                datasets: [{
                    data: @json($membersByJumuiyaChart['values'] ?? []),
                    borderWidth: 2
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
                            padding: 14
                        }
                    }
                }
            }
        });
    }

    if (typeof window.jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
        const languageConfig = {
            search: '',
            searchPlaceholder: @json(db_trans('search') ?: 'Search'),
            lengthMenu: @json((db_trans('show') ?: 'Show') . ' _MENU_ ' . (db_trans('entries') ?: 'entries')),
            info: @json(db_trans('showing_records') ?: 'Showing _START_ to _END_ of _TOTAL_ records'),
            infoEmpty: @json(db_trans('no_records_available') ?: 'No records available'),
            zeroRecords: @json(db_trans('no_matching_records') ?: 'No matching records found'),
            infoFiltered: @json(db_trans('filtered_from_total') ?: '(filtered from _MAX_ total records)'),
            paginate: {
                first: @json(db_trans('first') ?: 'First'),
                last: @json(db_trans('last') ?: 'Last'),
                next: @json(db_trans('next') ?: 'Next'),
                previous: @json(db_trans('previous') ?: 'Previous')
            }
        };

        const jumuiyaTable = $('#kandaJumuiyaTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            responsive: true,
            autoWidth: false,
            language: languageConfig,
            order: [[0, 'asc']]
        });

        $('#kandaJumuiyaSearch').on('keyup change', function () {
            jumuiyaTable.search(this.value).draw();
        });

        $('#recentMembersTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            responsive: true,
            autoWidth: false,
            language: languageConfig,
            order: [[4, 'desc']]
        });
    }
});
</script>
@endpush