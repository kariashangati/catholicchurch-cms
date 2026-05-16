@extends('layouts.admin')

@section('title', db_trans('send_sms'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/communication-sms-v2.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $campaigns = $campaigns ?? collect();
    $kpis = $kpis ?? [];
    $smsSettings = $sms_settings ?? [];
    $currency = $smsSettings['currency'] ?? 'TZS';
    $price = (float) ($smsSettings['sms_unit_price'] ?? 40);
    $statusClass = fn ($status) => match ($status) {
        'draft' => 'ui-status-muted',
        'scheduled' => 'ui-status-warning',
        'approved' => 'ui-status-info',
        'processing' => 'ui-status-info',
        'completed' => 'ui-status-approved',
        'failed', 'partially_failed' => 'ui-status-danger',
        'cancelled' => 'ui-status-muted',
        default => 'ui-status-muted',
    };
    $statusLabel = fn ($status) => db_trans('communication_status_' . ($status ?: 'unknown'));
@endphp

<div class="admin-ui-v4 communication-sms-v2">
    <div class="ui-page-hero sms-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-7">
                <span class="ui-page-badge"><i class="fas fa-paper-plane"></i>{{ db_trans('sms_center') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('send_sms') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-wallet"></i>{{ number_format((float) data_get($kpis, 'balance_units', 0), 2) }} {{ $currency }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-font"></i>{{ (int) data_get($smsSettings, 'segment_length', 160) }} {{ db_trans('characters') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-coins"></i>{{ number_format($price, 2) }} {{ $currency }} / {{ db_trans('sms_segment') }}</span>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="ui-actions-grid">
                    <a href="{{ route('admin.communication.sms.create') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-plus-circle"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('send_new_sms') }}</span>
                    </a>
                    <a href="{{ route('admin.communication.templates.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-file-alt"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('sms_templates') }}</span>
                    </a>
                    <a href="{{ route('admin.communication.sms.settings.edit') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-sliders-h"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('sms_settings') }}</span>
                    </a>
                    <a href="{{ route('admin.communication.balance.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-wallet"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('sms_balance') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success rounded-4">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger rounded-4">{{ session('error') }}</div>@endif

    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-bullhorn"></i></div><div class="ui-stat-label">{{ db_trans('total_campaigns') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'total_campaigns', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-clock"></i></div><div class="ui-stat-label">{{ db_trans('scheduled') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'scheduled_count', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-info"><i class="fas fa-spinner"></i></div><div class="ui-stat-label">{{ db_trans('processing') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'processing_count', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-check-circle"></i></div><div class="ui-stat-label">{{ db_trans('completed') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'completed_campaigns', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-comment-sms"></i></div><div class="ui-stat-label">{{ db_trans('sms_this_month') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'messages_this_month', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-money-bill-wave"></i></div><div class="ui-stat-label">{{ db_trans('month_cost') }}</div><div class="ui-stat-value">{{ number_format((float) data_get($kpis, 'estimated_cost_month', 0), 2) }}</div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading">
                    <div><h5 class="mb-1">{{ db_trans('sms_usage_trend') }}</h5></div>
                    <span class="ui-section-badge">{{ db_trans('last_14_days') }}</span>
                </div>
                <div class="sms-chart-shell"><canvas id="smsUsageTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><div><h5 class="mb-1">{{ db_trans('sms_statistics') }}</h5></div></div>
                <div class="sms-stat-list">
                    <div><span>{{ db_trans('today') }}</span><strong>{{ number_format((int) data_get($kpis, 'messages_today', 0)) }}</strong></div>
                    <div><span>{{ db_trans('this_week') }}</span><strong>{{ number_format((int) data_get($kpis, 'messages_this_week', 0)) }}</strong></div>
                    <div><span>{{ db_trans('this_month') }}</span><strong>{{ number_format((int) data_get($kpis, 'messages_this_month', 0)) }}</strong></div>
                    <div><span>{{ db_trans('this_year') }}</span><strong>{{ number_format((int) data_get($kpis, 'messages_this_year', 0)) }}</strong></div>
                    <div><span>{{ db_trans('total_sms_segments') }}</span><strong>{{ number_format((int) data_get($kpis, 'total_messages', 0)) }}</strong></div>
                    <div><span>{{ db_trans('sms_balance') }}</span><strong>{{ number_format((float) data_get($kpis, 'balance_units', 0), 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading">
            <div><h5 class="mb-1">{{ db_trans('sms_campaigns') }}</h5></div>
            <span class="ui-section-badge">{{ number_format(method_exists($campaigns, 'total') ? $campaigns->total() : $campaigns->count()) }} {{ db_trans('records') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle" id="smsCampaignsTable">
                <thead>
                    <tr>
                        <th>{{ db_trans('sn') }}</th>
                        <th>{{ db_trans('title') }}</th>
                        <th>{{ db_trans('type') }}</th>
                        <th>{{ db_trans('recipients') }}</th>
                        <th>{{ db_trans('segments') }}</th>
                        <th>{{ db_trans('cost') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th>{{ db_trans('created_at') }}</th>
                        <th>{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $campaign->title }}</strong><div class="small text-muted">{{ strtoupper($campaign->channel ?? 'sms') }}</div></td>
                            <td>{{ db_trans('campaign_type_' . ($campaign->type ?? 'manual_bulk')) }}</td>
                            <td>{{ number_format((int) $campaign->total_recipients) }}</td>
                            <td>{{ number_format((int) $campaign->total_segments) }}</td>
                            <td>{{ number_format((float) $campaign->estimated_cost_units, 2) }}</td>
                            <td><span class="ui-status-pill {{ $statusClass($campaign->status) }}">{{ $statusLabel($campaign->status) }}</span></td>
                            <td data-order="{{ optional($campaign->created_at)->format('Y-m-d H:i:s') }}">{{ optional($campaign->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.communication.logs.index', ['campaign_id' => $campaign->id]) }}" class="btn btn-sm ui-btn-light">{{ db_trans('logs') }}</a>
                                    @if($campaign->status === 'scheduled')
                                        <a href="{{ route('admin.communication.schedules.index') }}" class="btn btn-sm ui-btn-light">{{ db_trans('schedule') }}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($campaigns, 'links'))
            <div class="sms-pagination mt-3">{{ $campaigns->links() }}</div>
        @endif
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
    if (window.jQuery && $.fn.DataTable && $('#smsCampaignsTable').length) {
        $('#smsCampaignsTable').DataTable({
            paging: false,
            info: false,
            searching: true,
            responsive: true,
            order: [],
            language: {
                search: @json(db_trans('search')) + ':',
                zeroRecords: @json(db_trans('no_records_found')),
                paginate: { previous: '‹', next: '›' }
            }
        });
    }

    const ctx = document.getElementById('smsUsageTrendChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json(data_get($usage_chart ?? [], 'labels', [])),
                datasets: [{
                    label: @json(db_trans('sms_segments')),
                    data: @json(data_get($usage_chart ?? [], 'values', [])),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, .14)',
                    pointBackgroundColor: '#7c3aed',
                    fill: true,
                    tension: .35,
                    borderWidth: 3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
        });
    }
});
</script>
@endpush
