@extends('layouts.admin')

@section('title', db_trans('communication_center'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/communication-sms-v2.css') }}">
@endpush

@section('content')
@php
    $kpis = $kpis ?? [];
    $recentCampaigns = collect($recent_campaigns ?? []);
    $smsSettings = $sms_settings ?? [];
    $currency = $smsSettings['currency'] ?? 'TZS';
@endphp
<div class="admin-ui-v4 communication-sms-v2">
    <div class="ui-page-hero sms-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-comments"></i>{{ db_trans('communication_center') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('communication_center') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-wallet"></i>{{ number_format((float) data_get($kpis, 'balance_units', 0), 2) }} {{ $currency }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-comment-sms"></i>{{ number_format((int) data_get($kpis, 'messages_this_month', 0)) }} {{ db_trans('sms_this_month') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-coins"></i>{{ number_format((float) data_get($kpis, 'estimated_cost_month', 0), 2) }} {{ $currency }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('admin.communication.sms.create') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-paper-plane"></i></span><span class="ui-hero-action-text">{{ db_trans('send_new_sms') }}</span></a>
                    <a href="{{ route('admin.communication.sms.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-list"></i></span><span class="ui-hero-action-text">{{ db_trans('sms_campaigns') }}</span></a>
                    <a href="{{ route('admin.communication.templates.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-file-alt"></i></span><span class="ui-hero-action-text">{{ db_trans('sms_templates') }}</span></a>
                    <a href="{{ route('admin.communication.balance.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-wallet"></i></span><span class="ui-hero-action-text">{{ db_trans('sms_balance') }}</span></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-bullhorn"></i></div><div class="ui-stat-label">{{ db_trans('total_campaigns') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'total_campaigns', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-clock"></i></div><div class="ui-stat-label">{{ db_trans('scheduled') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'scheduled_count', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-info"><i class="fas fa-spinner"></i></div><div class="ui-stat-label">{{ db_trans('processing') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'processing_count', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-check-circle"></i></div><div class="ui-stat-label">{{ db_trans('completed') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'completed_campaigns', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-comment-sms"></i></div><div class="ui-stat-label">{{ db_trans('sms_this_month') }}</div><div class="ui-stat-value">{{ number_format((int) data_get($kpis, 'messages_this_month', 0)) }}</div></div></div>
        <div class="col-xl-2 col-md-4"><div class="ui-mini-card p-4 h-100"><div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-wallet"></i></div><div class="ui-stat-label">{{ db_trans('sms_balance') }}</div><div class="ui-stat-value">{{ number_format((float) data_get($kpis, 'balance_units', 0), 0) }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><div><h5 class="mb-1">{{ db_trans('sms_usage_trend') }}</h5></div><span class="ui-section-badge">{{ db_trans('last_14_days') }}</span></div>
                <div class="sms-chart-shell"><canvas id="communicationSmsTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><div><h5 class="mb-1">{{ db_trans('recent_sms_campaigns') }}</h5></div><span class="ui-section-badge">5</span></div>
                <div class="sms-stat-list">
                    @forelse($recentCampaigns as $campaign)
                        <div><span>{{ $campaign->title }}</span><strong>{{ number_format((int) $campaign->total_segments) }}</strong></div>
                    @empty
                        <div><span>{{ db_trans('no_records_found') }}</span><strong>0</strong></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('communicationSmsTrendChart');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx, {
        type: 'line',
        data: { labels: @json(data_get($usage_chart ?? [], 'labels', [])), datasets: [{ label: @json(db_trans('sms_segments')), data: @json(data_get($usage_chart ?? [], 'values', [])), borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.14)', fill:true, tension:.35, borderWidth:3 }] },
        options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }, plugins:{ legend:{ display:false } } }
    });
});
</script>
@endpush
