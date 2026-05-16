@extends('layouts.admin')

@section('title', db_trans('maintenance_access_control'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    @php
        $global = $global ?? [];
        $routes = $routes ?? [];
        $suggestedRoutes = $suggestedRoutes ?? [];
    @endphp

    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-maintenance">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <span class="syscfg-hero-badge">
                        <i class="fas fa-tools"></i>
                        {{ db_trans('maintenance') }}
                    </span>

                    <h2 class="syscfg-hero-title">{{ db_trans('maintenance_access_control') }}</h2>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="syscfg-hero-pill">
                            <i class="fas fa-power-off"></i>
                            {{ db_trans('app_maintenance') }}:
                            {{ !empty($global['enabled']) ? db_trans('enabled') : db_trans('disabled') }}
                        </span>

                        <span class="syscfg-hero-pill">
                            <i class="fas fa-server"></i>
                            {{ db_trans('laravel_maintenance') }}:
                            {{ !empty($global['laravel_down']) ? db_trans('enabled') : db_trans('disabled') }}
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                    <a href="{{ route('system-config.dashboard') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>{{ db_trans('system_configuration_center') }}
                    </a>

                    @can('system.config.systeminfo.view')
                        <a href="{{ route('system-config.system-information.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-server me-2"></i>{{ db_trans('system_information') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        @if(!empty($global['laravel_down']))
            <div class="alert alert-warning d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 rounded-4 border-0 shadow-sm">
                <div>
                    <strong>{{ db_trans('laravel_maintenance_is_active') }}</strong>
                    @if(!empty($global['laravel_bypass_url']))
                        <div class="small mt-1">
                            {{ db_trans('bypass_link') }}:
                            <a href="{{ $global['laravel_bypass_url'] }}" target="_blank">{{ $global['laravel_bypass_url'] }}</a>
                        </div>
                    @endif
                </div>

                @can('system.config.maintenance.update')
                    <form method="POST" action="{{ route('system-config.maintenance.laravel-up') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-plug-circle-xmark me-2"></i>{{ db_trans('turn_off_maintenance_now') }}
                        </button>
                    </form>
                @endcan
            </div>
        @endif

        <form method="POST" action="{{ route('system-config.maintenance.update') }}">
            @csrf
            @method('PUT')

            <div class="card syscfg-form-card">
                <div class="card-body">
                    <div class="syscfg-section-title">{{ db_trans('global_maintenance_mode') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('maintenance_status') }}</label>
                            <select name="global_enabled" class="form-select @error('global_enabled') is-invalid @enderror">
                                <option value="0" @selected((string) old('global_enabled', !empty($global['enabled']) ? '1' : '0') === '0')>{{ db_trans('disabled') }}</option>
                                <option value="1" @selected((string) old('global_enabled', !empty($global['enabled']) ? '1' : '0') === '1')>{{ db_trans('enabled') }}</option>
                            </select>
                            @error('global_enabled')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('also_enable_laravel_down') }}</label>
                            <select name="use_laravel_down" class="form-select">
                                <option value="0" @selected(!old('use_laravel_down'))>{{ db_trans('no') }}</option>
                                <option value="1" @selected(old('use_laravel_down'))>{{ db_trans('yes') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('safe_recovery') }}</label>
                            <div class="form-control bg-light">
                                {{ db_trans('system_config_remains_open') }}
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <label class="form-label">{{ db_trans('maintenance_title') }}</label>
                            <input
                                type="text"
                                name="global_title"
                                value="{{ old('global_title', $global['title'] ?? db_trans('maintenance_mode')) }}"
                                class="form-control @error('global_title') is-invalid @enderror"
                            >
                            @error('global_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-12">
                            <label class="form-label">{{ db_trans('maintenance_message') }}</label>
                            <textarea
                                name="global_message"
                                rows="4"
                                class="form-control @error('global_message') is-invalid @enderror"
                            >{{ old('global_message', $global['message'] ?? db_trans('system_is_currently_under_maintenance')) }}</textarea>
                            @error('global_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card syscfg-form-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                        <div class="syscfg-section-title mb-0">{{ db_trans('page_level_maintenance') }}</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addMaintenanceRouteRow">
                            <i class="fas fa-plus me-1"></i>{{ db_trans('add_page') }}
                        </button>
                    </div>

                    <div class="syscfg-repeater-list" id="maintenanceRoutesList">
                        @foreach($suggestedRoutes as $index => $routeName)
                            @php
                                $routeData = $routes[$routeName] ?? [];
                            @endphp

                            <div class="syscfg-repeater-item maintenance-route-row">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label class="form-label">{{ db_trans('route_name') }}</label>
                                        <input type="text" name="routes[{{ $index }}][name]" value="{{ old("routes.$index.name", $routeName) }}" class="form-control">
                                    </div>

                                    <div class="col-lg-2">
                                        <label class="form-label">{{ db_trans('status') }}</label>
                                        <select name="routes[{{ $index }}][enabled]" class="form-select">
                                            <option value="0" @selected((string) old("routes.$index.enabled", !empty($routeData['enabled']) ? '1' : '0') === '0')>{{ db_trans('disabled') }}</option>
                                            <option value="1" @selected((string) old("routes.$index.enabled", !empty($routeData['enabled']) ? '1' : '0') === '1')>{{ db_trans('enabled') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3">
                                        <label class="form-label">{{ db_trans('maintenance_title') }}</label>
                                        <input type="text" name="routes[{{ $index }}][title]" value="{{ old("routes.$index.title", $routeData['title'] ?? db_trans('maintenance_mode')) }}" class="form-control">
                                    </div>

                                    <div class="col-lg-4">
                                        <label class="form-label">{{ db_trans('maintenance_message') }}</label>
                                        <input type="text" name="routes[{{ $index }}][message]" value="{{ old("routes.$index.message", $routeData['message'] ?? db_trans('this_page_is_under_maintenance')) }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="syscfg-sticky-actions d-flex justify-content-end gap-2">
                <a href="{{ route('system-config.dashboard') }}" class="btn btn-outline-secondary px-4">
                    {{ db_trans('cancel') }}
                </a>

                <button type="submit" class="btn btn-warning px-4">
                    <i class="fas fa-tools me-2"></i>{{ db_trans('save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/system-config.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('maintenanceRoutesList');
            const addButton = document.getElementById('addMaintenanceRouteRow');

            if (!list || !addButton) {
                return;
            }

            const labels = {
                routeName: @json(db_trans('route_name')),
                status: @json(db_trans('status')),
                disabled: @json(db_trans('disabled')),
                enabled: @json(db_trans('enabled')),
                maintenanceTitle: @json(db_trans('maintenance_title')),
                maintenanceMessage: @json(db_trans('maintenance_message')),
                maintenanceMode: @json(db_trans('maintenance_mode')),
                pageMessage: @json(db_trans('this_page_is_under_maintenance')),
            };

            addButton.addEventListener('click', function () {
                const index = list.querySelectorAll('.maintenance-route-row').length;
                const wrapper = document.createElement('div');
                wrapper.className = 'syscfg-repeater-item maintenance-route-row';
                wrapper.innerHTML = `
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label">${labels.routeName}</label>
                            <input type="text" name="routes[${index}][name]" class="form-control" placeholder="finance.contributions.cash.index">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">${labels.status}</label>
                            <select name="routes[${index}][enabled]" class="form-select">
                                <option value="0">${labels.disabled}</option>
                                <option value="1">${labels.enabled}</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">${labels.maintenanceTitle}</label>
                            <input type="text" name="routes[${index}][title]" class="form-control" value="${labels.maintenanceMode}">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">${labels.maintenanceMessage}</label>
                            <input type="text" name="routes[${index}][message]" class="form-control" value="${labels.pageMessage}">
                        </div>
                    </div>
                `;
                list.appendChild(wrapper);
            });
        });
    </script>
@endpush
