<div class="cms-shell">
    <div class="cms-hero cms-hero-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="cms-hero-title">{{ $pageTitle }}</h2>
                <p class="cms-hero-subtitle">{{ $pageSubtitle }}</p>
            </div>

            <a href="{{ route('cms.announcements.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>{{ db_trans('announcements') }}
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
                        <input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}" class="form-control @error('title') is-invalid @enderror">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('slug') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $announcement->slug ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('image') }}</label>
                        <input type="text" name="image" value="{{ old('image', $announcement->image ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('publish_from') }}</label>
                        <input type="datetime-local" name="publish_from" value="{{ old('publish_from', isset($announcement?->publish_from) ? \Illuminate\Support\Carbon::parse($announcement->publish_from)->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('publish_until') }}</label>
                        <input type="datetime-local" name="publish_until" value="{{ old('publish_until', isset($announcement?->publish_until) ? \Illuminate\Support\Carbon::parse($announcement->publish_until)->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="is_published" class="form-select">
                            <option value="1" @selected((string) old('is_published', isset($announcement) ? ($announcement->is_published ? '1' : '0') : '1') === '1')>{{ db_trans('published') }}</option>
                            <option value="0" @selected((string) old('is_published', isset($announcement) ? ($announcement->is_published ? '1' : '0') : '1') === '0')>{{ db_trans('draft') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('display_order') }}</label>
                        <input type="number" name="display_order" value="{{ old('display_order', $announcement->display_order ?? 0) }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('featured') }}</label>
                        <select name="is_featured" class="form-select">
                            <option value="1" @selected((string) old('is_featured', isset($announcement) ? ($announcement->is_featured ? '1' : '0') : '0') === '1')>{{ db_trans('yes') }}</option>
                            <option value="0" @selected((string) old('is_featured', isset($announcement) ? ($announcement->is_featured ? '1' : '0') : '0') === '0')>{{ db_trans('no') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('show_on_homepage') }}</label>
                        <select name="show_on_homepage" class="form-select">
                            <option value="1" @selected((string) old('show_on_homepage', isset($announcement) ? ($announcement->show_on_homepage ? '1' : '0') : '1') === '1')>{{ db_trans('yes') }}</option>
                            <option value="0" @selected((string) old('show_on_homepage', isset($announcement) ? ($announcement->show_on_homepage ? '1' : '0') : '1') === '0')>{{ db_trans('no') }}</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('summary') }}</label>
                        <textarea name="summary" rows="3" class="form-control">{{ old('summary', $announcement->summary ?? '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('content') }}</label>
                        <textarea name="content" rows="12" class="form-control cms-richtext">{{ old('content', $announcement->content ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-sticky-actions d-flex justify-content-end gap-2">
            <a href="{{ route('cms.announcements.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
            </button>
        </div>
    </form>
</div>