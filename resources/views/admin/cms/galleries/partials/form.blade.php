<div class="cms-shell">
    <div class="cms-hero cms-hero-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="cms-hero-badge"><i class="fas fa-camera-retro"></i>{{ db_trans('galleries') }}</span>
                <h2 class="cms-hero-title">{{ $pageTitle }}</h2>
            </div>

            <a href="{{ route('cms.galleries.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>{{ db_trans('galleries') }}
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
                <div class="cms-section-title">{{ db_trans('gallery_details') }}</div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('title') }}</label>
                        <input type="text" name="title" value="{{ old('title', $gallery->title ?? '') }}" class="form-control @error('title') is-invalid @enderror">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('slug') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $gallery->slug ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('cover_image') }}</label>
                        <input type="text" name="cover_image" value="{{ old('cover_image', $gallery->cover_image ?? '') }}" class="form-control">
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">{{ db_trans('upload_cover_image') }}</label>
                        <input type="file" name="cover_image_file" class="form-control" accept="image/*">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('event_date') }}</label>
                        <input type="date" name="event_date" value="{{ old('event_date', isset($gallery?->event_date) ? \Illuminate\Support\Carbon::parse($gallery->event_date)->format('Y-m-d') : '') }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('display_order') }}</label>
                        <input type="number" name="display_order" value="{{ old('display_order', $gallery->display_order ?? 0) }}" class="form-control">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="is_published" class="form-select">
                            <option value="1" @selected((string) old('is_published', isset($gallery) ? ($gallery->is_published ? '1' : '0') : '1') === '1')>{{ db_trans('published') }}</option>
                            <option value="0" @selected((string) old('is_published', isset($gallery) ? ($gallery->is_published ? '1' : '0') : '1') === '0')>{{ db_trans('draft') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('featured') }}</label>
                        <select name="is_featured" class="form-select">
                            <option value="1" @selected((string) old('is_featured', isset($gallery) ? ($gallery->is_featured ? '1' : '0') : '0') === '1')>{{ db_trans('yes') }}</option>
                            <option value="0" @selected((string) old('is_featured', isset($gallery) ? ($gallery->is_featured ? '1' : '0') : '0') === '0')>{{ db_trans('no') }}</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('description') }}</label>
                        <textarea name="description" rows="5" class="form-control">{{ old('description', $gallery->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-sticky-actions d-flex justify-content-end gap-2">
            <a href="{{ route('cms.galleries.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
            </button>
        </div>
    </form>

    @if(isset($gallery) && $gallery)
        <div class="card cms-form-card">
            <div class="card-body">
                <div class="cms-section-title">{{ db_trans('add_gallery_image') }}</div>

                <form method="POST" action="{{ route('cms.galleries.images.store', $gallery) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('image_path') }}</label>
                            <input type="text" name="image_path" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('upload_image') }}</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ db_trans('caption') }}</label>
                            <input type="text" name="caption" class="form-control">
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ db_trans('alt_text') }}</label>
                            <input type="text" name="alt_text" class="form-control">
                        </div>

                        <div class="col-lg-1">
                            <label class="form-label">{{ db_trans('order') }}</label>
                            <input type="number" name="display_order" value="0" class="form-control">
                        </div>

                        <div class="col-lg-1">
                            <label class="form-label">{{ db_trans('active') }}</label>
                            <select name="is_active" class="form-select">
                                <option value="1">{{ db_trans('yes') }}</option>
                                <option value="0">{{ db_trans('no') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-image me-2"></i>{{ db_trans('add_image') }}
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="cms-section-title">{{ db_trans('gallery_images') }}</div>

                <div class="table-responsive">
                    <table class="table cms-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ db_trans('image') }}</th>
                                <th>{{ db_trans('caption') }}</th>
                                <th>{{ db_trans('alt_text') }}</th>
                                <th>{{ db_trans('display_order') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gallery->images as $image)
                                <tr>
                                    <td>
                                        @if($image->image_path)
                                            <img src="{{ asset(ltrim($image->image_path, '/')) }}" alt="{{ $image->alt_text }}" class="cms-thumb" onerror="this.style.display='none'">
                                            <div class="small text-muted mt-1">{{ $image->image_path }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $image->caption ?: '—' }}</td>
                                    <td>{{ $image->alt_text ?: '—' }}</td>
                                    <td>{{ $image->display_order }}</td>
                                    <td><span class="badge {{ $image->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $image->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('cms.galleries.images.destroy', [$gallery, $image]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ db_trans('are_you_sure') }}')">{{ db_trans('delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
