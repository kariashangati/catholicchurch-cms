@extends('layouts.admin')

@section('title', db_trans('receipts.show.title'))

@section('content')
<div class="receipt-page">
    <section class="receipt-hero">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.show.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ $receipt->receipt_no ?? db_trans('receipts.show.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.show.subtitle') }}</p>
            </div>
            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.pdf.download', $receipt) }}" class="btn btn-light">
                    {{ db_trans('receipts.actions.download_pdf') }}
                </a>
                <a href="{{ route('receipts.history') }}" class="btn btn-outline-light">
                    {{ db_trans('receipts.actions.back_to_history') }}
                </a>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="receipt-surface card mb-4">
                <div class="receipt-surface__header">
                    <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.show.details') }}</h2>
                </div>
                <div class="receipt-surface__body">
                    @include('admin.finance.receipts.partials._receipt_meta', ['receipt' => $receipt, 'viewData' => $viewData ?? []])
                    <div class="mt-4">
                        @include('admin.finance.receipts.partials._source_info', ['receipt' => $receipt, 'viewData' => $viewData ?? []])
                    </div>
                </div>
            </div>

            <div class="receipt-surface card">
                <div class="receipt-surface__header">
                    <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.show.timeline') }}</h2>
                </div>
                <div class="receipt-surface__body">
                    @include('admin.finance.receipts.partials._timeline', ['timeline' => $timeline ?? []])
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="receipt-surface card mb-4">
                <div class="receipt-surface__header">
                    <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.show.status_panel') }}</h2>
                </div>
                <div class="receipt-surface__body">
                    <div class="receipt-stack">
                        @include('admin.finance.receipts.partials._status_badge', ['status' => $receipt->status ?? 'issued'])
                        @include('admin.finance.receipts.partials._delivery_status', ['receipt' => $receipt])
                    </div>

                    <div class="receipt-quick-actions mt-4">
                        <button type="button" class="btn btn-outline-primary" data-receipt-action data-href="{{ route('receipts.pdf.download', $receipt) }}">
                            {{ db_trans('receipts.actions.download_pdf') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="receipt-surface card">
                <div class="receipt-surface__header">
                    <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.show.audit_summary') }}</h2>
                </div>
                <div class="receipt-surface__body">
                    <div class="receipt-meta-grid">
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.download_count') }}</span>
                            <strong class="receipt-stat-tile__value">{{ $receipt->download_count ?? 0 }}</strong>
                        </div>
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.print_count') }}</span>
                            <strong class="receipt-stat-tile__value">{{ $receipt->print_count ?? 0 }}</strong>
                        </div>
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.first_downloaded_at') }}</span>
                            <strong class="receipt-stat-tile__value">{{ optional($receipt->first_downloaded_at)->format('d M Y H:i') ?? '-' }}</strong>
                        </div>
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.last_reprinted_at') }}</span>
                            <strong class="receipt-stat-tile__value">{{ optional($receipt->last_reprinted_at)->format('d M Y H:i') ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
