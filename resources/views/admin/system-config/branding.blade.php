@extends('layouts.admin')

@section('title', db_trans('branding'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-branding">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="syscfg-hero-title">{{ db_trans('branding') }}</h2>
                    <p class="syscfg-hero-subtitle">{{ db_trans('manage_logo_favicon_and_site_image_assets') }}</p>
                </div>

                <a href="{{ route('system-config.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('system_configuration_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-config.branding.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card syscfg-form-card">
                        <div class="card-body">
                            <div class="syscfg-section-title">{{ db_trans('logo') }}</div>

                            @if(!empty($settings['site_logo']))
                                <div class="syscfg-image-preview-wrap mb-3">
                                    <img src="{{ asset($settings['site_logo']) }}" alt="logo" class="syscfg-image-preview" id="siteLogoPreview">
                                </div>
                            @else
                                <div class="syscfg-image-preview-wrap mb-3">
                                    <img src="" alt="logo preview" class="syscfg-image-preview d-none" id="siteLogoPreview">
                                </div>
                            @endif

                            <label class="form-label">{{ db_trans('upload_logo') }}</label>
                            <input
                                type="file"
                                name="site_logo"
                                class="form-control @error('site_logo') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp,.svg"
                                data-image-preview-input="#siteLogoPreview"
                            >
                            @error('site_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="remove_site_logo" value="1" id="removeSiteLogo">
                                <label class="form-check-label" for="removeSiteLogo">
                                    {{ db_trans('remove_current_logo') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card syscfg-form-card">
                        <div class="card-body">
                            <div class="syscfg-section-title">{{ db_trans('favicon') }}</div>

                            @if(!empty($settings['site_favicon']))
                                <div class="syscfg-image-preview-wrap mb-3">
                                    <img src="{{ asset($settings['site_favicon']) }}" alt="favicon" class="syscfg-favicon-large-preview" id="siteFaviconPreview">
                                </div>
                            @else
                                <div class="syscfg-image-preview-wrap mb-3">
                                    <img src="" alt="favicon preview" class="syscfg-favicon-large-preview d-none" id="siteFaviconPreview">
                                </div>
                            @endif

                            <label class="form-label">{{ db_trans('upload_favicon') }}</label>
                            <input
                                type="file"
                                name="site_favicon"
                                class="form-control @error('site_favicon') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.ico,.webp"
                                data-image-preview-input="#siteFaviconPreview"
                            >
                            @error('site_favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="remove_site_favicon" value="1" id="removeSiteFavicon">
                                <label class="form-check-label" for="removeSiteFavicon">
                                    {{ db_trans('remove_current_favicon') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card syscfg-form-card">
                        <div class="card-body">
                            <div class="syscfg-section-title">{{ db_trans('site_image') }}</div>

                            @if(!empty($settings['site_image']))
                                <div class="syscfg-image-preview-wrap mb-3">
                                    <img src="{{ asset($settings['site_image']) }}" alt="site image" class="syscfg-image-preview" id="siteImagePreview">
                                </div>
                            @else
                                <div class="syscfg-image-preview-wrap mb-3">
                                    <img src="" alt="site image preview" class="syscfg-image-preview d-none" id="siteImagePreview">
                                </div>
                            @endif

                            <label class="form-label">{{ db_trans('upload_site_image') }}</label>
                            <input
                                type="file"
                                name="site_image"
                                class="form-control @error('site_image') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp"
                                data-image-preview-input="#siteImagePreview"
                            >
                            @error('site_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="remove_site_image" value="1" id="removeSiteImage">
                                <label class="form-check-label" for="removeSiteImage">
                                    {{ db_trans('remove_current_site_image') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="syscfg-sticky-actions d-flex justify-content-end gap-2 mt-4">
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