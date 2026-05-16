@extends('layouts.admin')

@section('title', db_trans('system_information'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-systeminfo">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="syscfg-hero-title">{{ db_trans('system_information') }}</h2>
                    <p class="syscfg-hero-subtitle">{{ db_trans('runtime_versions_environment_and_driver_diagnostics') }}</p>
                </div>

                <a href="{{ route('system-config.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('system_configuration_center') }}
                </a>
            </div>
        </div>

        <div class="card syscfg-form-card">
            <div class="card-body">
                <div class="syscfg-section-title">{{ db_trans('application_runtime') }}</div>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('php_version') }}</div>
                            <div class="syscfg-info-value">{{ $info['php_version'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('laravel_version') }}</div>
                            <div class="syscfg-info-value">{{ $info['laravel_version'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('server_time') }}</div>
                            <div class="syscfg-info-value">{{ $info['server_time'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card syscfg-form-card">
            <div class="card-body">
                <div class="syscfg-section-title">{{ db_trans('application_environment') }}</div>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('app_environment') }}</div>
                            <div class="syscfg-info-value">{{ strtoupper($info['app_env'] ?? '-') }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('debug_mode') }}</div>
                            <div class="syscfg-info-value">{{ !empty($info['app_debug']) ? db_trans('enabled') : db_trans('disabled') }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('app_url') }}</div>
                            <div class="syscfg-info-value">{{ $info['app_url'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('app_locale') }}</div>
                            <div class="syscfg-info-value">{{ $info['app_locale'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('app_fallback_locale') }}</div>
                            <div class="syscfg-info-value">{{ $info['app_fallback_locale'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card syscfg-form-card">
            <div class="card-body">
                <div class="syscfg-section-title">{{ db_trans('drivers_and_connections') }}</div>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('database_connection') }}</div>
                            <div class="syscfg-info-value">{{ $info['db_connection'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('database_host') }}</div>
                            <div class="syscfg-info-value">{{ $info['db_host'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('database_port') }}</div>
                            <div class="syscfg-info-value">{{ $info['db_port'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('database_name') }}</div>
                            <div class="syscfg-info-value">{{ $info['db_database'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('session_driver') }}</div>
                            <div class="syscfg-info-value">{{ $info['session_driver'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('queue_driver') }}</div>
                            <div class="syscfg-info-value">{{ $info['queue_driver'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('cache_store') }}</div>
                            <div class="syscfg-info-value">{{ $info['cache_store'] ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="syscfg-info-item">
                            <div class="syscfg-info-label">{{ db_trans('filesystem_disk') }}</div>
                            <div class="syscfg-info-value">{{ $info['filesystem_disk'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/system-config.js') }}"></script>
@endpush