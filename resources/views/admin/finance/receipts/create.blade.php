@extends('layouts.admin')

@section('title', db_trans('receipts.create_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
@endpush

@section('content')
<div class="page-header receipts-hero mb-4">
    <div>
        <span class="section-eyebrow">{{ db_trans('receipts.module_label') }}</span>
        <h1 class="page-title mb-1">{{ db_trans('receipts.create_title') }}</h1>
        <p class="page-subtitle mb-0">{{ db_trans('receipts.create_subtitle') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-1">{{ db_trans('receipts.sections.receipt_configuration') }}</h5>
                <p class="text-muted small mb-0">{{ db_trans('receipts.sections.receipt_configuration_hint') }}</p>
            </div>

            <div class="card-body">
                <form action="{{ route('receipts.preview') }}" method="POST" class="receipt-form-grid">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.receipt_type') }}</label>
                            <select name="receipt_type" id="receiptTypeSelect" class="form-select @error('receipt_type') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($sourceTypes ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected(old('receipt_type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="source_type" id="sourceTypeMirror" value="{{ old('source_type') }}">
                            @error('receipt_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.receipt_layout') }}</label>
                            <select name="receipt_layout" class="form-select @error('receipt_layout') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($layouts ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected(old('receipt_layout') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('receipt_layout')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.scope') }}</label>
                            <select name="scope_type" class="form-select @error('scope_type') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($scopeTypes ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected(old('scope_type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('scope_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.period') }}</label>
                            <input type="month" name="period" value="{{ old('period') }}" class="form-control @error('period') is-invalid @enderror">
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.member') }}</label>
                            <select name="member_id" class="form-select @error('member_id') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($members ?? []) as $member)
                                    <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                        {{ $member->full_name ?? $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.familia') }}</label>
                            <select name="familia_id" class="form-select @error('familia_id') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($familias ?? []) as $familia)
                                    <option value="{{ $familia->id }}" @selected(old('familia_id') == $familia->id)>
                                        {{ $familia->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('familia_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.jumuiya') }}</label>
                            <select name="jumuiya_id" class="form-select @error('jumuiya_id') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($jumuiyas ?? []) as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" @selected(old('jumuiya_id') == $jumuiya->id)>
                                        {{ $jumuiya->name ?? $jumuiya->jina_la_jumuiya }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jumuiya_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.kanda') }}</label>
                            <select name="kanda_id" class="form-select @error('kanda_id') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($kandas ?? []) as $kanda)
                                    <option value="{{ $kanda->id }}" @selected(old('kanda_id') == $kanda->id)>
                                        {{ $kanda->name ?? $kanda->jina_la_kanda }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kanda_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipts.fields.contribution_type') }}</label>
                            <select name="contribution_type_id" class="form-select @error('contribution_type_id') is-invalid @enderror">
                                <option value="">{{ db_trans('common.select_option') }}</option>
                                @foreach(($contributionTypes ?? []) as $type)
                                    <option value="{{ $type->id }}" @selected(old('contribution_type_id') == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('contribution_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('receipts.dashboard') }}" class="btn btn-light">
                            {{ db_trans('common.cancel') }}
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>{{ db_trans('receipts.actions.preview') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-1">{{ db_trans('receipts.sections.guide_title') }}</h5>
                <p class="text-muted small mb-0">{{ db_trans('receipts.sections.guide_subtitle') }}</p>
            </div>

            <div class="card-body">
                <div class="guide-step mb-3">
                    <div class="guide-step-number">1</div>
                    <div>
                        <h6 class="mb-1">{{ db_trans('receipts.guide.select_source_title') }}</h6>
                        <p class="text-muted mb-0 small">{{ db_trans('receipts.guide.select_source_text') }}</p>
                    </div>
                </div>

                <div class="guide-step mb-3">
                    <div class="guide-step-number">2</div>
                    <div>
                        <h6 class="mb-1">{{ db_trans('receipts.guide.choose_scope_title') }}</h6>
                        <p class="text-muted mb-0 small">{{ db_trans('receipts.guide.choose_scope_text') }}</p>
                    </div>
                </div>

                <div class="guide-step">
                    <div class="guide-step-number">3</div>
                    <div>
                        <h6 class="mb-1">{{ db_trans('receipts.guide.preview_issue_title') }}</h6>
                        <p class="text-muted mb-0 small">{{ db_trans('receipts.guide.preview_issue_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const receiptType = document.getElementById('receiptTypeSelect');
    const sourceType = document.getElementById('sourceTypeMirror');

    if (receiptType && sourceType) {
        const syncReceiptType = () => {
            sourceType.value = receiptType.value;
        };

        syncReceiptType();
        receiptType.addEventListener('change', syncReceiptType);
    }
});
</script>
@endpush