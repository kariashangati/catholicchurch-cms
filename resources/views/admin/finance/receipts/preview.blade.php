@extends('layouts.admin')

@section('title', db_trans('receipts.preview_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
@endpush

@section('content')
<div class="page-header receipts-hero mb-4">
    <div>
        <span class="section-eyebrow">{{ db_trans('receipts.module_label') }}</span>
        <h1 class="page-title mb-1">{{ db_trans('receipts.preview_title') }}</h1>
        <p class="page-subtitle mb-0">{{ db_trans('receipts.preview_subtitle') }}</p>
    </div>
</div>

@php
    $previewData = [
        'receipt_no' => $preview['generated_number'] ?? '—',
        'receipt_type' => $preview['type'] ?? null,
        'receipt_layout' => $preview['layout'] ?? null,
        'issued_at' => now(),
        'amount' => data_get($preview, 'totals.amount', 0),
        'delivery_channel' => data_get($preview, 'filters.delivery_channel'),
        'status' => 'pending_issue',

        'recipient_name' => data_get($preview, 'source.recipient_name'),
        'member_id' => data_get($preview, 'source.member_id') ?? data_get($preview, 'filters.member_id'),
        'familia_id' => data_get($preview, 'source.familia_id') ?? data_get($preview, 'filters.familia_id'),
        'jumuiya_id' => data_get($preview, 'source.jumuiya_id') ?? data_get($preview, 'filters.jumuiya_id'),
        'kanda_id' => data_get($preview, 'source.kanda_id') ?? data_get($preview, 'filters.kanda_id'),
        'source_type' => data_get($preview, 'source.source_type') ?? data_get($preview, 'filters.source_type') ?? $preview['type'] ?? null,
        'source_reference' => data_get($preview, 'source.source_id'),
    ];
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ db_trans('receipts.sections.preview_card_title') }}</h5>
                    <p class="text-muted small mb-0">{{ db_trans('receipts.sections.preview_card_hint') }}</p>
                </div>

                @include('admin.finance.receipts.partials._status_badge', [
                    'status' => $previewData['status'] ?? 'pending_issue'
                ])
            </div>

            <div class="card-body">
                @include('admin.finance.receipts.partials._receipt_meta', [
                    'receipt' => (object) $previewData,
                    'viewData' => $viewData ?? [],
                ])

                <hr>

                @include('admin.finance.receipts.partials._source_info', [
                    'receipt' => (object) $previewData,
                    'viewData' => $viewData ?? [],
                ])
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-1">{{ db_trans('receipts.sections.preview_actions') }}</h5>
                <p class="text-muted small mb-0">{{ db_trans('receipts.sections.preview_actions_hint') }}</p>
            </div>

            <div class="card-body">
                <form action="{{ route('receipts.issue') }}" method="POST" class="d-grid gap-2">
                    @csrf

                    <input type="hidden" name="receipt_type" value="{{ $preview['type'] ?? '' }}">
                    <input type="hidden" name="receipt_layout" value="{{ $preview['layout'] ?? '' }}">
                    <input type="hidden" name="source_type" value="{{ data_get($preview, 'filters.source_type', $preview['type'] ?? '') }}">

                    <input type="hidden" name="member_id" value="{{ data_get($preview, 'filters.member_id', '') }}">
                    <input type="hidden" name="familia_id" value="{{ data_get($preview, 'filters.familia_id', '') }}">
                    <input type="hidden" name="jumuiya_id" value="{{ data_get($preview, 'filters.jumuiya_id', '') }}">
                    <input type="hidden" name="kanda_id" value="{{ data_get($preview, 'filters.kanda_id', '') }}">
                    <input type="hidden" name="date_from" value="{{ data_get($preview, 'filters.date_from', '') }}">
                    <input type="hidden" name="date_to" value="{{ data_get($preview, 'filters.date_to', '') }}">
                    <input type="hidden" name="month" value="{{ data_get($preview, 'filters.month', '') }}">
                    <input type="hidden" name="year" value="{{ data_get($preview, 'filters.year', '') }}">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i>{{ db_trans('receipts.actions.issue_receipt') }}
                    </button>

                    <a href="{{ route('receipts.create') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i>{{ db_trans('receipts.actions.back_to_form') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection