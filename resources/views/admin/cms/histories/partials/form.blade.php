<div class="cms-shell">
    <div class="cms-hero cms-hero-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="cms-hero-badge"><i class="fas fa-landmark"></i>{{ db_trans('histories') }}</span>
                <h2 class="cms-hero-title">{{ $pageTitle }}</h2>
            </div>

            <a href="{{ route('cms.histories.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>{{ db_trans('histories') }}
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
                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('title') }}</label>
                        <input type="text" name="title" value="{{ old('title', $history->title ?? '') }}" class="form-control @error('title') is-invalid @enderror">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('slug') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $history->slug ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('featured_image') }}</label>
                        <input type="text" name="featured_image" value="{{ old('featured_image', $history->featured_image ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('upload_image') }}</label>
                        <input type="file" name="featured_image_file" class="form-control" accept="image/*">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('published_at') }}</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($history?->published_at) ? \Illuminate\Support\Carbon::parse($history->published_at)->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('display_order') }}</label>
                        <input type="number" name="display_order" value="{{ old('display_order', $history->display_order ?? 0) }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="is_published" class="form-select">
                            <option value="1" @selected((string) old('is_published', isset($history) ? ($history->is_published ? '1' : '0') : '1') === '1')>{{ db_trans('published') }}</option>
                            <option value="0" @selected((string) old('is_published', isset($history) ? ($history->is_published ? '1' : '0') : '1') === '0')>{{ db_trans('draft') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('featured') }}</label>
                        <select name="is_featured" class="form-select">
                            <option value="1" @selected((string) old('is_featured', isset($history) ? ($history->is_featured ? '1' : '0') : '0') === '1')>{{ db_trans('yes') }}</option>
                            <option value="0" @selected((string) old('is_featured', isset($history) ? ($history->is_featured ? '1' : '0') : '0') === '0')>{{ db_trans('no') }}</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('excerpt') }}</label>
                        <textarea name="excerpt" rows="3" class="form-control">{{ old('excerpt', $history->excerpt ?? '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('content') }}</label>
                        <textarea name="content" rows="14" class="form-control cms-richtext">{{ old('content', $history->content ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-sticky-actions d-flex justify-content-end gap-2">
            <a href="{{ route('cms.histories.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
            </button>
        </div>
    </form>
</div>
