@extends('layouts.admin')

@section('title', db_trans('receipts.create.title'))

@section('content')
<div class="receipt-page">
    <section class="receipt-hero">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.create.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ db_trans('receipts.create.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.create.subtitle') }}</p>
            </div>
            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.history') }}" class="btn btn-outline-light">
                    {{ db_trans('receipts.actions.view_history') }}
                </a>
            </div>
        </div>
    </section>

    <div class="receipt-surface card">
        <div class="receipt-surface__header">
            <div>
                <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.create.form_title') }}</h2>
                <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.create.form_hint') }}</p>
            </div>
        </div>

        <div class="receipt-surface__body">
            <form id="receiptGenerateForm" method="POST" action="{{ route('receipts.preview') }}" class="receipt-form">
                @csrf

                <div class="receipt-form-grid">
                    @include('admin.finance.receipts.partials._filters', [
                        'filters' => $filters ?? [],
                        'supportedTypes' => $supportedTypes ?? [],
                        'supportedLayouts' => $supportedLayouts ?? [],
                    ])
                </div>

                <div class="receipt-notice-panel mt-4">
                    <div class="receipt-notice-panel__icon">ℹ️</div>
                    <div>
                        <div class="fw-semibold">{{ db_trans('receipts.create.tip_title') }}</div>
                        <div class="text-muted">{{ db_trans('receipts.create.tip_message') }}</div>
                    </div>
                </div>

                <div class="receipt-form-actions mt-4">
                    <button type="submit" class="btn btn-primary btn-lg" data-receipt-preview>
                        {{ db_trans('receipts.actions.preview_receipt') }}
                    </button>
                    <a href="{{ route('receipts.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                        {{ db_trans('receipts.actions.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
