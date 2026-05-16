@extends('layouts.admin')

@section('title', db_trans('receipts.pending.title'))

@section('content')
<div class="receipt-page">
    <section class="receipt-hero">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.pending.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ db_trans('receipts.pending.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.pending.subtitle') }}</p>
            </div>
            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.create') }}" class="btn btn-light">
                    {{ db_trans('receipts.actions.issue_receipt') }}
                </a>
            </div>
        </div>
    </section>

    <div class="receipt-surface card mb-4">
        <div class="receipt-surface__header">
            <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.pending.summary') }}</h2>
        </div>
        <div class="receipt-surface__body">
            <div class="receipt-meta-grid">
                @foreach(($pendingCounts ?? []) as $key => $count)
                    <div class="receipt-stat-tile">
                        <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.pending_' . $key) }}</span>
                        <strong class="receipt-stat-tile__value">{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="receipt-surface card mb-4">
        <div class="receipt-surface__header">
            <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.pending.filters_title') }}</h2>
        </div>
        <div class="receipt-surface__body">
            <form id="receiptFilterForm" method="GET" action="{{ route('receipts.pending') }}">
                @include('admin.finance.receipts.partials._filters', ['filters' => $filters ?? []])

                <div class="receipt-form-actions mt-4">
                    <button type="submit" class="btn btn-primary">{{ db_trans('receipts.actions.apply_filters') }}</button>
                    <button type="button" class="btn btn-outline-secondary" data-reset-receipt-filters>
                        {{ db_trans('receipts.actions.reset_filters') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="receipt-surface card">
        <div class="receipt-surface__header">
            <h2 class="receipt-section-title mb-0">{{ db_trans('receipts.pending.list_title') }}</h2>
        </div>
        <div class="receipt-surface__body p-0">
            @if(($pendingReceipts->count() ?? 0) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 receipt-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('receipts.labels.recipient') }}</th>
                                <th>{{ db_trans('receipts.labels.type') }}</th>
                                <th>{{ db_trans('receipts.labels.amount') }}</th>
                                <th>{{ db_trans('receipts.labels.date') }}</th>
                                <th class="text-end">{{ db_trans('receipts.labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReceipts as $item)
                                <tr>
                                    <td>{{ $item->recipient_name ?? $item->member?->full_name ?? '-' }}</td>
                                    <td>{{ db_trans('receipts.types.' . ($item->receipt_type ?? 'general')) }}</td>
                                    <td>{{ number_format((float) ($item->amount ?? 0), 2) }}</td>
                                    <td>{{ optional($item->contribution_date ?? $item->created_at)->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('receipts.create', ['source_id' => $item->id ?? null]) }}" class="btn btn-sm btn-primary">
                                            {{ db_trans('receipts.actions.issue_now') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($pendingReceipts, 'links'))
                    <div class="p-3">
                        {{ $pendingReceipts->links() }}
                    </div>
                @endif
            @else
                <div class="receipt-empty-state">
                    <div class="receipt-empty-state__icon">✅</div>
                    <h5>{{ db_trans('receipts.pending.empty_title') }}</h5>
                    <p class="mb-0">{{ db_trans('receipts.pending.empty_message') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
