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
<div class="admin-ui-v4 finance-reports-v4">
    <div class="ui-page-hero">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-shield-halved"></i>{{ db_trans('contribution_compliance_report') }}</span>
                <h1 class="ui-page-title mt-3">{{ db_trans('contribution_compliance_report') }}</h1>
                <p class="ui-page-subtitle">{{ db_trans('track_who_paid_and_who_did_not_pay_by_period_and_type') }}</p>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-users"></i>{{ db_trans('members_checked') }}: {{ $stats['members_total'] }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ db_trans('paid_members') }}: {{ $stats['paid_members'] }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-triangle-exclamation"></i>{{ db_trans('unpaid_members') }}: {{ $stats['unpaid_members'] }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-file-invoice-dollar"></i></span><span class="ui-hero-action-text">{{ db_trans('financial_summary') }}<small>{{ db_trans('open') }}</small></span></a>
                    <a href="{{ route('finance.contributions.cash.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-wallet"></i></span><span class="ui-hero-action-text">{{ db_trans('cash_contributions') }}<small>{{ db_trans('open') }}</small></span></a>
                    <a href="{{ route('finance.contributions.bank.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-building-columns"></i></span><span class="ui-hero-action-text">{{ db_trans('bank_contributions') }}<small>{{ db_trans('open') }}</small></span></a>
                </div>
            </div>
        </div>
    </div>

    @include('admin.finance.reports.partials.filter-bar', ['showMonth' => true, 'showComplianceFilters' => true, 'filters' => $filters, 'types' => $types, 'kandas' => $kandas, 'jumuiyas' => $jumuiyas])

    <div class="row g-4 mb-4 ui-kpi-grid">
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-primary border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-users-viewfinder"></i></div><span class="ui-chip">{{ db_trans('overview') }}</span></div><div class="ui-stat-label">{{ db_trans('members_checked') }}</div><div class="ui-stat-value">{{ $stats['members_total'] }}</div><div class="ui-stat-meta">{{ db_trans('filter_records') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-success border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-circle-check"></i></div><span class="ui-chip">{{ db_trans('paid') }}</span></div><div class="ui-stat-label">{{ db_trans('paid_members') }}</div><div class="ui-stat-value">{{ $stats['paid_members'] }}</div><div class="ui-stat-meta">{{ db_trans('paid') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-danger border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-user-xmark"></i></div><span class="ui-chip">{{ db_trans('unpaid') }}</span></div><div class="ui-stat-label">{{ db_trans('unpaid_members') }}</div><div class="ui-stat-value">{{ $stats['unpaid_members'] }}</div><div class="ui-stat-meta">{{ db_trans('status') }}</div></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="card ui-stat-card ui-tone-info border-0 h-100"><div class="card-body"><div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-sack-dollar"></i></div><span class="ui-chip">{{ db_trans('finance') }}</span></div><div class="ui-stat-label">{{ db_trans('total_collected') }}</div><div class="ui-stat-value">{{ number_format($stats['paid_total'], 2) }}</div><div class="ui-stat-meta">{{ db_trans('approved_total') }}</div></div></div></div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="ui-panel-title mb-1">{{ db_trans('contribution_compliance_report') }}</h5>
                <p class="ui-panel-subtitle mb-0">{{ db_trans('track_who_paid_and_who_did_not_pay_by_period_and_type') }}</p>
            </div>
            <span class="ui-soft-badge">{{ method_exists($rows, 'total') ? $rows->total() : count($rows) }}</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0" id="financeComplianceTable">
                <thead>
                    <tr>
                        <th>#</th><th>{{ db_trans('member') }}</th><th>{{ db_trans('familia') }}</th><th>{{ db_trans('jumuiya') }}</th><th>{{ db_trans('kanda') }}</th><th class="text-end">{{ db_trans('expected_amount') }}</th><th class="text-end">{{ db_trans('paid_amount') }}</th><th>{{ db_trans('last_paid_date') }}</th><th>{{ db_trans('source') }}</th><th>{{ db_trans('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ method_exists($rows, 'firstItem') ? $rows->firstItem() + $loop->index : $loop->iteration }}</td>
                            <td><div class="fw-semibold">{{ $row['member']->full_name }}</div><small class="text-muted">{{ $row['member']->member_code ?? '—' }}</small></td>
                            <td>{{ $row['member']->familia?->name ?? '—' }}</td>
                            <td>{{ $row['member']->familia?->jumuiya?->name ?? '—' }}</td>
                            <td>{{ $row['member']->familia?->jumuiya?->kanda?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($row['expected_amount'], 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['paid_amount'], 2) }}</td>
                            <td>{{ $row['last_paid_at']?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $sourceLabel($row['source']) }}</td>
                            <td>
                                @if($row['status'] === 'paid')<span class="ui-status-pill ui-status-paid">{{ db_trans('paid') }}</span>
                                @elseif($row['status'] === 'partial')<span class="ui-status-pill ui-status-partial">{{ db_trans('partial') }}</span>
                                @else<span class="ui-status-pill ui-status-unpaid">{{ db_trans('unpaid') }}</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="ui-empty-state"><i class="fas fa-inbox"></i><div>{{ db_trans('no_records_found') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($rows, 'hasPages') && $rows->hasPages())<div class="card-footer bg-transparent border-0">{{ $rows->links() }}</div>@endif
    </div>
</div>
@endsection
