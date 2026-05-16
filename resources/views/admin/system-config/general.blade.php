@extends('layouts.admin')

@section('title', db_trans('general_settings'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-general">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="syscfg-hero-title">{{ db_trans('general_settings') }}</h2>
                    <p class="syscfg-hero-subtitle">{{ db_trans('manage_site_name_tagline_description_and_locale_settings') }}</p>
                </div>

                <a href="{{ route('system-config.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('system_configuration_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-config.general.update') }}">
            @csrf
            @method('PUT')

            <div class="card syscfg-form-card">
                <div class="card-body">
                    <div class="syscfg-section-title">{{ db_trans('site_identity') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('site_name') }}</label>
                            <input
                                type="text"
                                name="site_name"
                                value="{{ old('site_name', $settings['site_name'] ?? '') }}"
                                class="form-control @error('site_name') is-invalid @enderror"
                                required
                            >
                            @error('site_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('site_tagline') }}</label>
                            <input
                                type="text"
                                name="site_tagline"
                                value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                                class="form-control @error('site_tagline') is-invalid @enderror"
                            >
                            @error('site_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('site_description') }}</label>
                            <textarea
                                name="site_description"
                                rows="5"
                                class="form-control @error('site_description') is-invalid @enderror"
                            >{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                            @error('site_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('site_image_path') }}</label>
                            <input
                                type="text"
                                name="site_image"
                                value="{{ old('site_image', $settings['site_image'] ?? '') }}"
                                class="form-control @error('site_image') is-invalid @enderror"
                            >
                            @error('site_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('public_url') }}</label>
                            <input
                                type="url"
                                name="public_url"
                                value="{{ old('public_url', $settings['public_url'] ?? '') }}"
                                class="form-control @error('public_url') is-invalid @enderror"
                            >
                            @error('public_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card syscfg-form-card">
                <div class="card-body">
                    <div class="syscfg-section-title">{{ db_trans('localization') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('default_locale') }}</label>
                            <select name="default_locale" class="form-select @error('default_locale') is-invalid @enderror">
                                <option value="en" @selected(old('default_locale', $settings['default_locale'] ?? 'en') === 'en')>English</option>
                                <option value="sw" @selected(old('default_locale', $settings['default_locale'] ?? 'en') === 'sw')>Swahili</option>
                            </select>
                            @error('default_locale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('fallback_locale') }}</label>
                            <select name="fallback_locale" class="form-select @error('fallback_locale') is-invalid @enderror">
                                <option value="en" @selected(old('fallback_locale', $settings['fallback_locale'] ?? 'en') === 'en')>English</option>
                                <option value="sw" @selected(old('fallback_locale', $settings['fallback_locale'] ?? 'en') === 'sw')>Swahili</option>
                            </select>
                            @error('fallback_locale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

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