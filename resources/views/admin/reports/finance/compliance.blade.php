@extends('layouts.admin')

@section('title', db_trans('contribution_compliance_report'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/reports-v4-polish.css') }}">
@endpush

@section('content')
@php
    $sourceLabel = fn($source) => $source === 'cash' ? db_trans('cash') : ($source === 'bank' ? db_trans('bank') : ($source === 'mixed' ? db_trans('mixed') : ucfirst((string) $source)));
@endphp
<div class="admin-ui-v4 reports-v4-page">
    <div class="ui-page-hero">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-shield-halved"></i>{{ db_trans('contribution_compliance_report') }}</span>
                <h1 class="ui-page-title mt-3">{{ db_trans('contribution_compliance_report') }}</h1>
                <p class="ui-page-subtitle">{{ db_trans('track_who_paid_and_who_did_not_pay_by_period_and_type') }}</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 ui-kpi-grid">
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-primary border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-users"></i></div><span class="ui-chip">{{ db_trans('overview') }}</span></div><div class="ui-stat-label">{{ db_trans('members_checked') }}</div><div class="ui-stat-value">{{ $stats['members_total'] }}</div><div class="ui-stat-meta">{{ db_trans('filter_records') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-success border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-circle-check"></i></div><span class="ui-chip">{{ db_trans('paid') }}</span></div><div class="ui-stat-label">{{ db_trans('paid_members') }}</div><div class="ui-stat-value">{{ $stats['paid_members'] }}</div><div class="ui-stat-meta">{{ db_trans('paid') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-danger border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-user-xmark"></i></div><span class="ui-chip">{{ db_trans('unpaid_members') }}</span></div><div class="ui-stat-label">{{ db_trans('unpaid_members') }}</div><div class="ui-stat-value">{{ $stats['unpaid_members'] }}</div><div class="ui-stat-meta">{{ db_trans('status') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-info border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-sack-dollar"></i></div><span class="ui-chip">{{ db_trans('finance') }}</span></div><div class="ui-stat-label">{{ db_trans('total_collected') }}</div><div class="ui-stat-value">{{ number_format($stats['paid_total'], 2) }}</div><div class="ui-stat-meta">{{ db_trans('approved_total') }}</div></div></div></div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="ui-panel-title mb-1">{{ db_trans('contribution_compliance_report') }}</h5><p class="ui-panel-subtitle mb-0">{{ db_trans('track_who_paid_and_who_did_not_pay_by_period_and_type') }}</p></div><span class="ui-soft-badge">{{ count($rows) }}</span></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>{{ db_trans('member') }}</th><th>{{ db_trans('familia') }}</th><th>{{ db_trans('expected_amount') }}</th><th>{{ db_trans('paid_amount') }}</th><th>{{ db_trans('source') }}</th><th>{{ db_trans('status') }}</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['member']->full_name }}</td>
                        <td>{{ $row['member']->familia?->name ?? '—' }}</td>
                        <td class="text-end">{{ number_format($row['expected_amount'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['paid_amount'], 2) }}</td>
                        <td>{{ $sourceLabel($row['source']) }}</td>
                        <td>@if($row['status'] === 'paid')<span class="ui-status-pill ui-status-paid">{{ db_trans('paid') }}</span>@elseif($row['status'] === 'partial')<span class="ui-status-pill ui-status-partial">{{ db_trans('partial') }}</span>@else<span class="ui-status-pill ui-status-unpaid">{{ db_trans('unpaid') }}</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="ui-empty-state"><i class="fas fa-inbox"></i><div>{{ db_trans('no_records_found') }}</div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
