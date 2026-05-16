@extends('layouts.admin')

@section('title', db_trans('print_receipts'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-print"></i>{{ db_trans('receipt_print_center') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('receipt_print_center') }}</h1>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('receipts.index') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-list"></i></span><span class="ui-hero-action-text">{{ db_trans('pending_receipts') }}</span></a>
                    <a href="{{ (Route::has('receipt.verify.search') ? route('receipt.verify.search') : url('/receipts/verify')) }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-qrcode"></i></span><span class="ui-hero-action-text">{{ db_trans('verify_receipt') }}</span></a>
                </div>
            </div>
        </div>
    </div>

    @php
        $reviewAction = Route::has('receipts.review.safe')
            ? route('receipts.review.safe')
            : (Route::has('receipts.review') ? route('receipts.review') : url('/admin/finance/receipts/print-review'));
    @endphp

    <form method="GET" action="{{ $reviewAction }}" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">{{ db_trans('receipt_type') }}</label>
                    <select name="receipt_type" id="receiptType" class="form-select" required>
                        <option value="zaka">{{ db_trans('tithes') }}</option>
                        <option value="mchango">{{ db_trans('contributions') }}</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">{{ db_trans('scope') }}</label>
                    <select name="receipt_layout" id="receiptLayout" class="form-select" required>
                        <option value="mwanajumuiya">{{ db_trans('member') }}</option>
                        <option value="jumuiya">{{ db_trans('jumuiya') }}</option>
                        <option value="kanda">{{ db_trans('kanda') }}</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 js-kanda-field">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" id="receiptKanda" class="form-select">
                        <option value="">{{ db_trans('select_kanda') }}</option>
                        @foreach($kandas as $kanda)<option value="{{ $kanda->id }}">{{ $kanda->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 js-jumuiya-field">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" id="receiptJumuiya" class="form-select">
                        <option value="">{{ db_trans('select_jumuiya') }}</option>
                        @foreach($jumuiyas as $jumuiya)<option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}">{{ $jumuiya->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 js-member-field">
                    <label class="form-label">{{ db_trans('member') }}</label>
                    <select name="member_id" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($members as $member)<option value="{{ $member->id }}">{{ $member->full_name ?? $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 js-contribution-field">
                    <label class="form-label">{{ db_trans('contribution_type') }}</label>
                    <select name="contribution_type_id" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ now()->year }}" required>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select"><option value="">{{ db_trans('all_months') }}</option>@foreach(range(1,12) as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <button class="btn ui-btn-primary w-100"><i class="fas fa-eye me-1"></i>{{ db_trans('review') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('receiptType');
    const layout = document.getElementById('receiptLayout');
    const kanda = document.getElementById('receiptKanda');
    const jumuiya = document.getElementById('receiptJumuiya');
    const contributionFields = document.querySelectorAll('.js-contribution-field');
    const memberFields = document.querySelectorAll('.js-member-field');
    const jumuiyaFields = document.querySelectorAll('.js-jumuiya-field');
    const kandaFields = document.querySelectorAll('.js-kanda-field');
    const sync = function(){
        contributionFields.forEach(el => el.style.display = type.value === 'mchango' ? '' : 'none');
        memberFields.forEach(el => el.style.display = layout.value === 'mwanajumuiya' ? '' : 'none');
        jumuiyaFields.forEach(el => el.style.display = ['mwanajumuiya','jumuiya'].includes(layout.value) ? '' : 'none');
        kandaFields.forEach(el => el.style.display = ['jumuiya','kanda'].includes(layout.value) ? '' : 'none');
        if (jumuiya) Array.from(jumuiya.options).forEach(function(opt){ if (opt.value) opt.hidden = !!kanda.value && opt.dataset.kanda !== kanda.value; });
    };
    sync(); [type, layout, kanda].forEach(el => el && el.addEventListener('change', sync));
});
</script>
@endpush
