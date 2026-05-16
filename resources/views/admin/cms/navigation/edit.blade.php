@extends('layouts.admin')

@section('title', db_trans('navigation_settings'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-hero cms-hero-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="cms-hero-title">{{ db_trans('navigation_settings') }}</h2>
                    <p class="cms-hero-subtitle">{{ db_trans('manage_navbar_link_visibility') }}</p>
                </div>

                <a href="{{ route('cms.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('content_management_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('cms.navigation.update') }}">
            @csrf
            @method('PUT')

            <div class="card cms-form-card">
                <div class="card-body">
                    <div class="cms-section-title">{{ db_trans('navbar_visibility_controls') }}</div>

                    <div class="row g-3">
                        @foreach($settings as $key => $value)
                            <div class="col-lg-4">
                                <label class="form-label">{{ $key }}</label>
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
                <a href="{{ route('cms.dashboard') }}" class="btn btn-outline-secondary px-4">
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
    <script src="{{ asset('admin/js/cms.js') }}"></script>
@endpush