@extends('layouts.admin')

@section('title', db_trans('receipts.history.title'))

@section('content')
<div class="receipt-page">
    <section class="receipt-hero">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.history.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ db_trans('receipts.history.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.history.subtitle') }}</p>
            </div>
            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.create') }}" class="btn btn-light">{{ db_trans('receipts.actions.issue_receipt') }}</a>
            </div>
        </div>
    </section>

    <div class="receipt-surface card mb-4">
        <div class="receipt-surface__header">
            <div>
                <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.history.filters_title') }}</h2>
                <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.history.filters_hint') }}</p>
            </div>
        </div>
        <div class="receipt-surface__body">
            <form id="receiptFilterForm" method="GET" action="{{ route('receipts.history') }}">
                @include('admin.finance.receipts.partials._filters', [
                    'filters' => $filters ?? [],
                    'statusOptions' => $statusOptions ?? [],
                ])

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
            <div>
                <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.history.list_title') }}</h2>
                <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.history.list_hint') }}</p>
            </div>
        </div>

        <div class="receipt-surface__body p-0">
            @if(($receipts->count() ?? 0) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 receipt-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('receipts.labels.receipt_no') }}</th>
                                <th>{{ db_trans('receipts.labels.recipient') }}</th>
                                <th>{{ db_trans('receipts.labels.type') }}</th>
                                <th>{{ db_trans('receipts.labels.amount') }}</th>
                                <th>{{ db_trans('receipts.labels.issued_at') }}</th>
                                <th>{{ db_trans('receipts.labels.status') }}</th>
                                <th class="text-end">{{ db_trans('receipts.labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipts as $receipt)
                                <tr>
                                    <td>
                                        <a href="{{ route('receipts.show', $receipt) }}" class="receipt-table__primary">
                                            {{ $receipt->receipt_no }}
                                        </a>
                                    </td>
                                    <td>{{ $receipt->recipient_name ?? $receipt->member?->full_name ?? '-' }}</td>
                                    <td>{{ db_trans('receipts.types.' . ($receipt->receipt_type ?? 'general')) }}</td>
                                    <td>{{ number_format((float) ($receipt->amount ?? 0), 2) }}</td>
                                    <td>{{ optional($receipt->issued_at ?? $receipt->created_at)->format('d M Y H:i') }}</td>
                                    <td>@include('admin.finance.receipts.partials._status_badge', ['status' => $receipt->status ?? 'issued'])</td>
                                    <td class="text-end">
                                        <div class="receipt-inline-actions justify-content-end">
                                            <a href="{{ route('receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">
                                                {{ db_trans('receipts.actions.view') }}
                                            </a>
                                            <a href="{{ route('receipts.pdf.download', $receipt) }}" class="btn btn-sm btn-outline-secondary">
                                                {{ db_trans('receipts.actions.download_pdf') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($receipts, 'links'))
                    <div class="p-3">
                        {{ $receipts->links() }}
                    </div>
                @endif
            @else
                <div class="receipt-empty-state">
                    <div class="receipt-empty-state__icon">🔎</div>
                    <h5>{{ db_trans('receipts.history.empty_title') }}</h5>
                    <p class="mb-0">{{ db_trans('receipts.history.empty_message') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
