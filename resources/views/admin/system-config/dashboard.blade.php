@extends('layouts.admin')

@section('title', db_trans('system_configuration_center'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-dashboard">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <span class="syscfg-hero-badge">
                        <i class="fas fa-cogs"></i>
                        {{ db_trans('system') }}
                    </span>

                    <h2 class="syscfg-hero-title">{{ $page['title'] }}</h2>
                    <p class="syscfg-hero-subtitle">{{ $page['subtitle'] }}</p>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="syscfg-hero-pill">
                            <i class="fas fa-clock"></i>
                            {{ db_trans('last_updated') }}:
                            {{ optional($page['updated_at'])->format('d M Y, h:i A') }}
                        </span>

                        <span class="syscfg-hero-pill">
                            <i class="fas fa-user-shield"></i>
                            {{ db_trans('logged_in_as') }}: {{ auth()->user()->name }}
                        </span>
                    </div>
                </div>

                <div class="syscfg-brand-preview-card">
                    <div class="syscfg-brand-preview-top">
                        <div>
                            <div class="syscfg-brand-name">{{ $branding['name'] ?: config('app.name') }}</div>
                            <div class="syscfg-brand-tagline">{{ $branding['tagline'] ?: db_trans('not_available') }}</div>
                        </div>

                        @if(!empty($branding['favicon']))
                            <img src="{{ asset($branding['favicon']) }}" alt="favicon" class="syscfg-favicon-preview">
                        @endif
                    </div>

                    @if(!empty($branding['logo']))
                        <div class="mt-3">
                            <img src="{{ asset($branding['logo']) }}" alt="logo" class="syscfg-logo-preview">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($kpis as $card)
                <div class="col-xxl-2 col-xl-4 col-md-6">
                    <div class="card syscfg-kpi-card">
                        <div class="card-body">
                            <div class="syscfg-kpi-top">
                                <span class="syscfg-kpi-icon tone-{{ $card['tone'] }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>

                                <span class="syscfg-kpi-chip">{{ db_trans('overview') }}</span>
                            </div>

                            <div class="syscfg-kpi-title">{{ $card['title'] }}</div>
                            <div class="syscfg-kpi-value">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="card syscfg-panel">
                    <div class="card-body">
                        <div class="syscfg-panel-head">
                            <div>
                                <h4 class="syscfg-panel-title">{{ db_trans('branding_preview') }}</h4>
                                <p class="syscfg-panel-subtitle">{{ db_trans('current_public_site_identity') }}</p>
                            </div>
                            <span class="syscfg-panel-badge">{{ db_trans('branding') }}</span>
                        </div>

                        <div class="syscfg-info-list">
                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('site_name') }}</div>
                                <div class="syscfg-info-value">{{ $branding['name'] ?: '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('site_tagline') }}</div>
                                <div class="syscfg-info-value">{{ $branding['tagline'] ?: '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('site_description') }}</div>
                                <div class="syscfg-info-value">{{ $branding['description'] ?: '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('logo') }}</div>
                                <div class="syscfg-info-value">{{ !empty($branding['logo']) ? db_trans('configured') : db_trans('not_configured') }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('favicon') }}</div>
                                <div class="syscfg-info-value">{{ !empty($branding['favicon']) ? db_trans('configured') : db_trans('not_configured') }}</div>
                            </div>
                        </div>

                        @can('system.config.branding.view')
                            <div class="mt-3">
                                <a href="{{ route('system-config.branding.edit') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-palette me-2"></i>{{ db_trans('manage_branding') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card syscfg-panel">
                    <div class="card-body">
                        <div class="syscfg-panel-head">
                            <div>
                                <h4 class="syscfg-panel-title">{{ db_trans('system_status') }}</h4>
                                <p class="syscfg-panel-subtitle">{{ db_trans('runtime_and_platform_diagnostics') }}</p>
                            </div>
                            <span class="syscfg-panel-badge">{{ db_trans('system_information') }}</span>
                        </div>

                        <div class="syscfg-info-list">
                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('php_version') }}</div>
                                <div class="syscfg-info-value">{{ $system['php_version'] ?? '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('laravel_version') }}</div>
                                <div class="syscfg-info-value">{{ $system['laravel_version'] ?? '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('database_connection') }}</div>
                                <div class="syscfg-info-value">{{ $system['db_connection'] ?? '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('database_status') }}</div>
                                <div class="syscfg-info-value">
                                    <span class="syscfg-status-dot {{ ($system['database_status'] ?? '') === 'connected' ? 'is-success' : 'is-danger' }}"></span>
                                    {{ ucfirst($system['database_status'] ?? 'unknown') }}
                                </div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('queue_driver') }}</div>
                                <div class="syscfg-info-value">{{ $system['queue_driver'] ?? '-' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('cache_store') }}</div>
                                <div class="syscfg-info-value">{{ $system['cache_store'] ?? '-' }}</div>
                            </div>
                        </div>

                        @can('system.config.systeminfo.view')
                            <div class="mt-3">
                                <a href="{{ route('system-config.system-information.index') }}" class="btn btn-outline-dark w-100">
                                    <i class="fas fa-server me-2"></i>{{ db_trans('view_system_information') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card syscfg-panel">
                    <div class="card-body">
                        <div class="syscfg-panel-head">
                            <div>
                                <h4 class="syscfg-panel-title">{{ db_trans('communication_configuration') }}</h4>
                                <p class="syscfg-panel-subtitle">{{ db_trans('mail_and_sms_connectivity_status') }}</p>
                            </div>
                            <span class="syscfg-panel-badge">{{ db_trans('communication') }}</span>
                        </div>

                        <div class="syscfg-info-list">
                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('sms_provider') }}</div>
                                <div class="syscfg-info-value">{{ $sms_balance['provider'] ?? 'beem' }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('sms_balance') }}</div>
                                <div class="syscfg-info-value">
                                    @if(($sms_balance['success'] ?? false) === true)
                                        {{ $sms_balance['balance'] ?? '-' }}
                                    @else
                                        {{ db_trans('unavailable') }}
                                    @endif
                                </div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('app_environment') }}</div>
                                <div class="syscfg-info-value">{{ strtoupper($system['app_env'] ?? '-') }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('debug_mode') }}</div>
                                <div class="syscfg-info-value">{{ !empty($system['app_debug']) ? db_trans('enabled') : db_trans('disabled') }}</div>
                            </div>

                            <div class="syscfg-info-item">
                                <div class="syscfg-info-label">{{ db_trans('maintenance_mode') }}</div>
                                <div class="syscfg-info-value">{{ !empty($maintenance['enabled']) ? db_trans('enabled') : db_trans('disabled') }}</div>
                            </div>
                        </div>

                        @can('system.config.communication.view')
                            <div class="mt-3">
                                <a href="{{ route('system-config.communication.edit') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-sms me-2"></i>{{ db_trans('manage_communication_config') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3">
            @can('system.config.general.view')
                <a href="{{ route('system-config.general.edit') }}" class="btn btn-primary px-4">
                    <i class="fas fa-sliders-h me-2"></i>{{ db_trans('general_settings') }}
                </a>
            @endcan

            @can('system.config.maintenance.view')
                <a href="{{ route('system-config.maintenance.edit') }}" class="btn btn-outline-warning px-4">
                    <i class="fas fa-tools me-2"></i>{{ db_trans('maintenance_access_control') }}
                </a>
            @endcan

            @can('system.config.environment.view')
                <a href="{{ route('system-config.environment.edit') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-code me-2"></i>{{ db_trans('environment_settings') }}
                </a>
            @endcan
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/system-config.js') }}"></script>
@endpush