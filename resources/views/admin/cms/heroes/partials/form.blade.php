<div class="cms-shell">
    <div class="cms-hero cms-hero-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="cms-hero-badge"><i class="fas fa-images"></i>{{ db_trans('hero_banner') }}</span>
                <h2 class="cms-hero-title">{{ $pageTitle }}</h2>
            </div>

            <a href="{{ route('cms.heroes.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>{{ db_trans('hero_banners') }}
            </a>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="card cms-form-card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('background_type') }}</label>
                        <select name="background_type" class="form-select">
                            <option value="image" @selected(old('background_type', $heroBanner->background_type ?? 'image') === 'image')>{{ db_trans('image') }}</option>
                            <option value="video" @selected(old('background_type', $heroBanner->background_type ?? 'image') === 'video')>{{ db_trans('video') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('background_value') }}</label>
                        <input type="text" name="background_value" value="{{ old('background_value', $heroBanner->background_value ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('upload_background') }}</label>
                        <input type="file" name="background_file" class="form-control" accept="image/*,video/mp4,video/webm,video/ogg">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('poster_image') }}</label>
                        <input type="text" name="poster_image" value="{{ old('poster_image', $heroBanner->poster_image ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('upload_poster') }}</label>
                        <input type="file" name="poster_image_file" class="form-control" accept="image/*">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('primary_button_text') }}</label>
                        <input type="text" name="primary_button_text" value="{{ old('primary_button_text', $heroBanner->primary_button_text ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('primary_button_link') }}</label>
                        <input type="text" name="primary_button_link" value="{{ old('primary_button_link', $heroBanner->primary_button_link ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('secondary_button_text') }}</label>
                        <input type="text" name="secondary_button_text" value="{{ old('secondary_button_text', $heroBanner->secondary_button_text ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('secondary_button_link') }}</label>
                        <input type="text" name="secondary_button_link" value="{{ old('secondary_button_link', $heroBanner->secondary_button_link ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('overlay_opacity') }}</label>
                        <input type="number" step="0.01" min="0" max="1" name="overlay_opacity" value="{{ old('overlay_opacity', $heroBanner->overlay_opacity ?? '0.55') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('display_order') }}</label>
                        <input type="number" name="display_order" value="{{ old('display_order', $heroBanner->display_order ?? 0) }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('starts_at') }}</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($heroBanner?->starts_at) ? \Illuminate\Support\Carbon::parse($heroBanner->starts_at)->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('ends_at') }}</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($heroBanner?->ends_at) ? \Illuminate\Support\Carbon::parse($heroBanner->ends_at)->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected((string) old('is_active', isset($heroBanner) ? ($heroBanner->is_active ? '1' : '0') : '1') === '1')>{{ db_trans('active') }}</option>
                            <option value="0" @selected((string) old('is_active', isset($heroBanner) ? ($heroBanner->is_active ? '1' : '0') : '1') === '0')>{{ db_trans('inactive') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-sticky-actions d-flex justify-content-end gap-2">
            <a href="{{ route('cms.heroes.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
            </button>
        </div>
    </form>
</div>
