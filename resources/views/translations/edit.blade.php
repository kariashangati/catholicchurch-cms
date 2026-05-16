@extends('layouts.admin')

@section('title', db_trans('edit_translation'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/translations-module-v4.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 translations-module-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <span class="ui-page-badge"><i class="fas fa-pen"></i>{{ db_trans('edit_translation') }}</span>
                <h1 class="ui-page-title mt-3">{{ db_trans('edit_translation') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-key me-1"></i>{{ $translation->translation_key }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-language me-1"></i>{{ strtoupper($translation->locale) }}</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('translations.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('back') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="ui-panel p-4">
                <div class="ui-panel-head">
                    <div><h5 class="ui-panel-title mb-1">{{ db_trans('basic_information') }}</h5></div>
                    <div class="ui-panel-icon"><i class="fas fa-language"></i></div>
                </div>

                <form method="POST" action="{{ route('translations.update', $translation) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="locale_display" class="form-label fw-semibold">{{ db_trans('locale') }}</label>
                            <input id="locale_display" type="text" value="{{ strtoupper($translation->locale) }}" class="form-control" readonly disabled>
                        </div>

                        <div class="col-md-8">
                            <label for="translation_key_display" class="form-label fw-semibold">{{ db_trans('key') }}</label>
                            <input id="translation_key_display" type="text" value="{{ $translation->translation_key }}" class="form-control" readonly disabled>
                        </div>

                        <div class="col-12">
                            <label for="translation_value" class="form-label fw-semibold">{{ db_trans('value') }}</label>
                            <textarea id="translation_value" name="translation_value" rows="6" class="form-control @error('translation_value') is-invalid @enderror" required>{{ old('translation_value', $translation->translation_value) }}</textarea>
                            @error('translation_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="ui-btn-primary" type="submit">{{ db_trans('update') }}</button>
                        <a href="{{ route('translations.index') }}" class="ui-btn-light btn">{{ db_trans('cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
