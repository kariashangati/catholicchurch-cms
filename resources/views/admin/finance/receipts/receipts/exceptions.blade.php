@extends('layouts.admin')

@section('title', db_trans('receipts.exceptions.title'))

@section('content')
<div class="receipt-page">
    <section class="receipt-hero receipt-hero--danger">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.exceptions.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ db_trans('receipts.exceptions.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.exceptions.subtitle') }}</p>
            </div>
            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.history') }}" class="btn btn-light">
                    {{ db_trans('receipts.actions.back_to_history') }}
                </a>
            </div>
        </div>
    </section>

    <div class="receipt-surface card">
        <div class="receipt-surface__header">
            <div>
                <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.exceptions.list') }}</h2>
                <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.exceptions.list_hint') }}</p>
            </div>
            <span class="badge bg-danger rounded-pill px-3 py-2">{{ $exceptions->total() ?? $exceptions->count() }}</span>
        </div>

        <div class="receipt-surface__body p-0">
            @if(($exceptions->count() ?? 0) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 receipt-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('receipts.labels.receipt_no') }}</th>
                                <th>{{ db_trans('receipts.labels.exception_type') }}</th>
                                <th>{{ db_trans('receipts.labels.message') }}</th>
                                <th>{{ db_trans('receipts.labels.happened_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exceptions as $exception)
                                <tr>
                                    <td>{{ optional($exception->receiptIssue)->receipt_no ?? '-' }}</td>
                                    <td>{{ $exception->exception_type ?? '-' }}</td>
                                    <td class="text-wrap">{{ $exception->message ?? '-' }}</td>
                                    <td>{{ optional($exception->happened_at ?? $exception->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($exceptions, 'links'))
                    <div class="p-3">
                        {{ $exceptions->links() }}
                    </div>
                @endif
            @else
                <div class="receipt-empty-state receipt-empty-state--soft">
                    <div class="receipt-empty-state__icon">🎉</div>
                    <h5>{{ db_trans('receipts.exceptions.empty_title') }}</h5>
                    <p class="mb-0">{{ db_trans('receipts.exceptions.empty_message') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
