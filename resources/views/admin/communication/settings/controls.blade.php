@extends('layouts.admin')

@section('title', db_trans('communication.controls.title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/communication-center-phase12.css') }}">
@endpush

@section('content')
<div class="container-fluid py-3">
    @include('admin.communication.partials.hero', [
        'kicker' => db_trans('communication.controls.kicker'),
        'title' => db_trans('communication.controls.title'),
        'subtitle' => db_trans('communication.controls.subtitle'),
    ])

    <form method="POST" action="{{ route('admin.communication.controls.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card cc-panel border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="cc-section-title"><i class="bi bi-moon-stars me-2"></i>{{ db_trans('communication.controls.quiet_hours_title') }}</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="quiet_hours_enabled" name="quiet_hours_enabled" value="1" {{ !empty($settings['quiet_hours_enabled']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="quiet_hours_enabled">{{ db_trans('communication.controls.quiet_hours_enabled') }}</label>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ db_trans('communication.controls.quiet_hours_start') }}</label>
                                <input type="time" name="quiet_hours_start" class="form-control" value="{{ $settings['quiet_hours_start'] ?? '21:00' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ db_trans('communication.controls.quiet_hours_end') }}</label>
                                <input type="time" name="quiet_hours_end" class="form-control" value="{{ $settings['quiet_hours_end'] ?? '06:00' }}">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="cc-section-title"><i class="bi bi-shield-lock me-2"></i>{{ db_trans('communication.controls.approvals_title') }}</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ db_trans('communication.controls.default_requires_approval') }}</label>
                                <select name="default_requires_approval" class="form-select">
                                    <option value="1" {{ !empty($settings['default_requires_approval']) ? 'selected' : '' }}>{{ db_trans('communication.common.yes') }}</option>
                                    <option value="0" {{ empty($settings['default_requires_approval']) ? 'selected' : '' }}>{{ db_trans('communication.common.no') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ db_trans('communication.controls.approval_threshold_recipients') }}</label>
                                <input type="number" min="0" name="approval_threshold_recipients" class="form-control" value="{{ $settings['approval_threshold_recipients'] ?? 250 }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ db_trans('communication.controls.approval_threshold_segments') }}</label>
                                <input type="number" min="0" name="approval_threshold_segments" class="form-control" value="{{ $settings['approval_threshold_segments'] ?? 350 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card cc-panel border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="cc-section-title"><i class="bi bi-arrow-repeat me-2"></i>{{ db_trans('communication.controls.delivery_rules_title') }}</h5>
                        <div class="mb-3">
                            <label class="form-label">{{ db_trans('communication.controls.default_duplicate_window_hours') }}</label>
                            <input type="number" min="0" max="168" name="default_duplicate_window_hours" class="form-control" value="{{ $settings['default_duplicate_window_hours'] ?? 24 }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ db_trans('communication.controls.allow_retry_failed') }}</label>
                            <select name="allow_retry_failed" class="form-select">
                                <option value="1" {{ !empty($settings['allow_retry_failed']) ? 'selected' : '' }}>{{ db_trans('communication.common.yes') }}</option>
                                <option value="0" {{ empty($settings['allow_retry_failed']) ? 'selected' : '' }}>{{ db_trans('communication.common.no') }}</option>
                            </select>
                        </div>

                        <div class="cc-control-note">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ db_trans('communication.controls.note') }}
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>{{ db_trans('communication.common.save_changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
