@extends('layouts.admin')

@section('title', db_trans('receipts.preview.title'))

@section('content')
<div class="receipt-page">
    <section class="receipt-hero">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.preview.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ db_trans('receipts.preview.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.preview.subtitle') }}</p>
            </div>
            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.create') }}" class="btn btn-outline-light">
                    {{ db_trans('receipts.actions.back') }}
                </a>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="receipt-surface card h-100">
                <div class="receipt-surface__header">
                    <div>
                        <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.preview.receipt_preview') }}</h2>
                        <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.preview.receipt_preview_hint') }}</p>
                    </div>
                </div>
                <div class="receipt-surface__body">
                    <div class="receipt-preview-shell">
                        @include('admin.finance.receipts.partials._receipt_meta', ['viewData' => $viewData ?? []])
                        @include('admin.finance.receipts.partials._source_info', ['viewData' => $viewData ?? []])
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="receipt-surface card">
                <div class="receipt-surface__header">
                    <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.preview.actions') }}</h2>
                </div>
                <div class="receipt-surface__body">
                    <form id="receiptPreviewForm" method="POST" action="{{ route('receipts.issue') }}">
                        @csrf

                        @foreach(($preview['payload'] ?? []) as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $nestedKey => $nestedValue)
                                    <input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <div class="receipt-stack">
                            <button type="submit" class="btn btn-primary btn-lg w-100" data-receipt-issue>
                                {{ db_trans('receipts.actions.issue_receipt') }}
                            </button>
                            <a href="{{ route('receipts.create') }}" class="btn btn-outline-secondary w-100">
                                {{ db_trans('receipts.actions.edit_selection') }}
                            </a>
                        </div>

                        <div class="receipt-mini-note mt-4">
                            {{ db_trans('receipts.preview.confirmation_hint') }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
