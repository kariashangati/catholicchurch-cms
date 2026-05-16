@extends('layouts.admin')

@section('title', db_trans('send_new_sms'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/communication-sms-v2.css') }}">
@endpush

@section('content')
@php
    $members = collect($members ?? []);
    $familias = collect($familias ?? []);
    $jumuiyas = collect($jumuiyas ?? []);
    $kandas = collect($kandas ?? []);
    $ageGroups = collect($ageGroups ?? []);
    $contributionTypes = collect($contributionTypes ?? []);
    $templates = collect($templates ?? []);
    $smsSettings = $smsSettings ?? [];
    $latestBalance = $latestBalance ?? null;
@endphp

<div class="admin-ui-v4 communication-sms-v2">
    <div class="ui-page-hero sms-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-paper-plane"></i>{{ db_trans('sms_compose_center') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('send_new_sms') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-wallet"></i>{{ number_format((float) ($latestBalance?->balance_units ?? 0), 2) }} {{ $smsSettings['currency'] ?? 'TZS' }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-font"></i>{{ $smsSettings['segment_length'] ?? 160 }} {{ db_trans('characters') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-coins"></i>{{ number_format((float) ($smsSettings['sms_unit_price'] ?? 40), 2) }} {{ $smsSettings['currency'] ?? 'TZS' }} / {{ db_trans('sms_segment') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('admin.communication.sms.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('back') }}</span>
                    </a>
                    <a href="{{ route('admin.communication.templates.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-file-alt"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('sms_templates') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.communication.sms.store') }}" id="communicationSmsForm">
        @csrf
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="ui-table-card p-4">
                    <div class="ui-section-heading"><div><h5 class="mb-1">{{ db_trans('compose_message') }}</h5></div></div>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    @if (session('error'))<div class="alert alert-danger rounded-4">{{ session('error') }}</div>@endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('title') }}</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="{{ db_trans('optional_campaign_title') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('sms_template') }}</label>
                            <select name="template_id" id="smsTemplate" class="form-select">
                                <option value="">{{ db_trans('write_custom_message') }}</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" data-body="{{ e($template->body) }}" @selected((string) old('template_id') === (string) $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('audience') }}</label>
                            <select name="audience_type" id="audienceType" class="form-select" required>
                                <option value="all_members" @selected(old('audience_type') === 'all_members')>{{ db_trans('all_members') }}</option>
                                <option value="all_active_members" @selected(old('audience_type') === 'all_active_members')>{{ db_trans('all_active_members') }}</option>
                                <option value="member" @selected(old('audience_type') === 'member')>{{ db_trans('single_member') }}</option>
                                <option value="familia" @selected(old('audience_type') === 'familia')>{{ db_trans('single_familia') }}</option>
                                <option value="jumuiya" @selected(old('audience_type') === 'jumuiya')>{{ db_trans('single_jumuiya') }}</option>
                                <option value="kanda" @selected(old('audience_type') === 'kanda')>{{ db_trans('single_kanda') }}</option>
                                <option value="filtered_members" @selected(old('audience_type') === 'filtered_members')>{{ db_trans('filtered_members') }}</option>
                                <option value="non_tithe_givers" @selected(old('audience_type') === 'non_tithe_givers')>{{ db_trans('non_tithe_givers') }}</option>
                                <option value="non_contribution_givers" @selected(old('audience_type') === 'non_contribution_givers')>{{ db_trans('non_contribution_givers') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('year') }}</label>
                            <input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ old('year', now()->year) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('month') }}</label>
                            <select name="month" class="form-select">
                                <option value="">{{ db_trans('all_months') }}</option>
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected((string) old('month') === (string) $month)>{{ \Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 sms-target-field" data-audience="member">
                            <label class="form-label">{{ db_trans('member') }}</label>
                            <select name="member_id" class="form-select">
                                <option value="">{{ db_trans('select_member') }}</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" data-familia="{{ $member->familia_id }}" data-jumuiya="{{ $member->familia?->jumuiya_id }}" data-kanda="{{ $member->familia?->jumuiya?->kanda_id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->full_name }} · {{ $member->phone ?: '—' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 sms-target-field" data-audience="familia">
                            <label class="form-label">{{ db_trans('familia') }}</label>
                            <select name="familia_id" id="familiaSelect" class="form-select">
                                <option value="">{{ db_trans('select_familia') }}</option>
                                @foreach($familias as $familia)
                                    <option value="{{ $familia->id }}" data-jumuiya="{{ $familia->jumuiya_id }}" data-kanda="{{ $familia->jumuiya?->kanda_id }}" @selected((string) old('familia_id') === (string) $familia->id)>{{ $familia->name }} · {{ $familia->jumuiya?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 sms-target-field" data-audience="jumuiya kanda filtered_members non_tithe_givers non_contribution_givers">
                            <label class="form-label">{{ db_trans('kanda') }}</label>
                            <select name="kanda_id" id="kandaSelect" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" @selected((string) old('kanda_id') === (string) $kanda->id)>{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 sms-target-field" data-audience="jumuiya filtered_members non_tithe_givers non_contribution_givers">
                            <label class="form-label">{{ db_trans('jumuiya') }}</label>
                            <select name="jumuiya_id" id="jumuiyaSelect" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}" @selected((string) old('jumuiya_id') === (string) $jumuiya->id)>{{ $jumuiya->name }} · {{ $jumuiya->kanda?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 sms-target-field" data-audience="non_contribution_givers">
                            <label class="form-label">{{ db_trans('contribution_type') }}</label>
                            <select name="contribution_type_id" class="form-select">
                                <option value="">{{ db_trans('all_contribution_types') }}</option>
                                @foreach($contributionTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) old('contribution_type_id') === (string) $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 sms-target-field" data-audience="filtered_members">
                            <label class="form-label">{{ db_trans('gender') }}</label>
                            <select name="filters[gender]" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                <option value="Male" @selected(old('filters.gender') === 'Male')>{{ db_trans('male') }}</option>
                                <option value="Female" @selected(old('filters.gender') === 'Female')>{{ db_trans('female') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 sms-target-field" data-audience="filtered_members">
                            <label class="form-label">{{ db_trans('age_group') }}</label>
                            <select name="filters[age_group_id]" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($ageGroups as $ageGroup)
                                    <option value="{{ $ageGroup->id }}" @selected((string) old('filters.age_group_id') === (string) $ageGroup->id)>{{ $ageGroup->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('message') }}</label>
                            <textarea name="message" id="messageBody" rows="8" class="form-control sms-message-box" placeholder="{{ db_trans('type_your_sms_here') }}" required>{{ old('message') }}</textarea>
                            <div class="sms-char-line mt-2">
                                <span><strong id="charCount">0</strong> {{ db_trans('characters') }}</span>
                                <span><strong id="segmentCount">0</strong> {{ db_trans('sms_segments') }}</span>
                                <span><strong id="encodingText">GSM-7</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-table-card p-4 mb-4">
                    <div class="ui-section-heading"><div><h5 class="mb-1">{{ db_trans('delivery_preview') }}</h5></div></div>
                    <div class="sms-preview-grid">
                        <div><span>{{ db_trans('recipients') }}</span><strong id="previewRecipients">0</strong></div>
                        <div><span>{{ db_trans('segments') }}</span><strong id="previewSegments">0</strong></div>
                        <div><span>{{ db_trans('total_segments') }}</span><strong id="previewTotalSegments">0</strong></div>
                        <div><span>{{ db_trans('estimated_cost') }}</span><strong id="previewCost">0.00</strong></div>
                    </div>
                    <div class="sms-balance-note mt-3" id="balanceNote">{{ db_trans('preview_to_check_sms_balance') }}</div>
                    <button type="button" id="previewBtn" class="btn ui-btn-primary w-100 mt-3"><i class="fas fa-search me-1"></i>{{ db_trans('preview') }}</button>
                </div>

                <div class="ui-table-card p-4">
                    <div class="ui-section-heading"><div><h5 class="mb-1">{{ db_trans('send_options') }}</h5></div></div>
                    <label class="form-label">{{ db_trans('send_mode') }}</label>
                    <select name="send_mode" id="sendMode" class="form-select mb-3">
                        <option value="now" @selected(old('send_mode', 'now') === 'now')>{{ db_trans('send_now') }}</option>
                        <option value="later" @selected(old('send_mode') === 'later')>{{ db_trans('schedule_for_later') }}</option>
                    </select>
                    <div id="scheduledAtWrap" class="{{ old('send_mode') === 'later' ? '' : 'd-none' }}">
                        <label class="form-label">{{ db_trans('scheduled_at') }}</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="form-control mb-3">
                    </div>
                    <button type="submit" id="submitSmsBtn" class="btn ui-btn-primary w-100"><i class="fas fa-paper-plane me-1"></i>{{ db_trans('save_and_continue') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin/js/communication-sms-v2.js') }}"></script>
<script>
window.communicationSmsConfig = {
    previewUrl: @json(route('admin.communication.sms.preview')),
    csrfToken: @json(csrf_token()),
    unitPrice: @json((float) ($smsSettings['sms_unit_price'] ?? 40)),
    currency: @json($smsSettings['currency'] ?? 'TZS'),
    translations: {
        enoughBalance: @json(db_trans('sms_balance_is_enough')),
        lowBalance: @json(db_trans('sms_balance_is_low')),
        previewError: @json(db_trans('preview_failed')),
        balanceFinished: @json(db_trans('sms_balance_finished_add_more'))
    },
    balance: @json((float) ($latestBalance?->balance_units ?? 0))
};
</script>
@endpush
