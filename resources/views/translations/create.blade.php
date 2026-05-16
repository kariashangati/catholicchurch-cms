@extends('layouts.admin')

@section('title', db_trans('add_translation'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/translations-module-v4.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 translations-module-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <span class="ui-page-badge"><i class="fas fa-plus"></i>{{ db_trans('add_translation') }}</span>
                <h1 class="ui-page-title mt-3">{{ db_trans('add_translation') }}</h1>
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
                    <div class="ui-panel-icon"><i class="fas fa-pen-nib"></i></div>
                </div>

                <form method="POST" action="{{ route('translations.store') }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="locale" class="form-label fw-semibold">{{ db_trans('locale') }}</label>
                            <select id="locale" name="locale" class="form-select @error('locale') is-invalid @enderror" required>
                                <option value="">{{ db_trans('select_locale') }}</option>
                                <option value="en" @selected(old('locale') === 'en')>English</option>
                                <option value="sw" @selected(old('locale') === 'sw')>Swahili</option>
                            </select>
                            @error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label for="translation_key" class="form-label fw-semibold">{{ db_trans('key') }}</label>
                            <input id="translation_key" type="text" name="translation_key" value="{{ old('translation_key') }}" class="form-control @error('translation_key') is-invalid @enderror" required>
                            @error('translation_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="translation_value" class="form-label fw-semibold">{{ db_trans('value') }}</label>
                            <textarea id="translation_value" name="translation_value" rows="6" class="form-control @error('translation_value') is-invalid @enderror" required>{{ old('translation_value') }}</textarea>
                            @error('translation_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="ui-btn-primary" type="submit">{{ db_trans('save') }}</button>
                        <a href="{{ route('translations.index') }}" class="ui-btn-light btn">{{ db_trans('cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
