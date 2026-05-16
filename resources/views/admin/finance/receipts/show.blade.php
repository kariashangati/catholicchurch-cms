@extends('layouts.admin')

@section('title', db_trans('receipts.detail_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
@endpush

@section('content')
<div class="page-header receipts-hero mb-4">
    <div>
        <span class="section-eyebrow">{{ db_trans('receipts.module_label') }}</span>
        <h1 class="page-title mb-1">{{ $receipt->receipt_no ?? db_trans('receipts.detail_title') }}</h1>
        <p class="page-subtitle mb-0">{{ db_trans('receipts.detail_subtitle') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('receipts.pdf.download', $receipt) }}" class="btn btn-primary">
            <i class="fas fa-download me-1"></i>{{ db_trans('receipts.actions.download') }}
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ db_trans('receipts.sections.receipt_details') }}</h5>
                @include('admin.finance.receipts.partials._status_badge', ['status' => $receipt->status ?? null])
            </div>
            <div class="card-body">
                @include('admin.finance.receipts.partials._receipt_meta', ['receipt' => $receipt])
                <hr>
                @include('admin.finance.receipts.partials._source_info', ['receipt' => $receipt])
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">{{ db_trans('receipts.sections.lifecycle_snapshot') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>{{ db_trans('receipts.fields.issued_at') }}</span>
                    <strong>{{ optional($receipt->issued_at)->format('d M Y H:i') ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>{{ db_trans('receipts.fields.print_count') }}</span>
                    <strong>{{ $receipt->print_count ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>{{ db_trans('receipts.fields.download_count') }}</span>
                    <strong>{{ $receipt->download_count ?? 0 }}</strong>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">{{ db_trans('receipts.sections.quick_actions') }}</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('receipts.history') }}" class="btn btn-light">{{ db_trans('receipts.actions.back_to_history') }}</a>
                <a href="{{ route('receipts.pending') }}" class="btn btn-outline-warning">{{ db_trans('receipts.actions.view_pending') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
