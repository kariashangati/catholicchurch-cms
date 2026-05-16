@extends('layouts.admin')

@section('title', db_trans('finance_reports'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-chart-line"></i>{{ db_trans('finance_reports') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('finance_reports') }}</h1>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('finance.reports.financial-summary') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('financial_summary') }}</span>
                    </a>
                    <a href="{{ route('finance.reports.compliance') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-shield-halved"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('contribution_compliance_report') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><h5 class="mb-1">{{ db_trans('financial_summary') }}</h5></div>
                <a href="{{ route('finance.reports.financial-summary') }}" class="btn ui-btn-primary btn-sm mt-3">{{ db_trans('open_report') }}</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading"><h5 class="mb-1">{{ db_trans('contribution_compliance_report') }}</h5></div>
                <a href="{{ route('finance.reports.compliance') }}" class="btn ui-btn-primary btn-sm mt-3">{{ db_trans('open_report') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
