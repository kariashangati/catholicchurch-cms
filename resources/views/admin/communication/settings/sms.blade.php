@extends('layouts.admin')

@section('title', db_trans('sms_settings'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/communication-sms-v2.css') }}">
@endpush

@section('content')
@php($settings = $settings ?? [])
<div class="admin-ui-v4 communication-sms-v2">
    <div class="ui-page-hero sms-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-sliders-h"></i>{{ db_trans('sms_settings') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('sms_settings') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-font"></i>{{ $settings['segment_length'] ?? 160 }} {{ db_trans('characters') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-coins"></i>{{ number_format((float) ($settings['sms_unit_price'] ?? 40), 2) }} {{ $settings['currency'] ?? 'TZS' }}</span>
                </div>
            </div>
            <div class="col-xl-4"><div class="ui-actions-grid"><a href="{{ route('admin.communication.sms.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span><span class="ui-hero-action-text">{{ db_trans('back') }}</span></a></div></div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success rounded-4">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger rounded-4"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('admin.communication.sms.settings.update') }}" class="ui-table-card p-4">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">{{ db_trans('provider') }}</label><input type="text" name="provider" class="form-control" value="{{ old('provider', $settings['provider'] ?? 'beem') }}" required></div>
            <div class="col-md-4"><label class="form-label">{{ db_trans('sender_id') }}</label><input type="text" name="sender_id" class="form-control" value="{{ old('sender_id', $settings['sender_id'] ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">{{ db_trans('currency') }}</label><input type="text" name="currency" class="form-control" value="{{ old('currency', $settings['currency'] ?? 'TZS') }}" required></div>
            <div class="col-md-4"><label class="form-label">{{ db_trans('sms_segment_length') }}</label><input type="number" name="segment_length" class="form-control" min="1" value="{{ old('segment_length', $settings['segment_length'] ?? 160) }}" required></div>
            <div class="col-md-4"><label class="form-label">{{ db_trans('unicode_segment_length') }}</label><input type="number" name="unicode_segment_length" class="form-control" min="1" value="{{ old('unicode_segment_length', $settings['unicode_segment_length'] ?? 70) }}" required></div>
            <div class="col-md-4"><label class="form-label">{{ db_trans('sms_unit_price') }}</label><input type="number" step="0.01" name="sms_unit_price" class="form-control" min="0" value="{{ old('sms_unit_price', $settings['sms_unit_price'] ?? 40) }}" required></div>
            <div class="col-12"><label class="form-label">{{ db_trans('notes') }}</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $settings['notes'] ?? '') }}</textarea></div>
            <div class="col-12"><button class="btn ui-btn-primary"><i class="fas fa-save me-1"></i>{{ db_trans('save_changes') }}</button></div>
        </div>
    </form>
</div>
@endsection
