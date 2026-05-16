@extends('layouts.admin')
@section('title', db_trans('sermons_center'))
@push('styles')<link rel="stylesheet" href="{{ asset('admin/css/sermons-module-v1.css') }}">@endpush
@section('content')
<div class="sermons-module">
    <div class="sermon-hero">
        <div>
            <span class="sermon-hero-badge"><i class="fas fa-book-bible"></i>{{ db_trans('sermons') }}</span>
            <h2 class="sermon-hero-title">{{ db_trans('sermons_center') }}</h2>
            <p class="sermon-hero-subtitle">{{ db_trans('sermons_center_subtitle') }}</p>
        </div>
        <div class="sermon-hero-actions">
            @can('sermons.create')<a href="{{ route('sermons.create') }}" class="sermon-hero-action"><i class="fas fa-pen"></i>{{ db_trans('write_sermon') }}</a>@endcan
            @can('sermons.requests.view')<a href="{{ route('sermons.requests.index') }}" class="sermon-hero-action"><i class="fas fa-inbox"></i>{{ db_trans('sermon_requests') }}</a>@endcan
        </div>
    </div>
    <div class="row g-4">
        @foreach($kpis as $card)
            <div class="col-xl-3 col-md-6"><div class="card sermon-kpi-card sermon-tone-{{ $card['tone'] }} border-0"><div class="card-body"><div class="sermon-kpi-top"><span class="sermon-kpi-icon"><i class="{{ $card['icon'] }}"></i></span><span class="sermon-kpi-chip">{{ db_trans('overview') }}</span></div><div class="sermon-kpi-label">{{ $card['title'] }}</div><div class="sermon-kpi-value">{{ number_format($card['value']) }}</div></div></div></div>
        @endforeach
    </div>
    <div class="row g-4">
        <div class="col-xl-7"><div class="card sermon-panel border-0 h-100"><div class="card-body"><div class="sermon-panel-head"><h5>{{ db_trans('sermons_by_month') }}</h5><span class="sermon-panel-badge">{{ db_trans('chart') }}</span></div><div class="sermon-chart-shell"><canvas id="sermonMonthlyChart"></canvas></div></div></div></div>
        <div class="col-xl-5"><div class="card sermon-panel border-0 h-100"><div class="card-body"><div class="sermon-panel-head"><h5>{{ db_trans('request_status_distribution') }}</h5><span class="sermon-panel-badge">{{ db_trans('chart') }}</span></div><div class="sermon-chart-shell"><canvas id="sermonRequestStatusChart"></canvas></div></div></div></div>
    </div>
    <div class="row g-4">
        @include('admin.sermons.partials.dashboard-table',['title'=>db_trans('recent_sermons'),'rows'=>$recentSermons,'type'=>'sermons'])
        @include('admin.sermons.partials.dashboard-table',['title'=>db_trans('new_sermon_requests'),'rows'=>$recentRequests,'type'=>'requests'])
        @include('admin.sermons.partials.dashboard-table',['title'=>db_trans('recent_sermon_recipients'),'rows'=>$recentRecipients,'type'=>'recipients'])
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>window.sermonDashboardData={monthly:@json($monthly),requestStatus:@json($requestStatus)};</script>
<script src="{{ asset('admin/js/sermons-module-v1.js') }}"></script>
@endpush
