@extends('layouts.admin')

@section('title', $template->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/communication-templates.css') }}">
@endpush

@section('content')
<div class="communication-templates-page">
    <div class="dashboard-hero communication-hero mb-4">
        <div class="hero-pattern"></div>
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
                <span class="dashboard-hero-badge">{{ db_trans('communication.templates.preview_badge') }}</span>
                <h2 class="dashboard-title mb-2">{{ $template->name }}</h2>
                <p class="dashboard-subtitle mb-3">{{ $template->code }} · {{ strtoupper($template->locale) }}</p>
            </div>
            <div class="col-lg-4">
                <div class="hero-actions-grid">
                    <a href="{{ route('admin.communication.templates.edit', $template) }}" class="hero-action-btn">
                        <span class="hero-action-icon"><i class="fas fa-pen"></i></span>
                        <span class="hero-action-text">{{ db_trans('communication.templates.edit_action') }}</span>
                    </a>
                    <a href="{{ route('admin.communication.templates.index') }}" class="hero-action-btn">
                        <span class="hero-action-icon"><i class="fas fa-list"></i></span>
                        <span class="hero-action-text">{{ db_trans('communication.templates.back_to_list') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 template-form-card h-100">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="template-detail-item"><span>{{ db_trans('communication.templates.category') }}</span><strong>{{ $template->category }}</strong></div></div>
                        <div class="col-md-6"><div class="template-detail-item"><span>{{ db_trans('communication.templates.status') }}</span><strong>{{ db_trans('communication.templates.status_'.$template->status) }}</strong></div></div>
                        <div class="col-md-6"><div class="template-detail-item"><span>{{ db_trans('communication.templates.event_key') }}</span><strong>{{ $template->event_key ?: '—' }}</strong></div></div>
                        <div class="col-md-6"><div class="template-detail-item"><span>{{ db_trans('communication.templates.audience_type') }}</span><strong>{{ $template->audience_type ?: '—' }}</strong></div></div>
                        <div class="col-12"><div class="template-detail-item"><span>{{ db_trans('communication.templates.variables') }}</span><strong>{{ $template->variables ? implode(', ', $template->variables) : '—' }}</strong></div></div>
                        <div class="col-12"><div class="template-body-panel"><label>{{ db_trans('communication.templates.body') }}</label><div>{{ $template->body }}</div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 template-preview-card h-100">
                <div class="card-body p-4">
                    <div class="template-section-head mb-3">
                        <span class="template-section-pill template-section-pill-alt"><i class="fas fa-eye me-2"></i>{{ db_trans('communication.templates.live_preview') }}</span>
                        <h5 class="template-preview-title mb-1">{{ db_trans('communication.templates.preview_title') }}</h5>
                    </div>
                    <div class="sms-preview-device">
                        <div class="sms-preview-topbar"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
                        <div class="sms-preview-bubble">{{ $preview['rendered_body'] }}</div>
                        <div class="sms-preview-meta mt-3">
                            <div><strong>{{ db_trans('communication.templates.missing_variables') }}:</strong> {{ $preview['missing'] ? implode(', ', $preview['missing']) : '—' }}</div>
                            <div><strong>{{ db_trans('communication.templates.sample_recipient') }}:</strong> {{ $previewData['member_name'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
