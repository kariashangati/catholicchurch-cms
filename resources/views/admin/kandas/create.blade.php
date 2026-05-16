@extends('layouts.admin')

@section('title', db_trans('create_kanda'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/kanda-pages.css') }}">
@endpush

@section('content')
    <div class="kanda-pages">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                    <span class="dashboard-hero-badge">{{ db_trans('create_new') }}</span>
                    <h2 class="dashboard-title mb-2">{{ db_trans('create_kanda') }}</h2>
                    <p class="dashboard-subtitle mb-0">{{ db_trans('manage_kandas') }}</p>
                </div>

                <div class="col-lg-4">
                    <div class="hero-actions-grid">
                        <a href="{{ route('kandas.index') }}" class="hero-action-btn">
                            <span class="hero-action-icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span class="hero-action-text">{{ db_trans('back') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card form-panel border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="form-panel-head mb-4">
                    <div>
                        <h4 class="form-panel-title">{{ db_trans('create_kanda') }}</h4>
                        <p class="form-panel-subtitle">{{ db_trans('fill_the_form_below_to_create_a_new_kanda') }}</p>
                    </div>
                </div>

                <form action="{{ route('kandas.store') }}" method="POST" class="kanda-form js-kanda-form">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">{{ db_trans('kanda') }}</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                class="form-control modern-input @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="code" class="form-label fw-semibold">{{ db_trans('code') }}</label>
                            <input
                                id="code"
                                type="text"
                                name="code"
                                class="form-control modern-input @error('code') is-invalid @enderror"
                                value="{{ old('code') }}"
                                required
                            >
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="comment" class="form-label fw-semibold">{{ db_trans('comment') }}</label>
                            <textarea
                                id="comment"
                                name="comment"
                                rows="5"
                                class="form-control modern-input @error('comment') is-invalid @enderror"
                            >{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="modern-switch">
                                <div>
                                    <div class="modern-switch-title">{{ db_trans('active') }}</div>
                                    <div class="modern-switch-help">{{ db_trans('toggle_whether_this_kanda_is_active') }}</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        id="is_active"
                                        {{ old('is_active', 1) ? 'checked' : '' }}
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn-primary btn-modern-primary px-4">
                                <i class="fas fa-save me-2"></i>{{ db_trans('save') }}
                            </button>

                            <a href="{{ route('kandas.index') }}" class="btn btn-light btn-modern-light px-4">
                                {{ db_trans('back') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('admin/js/kanda-pages.js') }}"></script>
@endpush