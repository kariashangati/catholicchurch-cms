@extends('layouts.admin')

@section('title', db_trans('edit_kanda'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/kanda-pages.css') }}">
@endpush

@section('content')
    <div class="kanda-pages">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                    <span class="dashboard-hero-badge">{{ db_trans('edit') }}</span>
                    <h2 class="dashboard-title mb-2">{{ db_trans('edit_kanda') }}</h2>
                    <p class="dashboard-subtitle mb-0">{{ $kanda->name }}</p>
                </div>
                <div class="col-lg-4">
                    <div class="hero-actions-grid">
                        <a href="{{ route('kandas.index') }}" class="hero-action-btn">
                            <span class="hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                            <span class="hero-action-text">{{ db_trans('back') }}</span>
                        </a>

                        <a href="{{ route('kandas.show', $kanda) }}" class="hero-action-btn">
                            <span class="hero-action-icon"><i class="fas fa-eye"></i></span>
                            <span class="hero-action-text">{{ db_trans('view') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card form-panel border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="form-panel-head mb-4">
                    <div>
                        <h4 class="form-panel-title">{{ db_trans('edit_kanda') }}</h4>
                        <p class="form-panel-subtitle">{{ db_trans('update_the_kanda_information_below') }}</p>
                    </div>
                </div>

                <form action="{{ route('kandas.update', $kanda) }}" method="POST" class="kanda-form js-kanda-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ db_trans('kanda') }}</label>
                            <input type="text" name="name" class="form-control modern-input @error('name') is-invalid @enderror" value="{{ old('name', $kanda->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ db_trans('code') }}</label>
                            <input type="text" name="code" class="form-control modern-input @error('code') is-invalid @enderror" value="{{ old('code', $kanda->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ db_trans('comment') }}</label>
                            <textarea name="comment" rows="5" class="form-control modern-input @error('comment') is-invalid @enderror">{{ old('comment', $kanda->comment) }}</textarea>
                            @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="modern-switch">
                                <div>
                                    <div class="modern-switch-title">{{ db_trans('active') }}</div>
                                    <div class="modern-switch-help">{{ db_trans('toggle_whether_this_kanda_is_active') }}</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $kanda->is_active) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn-primary btn-modern-primary px-4">
                                <i class="fas fa-save me-2"></i>{{ db_trans('update') }}
                            </button>

                            <a href="{{ route('kandas.show', $kanda) }}" class="btn btn-light btn-modern-light px-4">
                                {{ db_trans('cancel') }}
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