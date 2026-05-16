@extends('layouts.admin')

@section('title', db_trans('reports_dashboard'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/reports-v4-polish.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 reports-v4-page">
    <div class="ui-page-hero">
        <div class="ui-hero-pattern"></div>

        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-chart-pie"></i>
                    {{ db_trans('reports_dashboard') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-0">
                    {{ db_trans('reports_dashboard') }}
                </h1>

                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill">
                        <i class="fas fa-file-invoice-dollar"></i>
                        {{ db_trans('finance_reports') }}
                    </span>

                    <span class="ui-meta-pill">
                        <i class="fas fa-book-bible"></i>
                        {{ db_trans('sacrament_reports') }}
                    </span>

                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-scale-balanced"></i>
                        {{ db_trans('budget_reports') }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('reports.finance.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('finance_reports') }}
                        </span>
                    </a>

                    <a href="{{ route('reports.sacraments.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-book-bible"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('sacrament_reports') }}
                        </span>
                    </a>

                    <a href="{{ route('reports.budgets.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-scale-balanced"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('budget_reports') }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card ui-panel ui-report-tile border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div>
                            <div class="ui-data-label">
                                {{ db_trans('finance_reports') }}
                            </div>

                            <h4 class="ui-panel-title mb-0">
                                {{ db_trans('income_expense_balance') }}
                            </h4>
                        </div>

                        <div class="ui-panel-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>

                    <a href="{{ route('reports.finance.index') }}" class="btn ui-btn-primary btn-sm mt-3">
                        {{ db_trans('open_report') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card ui-panel ui-report-tile border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div>
                            <div class="ui-data-label">
                                {{ db_trans('sacrament_reports') }}
                            </div>

                            <h4 class="ui-panel-title mb-0">
                                {{ db_trans('faithful_census_reports') }}
                            </h4>
                        </div>

                        <div class="ui-panel-icon">
                            <i class="fas fa-book-bible"></i>
                        </div>
                    </div>

                    <a href="{{ route('reports.sacraments.index') }}" class="btn ui-btn-primary btn-sm mt-3">
                        {{ db_trans('open_report') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card ui-panel ui-report-tile border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div>
                            <div class="ui-data-label">
                                {{ db_trans('budget_reports') }}
                            </div>

                            <h4 class="ui-panel-title mb-0">
                                {{ db_trans('budget_vs_actual') }}
                            </h4>
                        </div>

                        <div class="ui-panel-icon">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                    </div>

                    <a href="{{ route('reports.budgets.index') }}" class="btn ui-btn-primary btn-sm mt-3">
                        {{ db_trans('open_report') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection