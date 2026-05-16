@extends('layouts.admin')
@section('title', $mode === 'create' ? db_trans('write_sermon') : db_trans('edit_sermon'))
@push('styles')<link rel="stylesheet" href="{{ asset('admin/css/sermons-module-v1.css') }}">@endpush
@section('content')
<div class="sermons-module">
    <div class="sermon-hero sermon-hero-compact"><div><h2 class="sermon-hero-title">{{ $mode === 'create' ? db_trans('write_sermon') : $sermon->title }}</h2><p class="sermon-hero-subtitle">{{ db_trans('sermon_form_hint') }}</p></div><a href="{{ route('sermons.index') }}" class="btn btn-light btn-sm">{{ db_trans('back') }}</a></div>
    <form method="POST" enctype="multipart/form-data" action="{{ $mode === 'create' ? route('sermons.store') : route('sermons.update',$sermon) }}" class="row g-4">
        @csrf @if($mode !== 'create') @method('PUT') @endif
        <div class="col-xl-8"><div class="card sermon-panel border-0"><div class="card-body">
            <div class="mb-3"><label class="form-label">{{ db_trans('title') }}</label><input name="title" value="{{ old('title',$sermon->title) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">{{ db_trans('summary') }}</label><textarea name="summary" class="form-control" rows="2">{{ old('summary',$sermon->summary) }}</textarea></div>
            <div class="mb-3"><label class="form-label">{{ db_trans('sermon_body') }}</label><textarea name="body_html" class="form-control js-tinymce" rows="14">{{ old('body_html',$sermon->body_html) }}</textarea></div>
        </div></div></div>
        <div class="col-xl-4"><div class="card sermon-panel border-0"><div class="card-body">
            <div class="mb-3"><label class="form-label">{{ db_trans('status') }}</label><select name="status" class="form-select">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status',$sermon->status)===$status)>{{ db_trans('sermon_status_'.$status) }}</option>@endforeach</select></div>
            <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active',$sermon->is_active))><label class="form-check-label">{{ db_trans('active') }}</label></div>
            <div class="mb-3"><label class="form-label">{{ db_trans('cover_image') }}</label><input type="file" name="cover_image" class="form-control" accept="image/*"></div>
            <div class="mb-3"><label class="form-label">{{ db_trans('video_source') }}</label><select name="video_source" class="form-select sermon-video-source"><option value="none">{{ db_trans('none') }}</option><option value="upload" @selected(old('video_source',$sermon->video_source)==='upload')>{{ db_trans('upload_video') }}</option><option value="url" @selected(old('video_source',$sermon->video_source)==='url')>{{ db_trans('video_url') }}</option></select></div>
            <div class="mb-3 sermon-video-upload"><label class="form-label">{{ db_trans('video_file') }}</label><input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/quicktime"></div>
            <div class="mb-3 sermon-video-url"><label class="form-label">{{ db_trans('video_url') }}</label><input type="url" name="video_url" value="{{ old('video_url',$sermon->video_url) }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">{{ db_trans('thumbnail') }}</label><input type="file" name="thumbnail" class="form-control" accept="image/*"></div>
        </div></div></div>
        @php
            $savedTarget = $sermon->relationLoaded('targets') ? $sermon->targets->first() : null;
            $savedTargetType = old('target_type', $savedTarget->target_type ?? 'all');
            $savedTargetIds = collect(old('target_ids', data_get($savedTarget?->meta, 'target_ids', [])))->map(fn ($id) => (string) $id)->all();
        @endphp
        <div class="col-12">
            <div class="card sermon-panel sermon-target-picker border-0">
                <div class="card-body">
                    <div class="sermon-target-head">
                        <div>
                            <span class="sermon-section-eyebrow"><i class="fas fa-users me-1"></i>{{ db_trans('target_audience') }}</span>
                            <h5 class="sermon-target-title">{{ db_trans('choose_target_type') }}</h5>
                            <p class="sermon-target-subtitle mb-0">{{ db_trans('target_audience_hint') }}</p>
                        </div>
                        <span class="sermon-target-counter" data-sermon-target-counter>0</span>
                    </div>

                    <div class="row g-3 align-items-start mt-1">
                        <div class="col-lg-4">
                            <label class="form-label sermon-filter-label">{{ db_trans('target_type') }}</label>
                            <select name="target_type" class="form-select sermon-target-type">
                                <option value="all" @selected($savedTargetType === 'all')>{{ db_trans('all_members') }}</option>
                                <option value="kanda" @selected($savedTargetType === 'kanda')>{{ db_trans('kanda') }}</option>
                                <option value="jumuiya" @selected($savedTargetType === 'jumuiya')>{{ db_trans('jumuiya') }}</option>
                                <option value="familia" @selected($savedTargetType === 'familia')>{{ db_trans('familia') }}</option>
                                <option value="custom" @selected($savedTargetType === 'custom')>{{ db_trans('custom_members') }}</option>
                            </select>

                            <div class="sermon-target-type-hints mt-3">
                                <div class="sermon-target-hint" data-target-hint="all"><i class="fas fa-globe-africa"></i><span>{{ db_trans('all_members') }}</span></div>
                                <div class="sermon-target-hint" data-target-hint="kanda"><i class="fas fa-layer-group"></i><span>{{ db_trans('kanda') }}</span></div>
                                <div class="sermon-target-hint" data-target-hint="jumuiya"><i class="fas fa-project-diagram"></i><span>{{ db_trans('jumuiya') }}</span></div>
                                <div class="sermon-target-hint" data-target-hint="familia"><i class="fas fa-home"></i><span>{{ db_trans('familia') }}</span></div>
                                <div class="sermon-target-hint" data-target-hint="custom"><i class="fas fa-user-check"></i><span>{{ db_trans('custom_members') }}</span></div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="sermon-target-box">
                                <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-2">
                                    <label class="form-label sermon-filter-label mb-0">{{ db_trans('target') }}</label>
                                    <div class="sermon-target-search-wrap">
                                        <i class="fas fa-search"></i>
                                        <input type="search" class="form-control form-control-sm sermon-target-search" placeholder="{{ db_trans('search') }}">
                                    </div>
                                </div>

                                <div class="sermon-target-checklist" data-sermon-target-checklist>
                                    <div class="sermon-target-check-group" data-type="kanda">
                                        <div class="sermon-target-check-group-title">{{ db_trans('kanda') }}</div>
                                        @foreach($kandas as $k)
                                            <label class="sermon-target-check-item" data-search-text="{{ strtolower($k->name) }}">
                                                <input type="checkbox"
                                                       name="target_ids[]"
                                                       value="{{ $k->id }}"
                                                       class="form-check-input sermon-target-check"
                                                       @checked(in_array((string) $k->id, $savedTargetIds, true))>
                                                <span class="sermon-target-check-box"><i class="fas fa-check"></i></span>
                                                <span class="sermon-target-check-text">{{ $k->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="sermon-target-check-group" data-type="jumuiya">
                                        <div class="sermon-target-check-group-title">{{ db_trans('jumuiya') }}</div>
                                        @foreach($jumuiyas as $j)
                                            <label class="sermon-target-check-item" data-search-text="{{ strtolower($j->name) }}">
                                                <input type="checkbox"
                                                       name="target_ids[]"
                                                       value="{{ $j->id }}"
                                                       class="form-check-input sermon-target-check"
                                                       @checked(in_array((string) $j->id, $savedTargetIds, true))>
                                                <span class="sermon-target-check-box"><i class="fas fa-check"></i></span>
                                                <span class="sermon-target-check-text">{{ $j->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="sermon-target-check-group" data-type="familia">
                                        <div class="sermon-target-check-group-title">{{ db_trans('familia') }}</div>
                                        @foreach($familias as $f)
                                            <label class="sermon-target-check-item" data-search-text="{{ strtolower($f->name) }}">
                                                <input type="checkbox"
                                                       name="target_ids[]"
                                                       value="{{ $f->id }}"
                                                       class="form-check-input sermon-target-check"
                                                       @checked(in_array((string) $f->id, $savedTargetIds, true))>
                                                <span class="sermon-target-check-box"><i class="fas fa-check"></i></span>
                                                <span class="sermon-target-check-text">{{ $f->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="sermon-target-check-group" data-type="custom">
                                        <div class="sermon-target-check-group-title">{{ db_trans('members') }}</div>
                                        @foreach($members as $m)
                                            <label class="sermon-target-check-item" data-search-text="{{ strtolower(($m->full_name ?? '').' '.($m->phone ?? '')) }}">
                                                <input type="checkbox"
                                                       name="target_ids[]"
                                                       value="{{ $m->id }}"
                                                       class="form-check-input sermon-target-check"
                                                       @checked(in_array((string) $m->id, $savedTargetIds, true))>
                                                <span class="sermon-target-check-box"><i class="fas fa-check"></i></span>
                                                <span class="sermon-target-check-text">{{ $m->full_name }} <small>{{ $m->phone }}</small></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="sermon-target-empty d-none" data-sermon-target-empty>
                                    <i class="fas fa-info-circle me-1"></i>{{ db_trans('all_members_selected_hint') }}
                                </div>

                                <div class="sermon-target-selected mt-2" data-sermon-target-selected></div>
                            </div>
                        </div>

                        @if($mode !== 'create')
                            <div class="col-12">
                                <label class="sermon-resync-card">
                                    <input type="checkbox" name="resync_recipients" value="1" class="form-check-input">
                                    <span>
                                        <strong>{{ db_trans('resync_recipients_and_tokens') }}</strong>
                                        <small>{{ db_trans('resync_recipients_hint') }}</small>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12"><button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>{{ db_trans('save') }}</button></div>
    </form>
</div>
@endsection
@push('scripts')<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script><script src="{{ asset('admin/js/sermons-module-v1.js') }}"></script>@endpush
