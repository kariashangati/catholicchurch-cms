@extends('layouts.admin')

@section('title', db_trans('environment_settings'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-environment">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="syscfg-hero-title">{{ db_trans('environment_settings') }}</h2>
                    <p class="syscfg-hero-subtitle">{{ db_trans('review_and_update_allowed_environment_configuration_values') }}</p>
                </div>

                <a href="{{ route('system-config.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('system_configuration_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-config.environment.update') }}">
            @csrf
            @method('PUT')

            @foreach($groups as $groupName => $items)
                <div class="card syscfg-form-card">
                    <div class="card-body">
                        <div class="syscfg-section-title">{{ db_trans($groupName) }}</div>

                        <div class="syscfg-env-grid">
                            @foreach($items as $key => $config)
                                <div class="syscfg-env-card">
                                    <div class="syscfg-env-key">{{ $key }}</div>

                                    <input
                                        type="{{ $config['is_masked'] ? 'password' : 'text' }}"
                                        name="{{ $key }}"
                                        value="{{ old($key, $config['value']) }}"
                                        class="form-control"
                                        id="env_{{ $key }}"
                                        @disabled(!$config['is_editable'])
                                    >

                                    <div class="syscfg-env-meta">
                                        @if($config['is_masked'])
                                            {{ db_trans('sensitive_value_masked_in_summary_views') }}
                                        @elseif(!$config['is_editable'])
                                            {{ db_trans('read_only_configuration_value') }}
                                        @else
                                            {{ db_trans('editable_environment_value') }}
                                        @endif
                                    </div>

                                    @if($config['is_masked'])
                                        <div class="d-flex gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle-secret="#env_{{ $key }}">
                                                {{ db_trans('show_hide') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-dark" data-copy-value="{{ $config['value'] }}">
                                                {{ db_trans('copy') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="syscfg-sticky-actions d-flex justify-content-end gap-2">
                <a href="{{ route('system-config.dashboard') }}" class="btn btn-outline-secondary px-4">
                    {{ db_trans('cancel') }}
                </a>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/system-config.js') }}"></script>
@endpush