@extends('layouts.admin')

@section('title', db_trans('dashboard'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/dashboard-v3.css') }}">
@endpush

@section('content')
    <div class="dashboard-v3">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                   
                    <h2 class="dashboard-title mb-2">{{ $page['title'] }}</h2>
                    <p class="dashboard-subtitle mb-3">{{ $hero['title'] }} · {{ $page['today'] }}</p>
               
                </div>

                <div class="col-lg-4">
                    <form method="GET" action="{{ route('dashboard') }}" class="card border-0 shadow-sm p-3 bg-white bg-opacity-10">
                        <label for="dashboard-date" class="form-label text-white fw-semibold mb-2">{{ db_trans('filter_dashboard_by_date') }}</label>
                        <div class="d-flex gap-2">
                            <input id="dashboard-date" type="date" name="date" value="{{ $selectedDate?->toDateString() }}" class="form-control">
                            <button class="btn btn-light btn-modern" type="submit">{{ db_trans('apply') }}</button>
                        </div>
                        @if($selectedDate)
                            <a href="{{ route('dashboard') }}" class="text-white small mt-2 d-inline-block">{{ db_trans('clear_filter') }}</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($summaryCards as $card)
                <div class="col-xl-3 col-md-6">
                    @if(!empty($card['can_open']) && !empty($card['url']))
                        <a href="{{ $card['url'] }}" class="text-decoration-none text-reset d-block h-100">
                            <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0 stat-card-link">
                                <div class="card-body">
                                    <div class="stat-top-row">
                                        <div class="stat-icon"><i class="{{ $card['icon'] }}"></i></div>
                                        <span class="stat-chip">{{ db_trans('overview') }}</span>
                                    </div>
                                    <div class="stat-label">{{ $card['title'] }}</div>
                                    <div class="stat-number">{{ $card['value'] }}</div>
                                    <div class="mt-3">
                                        <span class="btn btn-sm btn-light btn-modern">{{ $card['link_label'] }} <i class="fas fa-arrow-right ms-1"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @else
                        <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0 stat-card-disabled" title="{{ $card['disabled_reason'] ?? db_trans('permission_required') }}">
                            <div class="card-body">
                                <div class="stat-top-row">
                                    <div class="stat-icon"><i class="{{ $card['icon'] }}"></i></div>
                                    <span class="stat-chip">{{ db_trans('overview') }}</span>
                                </div>
                                <div class="stat-label">{{ $card['title'] }}</div>
                                <div class="stat-number">{{ $card['value'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <a href="{{ $chartLinks['financeChart']['url'] ?? url('/finance') }}" class="text-decoration-none text-reset d-block h-100">
                    <div class="card analytics-panel h-100 border-0">
                        <div class="card-body p-4">
                            <div class="panel-head">
                                <div>
                                    <h5 class="panel-title">{{ db_trans('financial_trend') }}</h5>
                                </div>
                                <div class="panel-icon"><i class="fas fa-chart-column"></i></div>
                            </div>
                            <div class="chart-shell chart-shell-lg"><canvas id="financeChart"></canvas></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-5">
                <div class="card analytics-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('kanda_distribution') }}</h5>
                            </div>
                            <a href="{{ $chartLinks['kandaDistributionChart']['url'] ?? url('/kandas') }}" class="btn btn-sm btn-outline-primary btn-modern">{{ db_trans('view_more') }}</a>
                        </div>
                        <div class="chart-shell"><canvas id="kandaDistributionChart"></canvas></div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('kanda') }}</th>
                                        <th>{{ db_trans('families') }}</th>
                                        <th>{{ db_trans('members') }}</th>
                                        <th>{{ db_trans('jumuiyas') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kandaDistributionChart['rows'] as $row)
                                        <tr>
                                            <td class="fw-semibold">{{ $row['name'] }}</td>
                                            <td>{{ number_format($row['familias']) }}</td>
                                            <td>{{ number_format($row['members']) }}</td>
                                            <td>{{ number_format($row['jumuiyas']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">{{ db_trans('no_kanda_data_found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-panel border-0">
            <div class="card-header table-panel-header border-0 p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="panel-title mb-1">{{ db_trans('recent_activities') }}</h5>
                    </div>
                    <a href="{{ Route::has('system.activity-logs.index') ? route('system.activity-logs.index') : url('/system/activity-logs') }}" class="btn btn-outline-primary btn-modern">
                        {{ db_trans('view_more') }} <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive dashboard-table-wrap">
                    <table class="table align-middle table-hover mb-0 dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('activity_type') }}</th>
                                <th>{{ db_trans('task_performed') }}</th>
                                <th>{{ db_trans('actor') }}</th>
                                <th>{{ db_trans('time') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $activity)
                                <tr>
                                    <td><span class="badge rounded-pill text-bg-light">{{ $activity['type'] }}</span></td>
                                    <td class="fw-semibold">{{ $activity['task'] }}</td>
                                    <td>{{ $activity['actor'] }}</td>
                                    <td>{{ $activity['time'] }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-modern" data-bs-toggle="modal" data-bs-target="#activityModal{{ $activity['id'] }}">
                                            {{ db_trans('open') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">{{ db_trans('no_recent_activity_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
                @foreach($recentActivities as $activity)
                    <div class="modal fade" id="activityModal{{ $activity['id'] }}" tabindex="-1" aria-labelledby="activityModalLabel{{ $activity['id'] }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="activityModalLabel{{ $activity['id'] }}">{{ db_trans('activity_details') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('activity_type') }}</div><div class="fw-semibold">{{ $activity['type'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('actor') }}</div><div class="fw-semibold">{{ $activity['actor'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('time') }}</div><div class="fw-semibold">{{ $activity['time'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('module') }}</div><div class="fw-semibold">{{ $activity['module'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('subject') }}</div><div class="fw-semibold">{{ $activity['subject'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('risk_level') }}</div><div class="fw-semibold">{{ $activity['risk_level'] }}</div></div>
                                        <div class="col-12"><div class="small text-muted">{{ db_trans('task_performed') }}</div><div class="fw-semibold">{{ $activity['task'] }}</div></div>
                                        <div class="col-12"><div class="small text-muted">{{ db_trans('description') }}</div><div class="fw-semibold">{{ $activity['description'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('ip_address') }}</div><div class="fw-semibold">{{ $activity['ip_address'] }}</div></div>
                                        <div class="col-md-6"><div class="small text-muted">{{ db_trans('method') }}</div><div class="fw-semibold">{{ $activity['method'] }}</div></div>
                                        <div class="col-12"><div class="small text-muted">{{ db_trans('route') }}</div><div class="fw-semibold text-break">{{ $activity['route_name'] }}</div></div>
                                        <div class="col-12"><div class="small text-muted">{{ db_trans('url') }}</div><div class="fw-semibold text-break">{{ $activity['url'] }}</div></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light btn-modern" data-bs-dismiss="modal">{{ db_trans('close') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.dashboardChartsData = {
            financeChart: @json($financeChart),
            kandaDistributionChart: @json($kandaDistributionChart),
            chartLinks: @json($chartLinks ?? []),
            labels: {
                offerings: @json(db_trans('offerings')),
                tithes: @json(db_trans('tithes')),
                contributions: @json(db_trans('contributions')),
                projects: @json(db_trans('projects')),
                members: @json(db_trans('members')),
                families: @json(db_trans('families')),
                jumuiyas: @json(db_trans('jumuiyas')),
            }
        };
    </script>
    <script src="{{ asset('admin/js/dashboard-v3.js') }}"></script>
@endpush
