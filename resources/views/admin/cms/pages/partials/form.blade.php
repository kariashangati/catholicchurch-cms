<div class="cms-shell">
    <div class="cms-hero cms-hero-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="cms-hero-title">{{ $pageTitle }}</h2>
                <p class="cms-hero-subtitle">{{ $pageSubtitle }}</p>
            </div>

            <a href="{{ route('cms.pages.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>{{ db_trans('pages') }}
            </a>
        </div>
    </div>

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="card cms-form-card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('title') }}</label>
                        <input type="text" name="title" value="{{ old('title', $pageModel->title ?? '') }}" class="form-control @error('title') is-invalid @enderror">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('slug') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $pageModel->slug ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('featured_image') }}</label>
                        <input type="text" name="featured_image" value="{{ old('featured_image', $pageModel->featured_image ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('template') }}</label>
                        <input type="text" name="template" value="{{ old('template', $pageModel->template ?? 'default') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('published_at') }}</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($pageModel?->published_at) ? \Illuminate\Support\Carbon::parse($pageModel->published_at)->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('menu_title') }}</label>
                        <input type="text" name="menu_title" value="{{ old('menu_title', $pageModel->menu_title ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('meta_title') }}</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $pageModel->meta_title ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="is_published" class="form-select">
                            <option value="1" @selected((string) old('is_published', isset($pageModel) ? ($pageModel->is_published ? '1' : '0') : '1') === '1')>{{ db_trans('published') }}</option>
                            <option value="0" @selected((string) old('is_published', isset($pageModel) ? ($pageModel->is_published ? '1' : '0') : '1') === '0')>{{ db_trans('draft') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('show_in_menu') }}</label>
                        <select name="show_in_menu" class="form-select">
                            <option value="1" @selected((string) old('show_in_menu', isset($pageModel) ? ($pageModel->show_in_menu ? '1' : '0') : '0') === '1')>{{ db_trans('yes') }}</option>
                            <option value="0" @selected((string) old('show_in_menu', isset($pageModel) ? ($pageModel->show_in_menu ? '1' : '0') : '0') === '0')>{{ db_trans('no') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('show_in_footer') }}</label>
                        <select name="show_in_footer" class="form-select">
                            <option value="1" @selected((string) old('show_in_footer', isset($pageModel) ? ($pageModel->show_in_footer ? '1' : '0') : '0') === '1')>{{ db_trans('yes') }}</option>
                            <option value="0" @selected((string) old('show_in_footer', isset($pageModel) ? ($pageModel->show_in_footer ? '1' : '0') : '0') === '0')>{{ db_trans('no') }}</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('excerpt') }}</label>
                        <textarea name="excerpt" rows="3" class="form-control">{{ old('excerpt', $pageModel->excerpt ?? '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('meta_description') }}</label>
                        <textarea name="meta_description" rows="3" class="form-control">{{ old('meta_description', $pageModel->meta_description ?? '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('content') }}</label>
                        <textarea name="content" rows="12" class="form-control cms-richtext">{{ old('content', $pageModel->content ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-sticky-actions d-flex justify-content-end gap-2">
            <a href="{{ route('cms.pages.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
            </button>
        </div>
    </form>
</div>