@extends('layouts.admin')

@section('title', db_trans('homepage_builder'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-hero cms-hero-homepage">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="cms-hero-badge"><i class="fas fa-home"></i>{{ db_trans('homepage_builder') }}</span>
                    <h2 class="cms-hero-title">{{ db_trans('homepage_builder') }}</h2>
                </div>

                <a href="{{ route('cms.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('content_management_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('cms.homepage.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card cms-form-card">
                <div class="card-body">
                    <div class="cms-section-title">{{ db_trans('hero_settings') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('hero_badge') }}</label>
                            <input type="text" name="hero_badge" value="{{ old('hero_badge', $heroSettings['hero_badge'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('enable_video') }}</label>
                            <select name="enable_video" class="form-select">
                                <option value="1" @selected((string) old('enable_video', !empty($heroSettings['enable_video']) ? '1' : '0') === '1')>{{ db_trans('enabled') }}</option>
                                <option value="0" @selected((string) old('enable_video', !empty($heroSettings['enable_video']) ? '1' : '0') === '0')>{{ db_trans('disabled') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('overlay_opacity') }}</label>
                            <input type="number" step="0.01" min="0" max="1" name="overlay_opacity" value="{{ old('overlay_opacity', $heroSettings['overlay_opacity'] ?? '0.50') }}" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('video_url') }}</label>
                            <input type="text" name="video_url" value="{{ old('video_url', $heroSettings['video_url'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('upload_video') }}</label>
                            <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/ogg">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('poster_url') }}</label>
                            <input type="text" name="poster_url" value="{{ old('poster_url', $heroSettings['poster_url'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('upload_poster') }}</label>
                            <input type="file" name="poster_file" class="form-control" accept="image/*">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('image_url') }}</label>
                            <input type="text" name="image_url" value="{{ old('image_url', $heroSettings['image_url'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('upload_image') }}</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('mobile_image_url') }}</label>
                            <input type="text" name="mobile_image_url" value="{{ old('mobile_image_url', $heroSettings['mobile_image_url'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('upload_mobile_image') }}</label>
                            <input type="file" name="mobile_image_file" class="form-control" accept="image/*">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('primary_button_text') }}</label>
                            <input type="text" name="cta_primary_text" value="{{ old('cta_primary_text', $heroSettings['cta_primary_text'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('primary_button_link') }}</label>
                            <input type="text" name="cta_primary_link" value="{{ old('cta_primary_link', $heroSettings['cta_primary_link'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('secondary_button_text') }}</label>
                            <input type="text" name="cta_secondary_text" value="{{ old('cta_secondary_text', $heroSettings['cta_secondary_text'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('secondary_button_link') }}</label>
                            <input type="text" name="cta_secondary_link" value="{{ old('cta_secondary_link', $heroSettings['cta_secondary_link'] ?? '') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card cms-form-card">
                <div class="card-body">
                    <div class="cms-section-title">{{ db_trans('homepage_visibility') }}</div>

                    <div class="row g-3">
                        @foreach($homepageToggles as $key => $value)
                            <div class="col-lg-4">
                                <label class="form-label">{{ db_trans(str_replace('homepage.show_', '', $key)) }}</label>
                                <select name="{{ $key }}" class="form-select">
                                    <option value="1" @selected((string) old($key, $value ? '1' : '0') === '1')>{{ db_trans('enabled') }}</option>
                                    <option value="0" @selected((string) old($key, $value ? '1' : '0') === '0')>{{ db_trans('disabled') }}</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="cms-sticky-actions d-flex justify-content-end gap-2">
                <a href="{{ route('cms.dashboard') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
@endpush
