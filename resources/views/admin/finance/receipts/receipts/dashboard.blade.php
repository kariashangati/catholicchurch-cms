@extends('layouts.admin')

@section('title', db_trans('receipts.dashboard.title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/receipts-dashboard.css') }}">
@endpush

@section('content')
<div class="receipt-page">
    <section class="receipt-hero receipt-hero--dashboard">
        <div class="receipt-hero__content">
            <div>
                <span class="receipt-eyebrow">{{ db_trans('receipts.dashboard.eyebrow') }}</span>
                <h1 class="receipt-hero__title">{{ db_trans('receipts.dashboard.title') }}</h1>
                <p class="receipt-hero__subtitle">{{ db_trans('receipts.dashboard.subtitle') }}</p>
            </div>

            <div class="receipt-hero__actions">
                <a href="{{ route('receipts.create') }}" class="btn btn-light btn-lg">
                    {{ db_trans('receipts.actions.issue_receipt') }}
                </a>
                <a href="{{ route('receipts.history') }}" class="btn btn-outline-light btn-lg">
                    {{ db_trans('receipts.actions.view_history') }}
                </a>
            </div>
        </div>
    </section>

    @include('admin.finance.receipts.partials._kpis', [
        'summary' => $summary ?? [],
        'pendingSummary' => $pendingSummary ?? [],
        'statusCards' => $statusCards ?? [],
    ])

    <div class="row g-4 mt-1">
        <div class="col-12 col-xxl-8">
            @include('admin.finance.receipts.partials._charts', ['analytics' => $analytics ?? []])
        </div>
        <div class="col-12 col-xxl-4">
            @include('admin.finance.receipts.partials._funnel', ['deliveryFunnel' => $analytics['deliveryFunnel'] ?? []])
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-7">
            <div class="receipt-surface card">
                <div class="receipt-surface__header">
                    <div>
                        <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.dashboard.recent_receipts') }}</h2>
                        <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.dashboard.recent_receipts_hint') }}</p>
                    </div>
                    <a href="{{ route('receipts.history') }}" class="btn btn-sm btn-outline-primary">
                        {{ db_trans('receipts.actions.view_all') }}
                    </a>
                </div>

                <div class="receipt-surface__body p-0">
                    @if(($recentReceipts->count() ?? 0) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 receipt-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('receipts.labels.receipt_no') }}</th>
                                        <th>{{ db_trans('receipts.labels.recipient') }}</th>
                                        <th>{{ db_trans('receipts.labels.type') }}</th>
                                        <th>{{ db_trans('receipts.labels.amount') }}</th>
                                        <th>{{ db_trans('receipts.labels.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentReceipts as $receipt)
                                        <tr>
                                            <td>
                                                <a href="{{ route('receipts.show', $receipt) }}" class="receipt-table__primary">
                                                    {{ $receipt->receipt_no }}
                                                </a>
                                            </td>
                                            <td>{{ $receipt->recipient_name ?? $receipt->member?->full_name ?? '-' }}</td>
                                            <td>{{ db_trans('receipts.types.' . ($receipt->receipt_type ?? 'general')) }}</td>
                                            <td>{{ number_format((float) ($receipt->amount ?? 0), 2) }}</td>
                                            <td>@include('admin.finance.receipts.partials._status_badge', ['status' => $receipt->status ?? 'issued'])</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="receipt-empty-state receipt-empty-state--soft">
                            <div class="receipt-empty-state__icon">🧾</div>
                            <h5>{{ db_trans('receipts.dashboard.no_recent_receipts') }}</h5>
                            <p class="mb-0">{{ db_trans('receipts.dashboard.no_recent_receipts_message') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="receipt-surface card h-100">
                <div class="receipt-surface__header">
                    <div>
                        <h2 class="receipt-section-title mb-1">{{ db_trans('receipts.dashboard.pending_overview') }}</h2>
                        <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.dashboard.pending_overview_hint') }}</p>
                    </div>
                </div>

                <div class="receipt-surface__body">
                    <div class="receipt-meta-grid">
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.pending_total') }}</span>
                            <strong class="receipt-stat-tile__value">{{ $pendingSummary['total'] ?? 0 }}</strong>
                        </div>
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.pending_tithes') }}</span>
                            <strong class="receipt-stat-tile__value">{{ $pendingSummary['tithes'] ?? 0 }}</strong>
                        </div>
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.pending_contributions') }}</span>
                            <strong class="receipt-stat-tile__value">{{ $pendingSummary['contributions'] ?? 0 }}</strong>
                        </div>
                        <div class="receipt-stat-tile">
                            <span class="receipt-stat-tile__label">{{ db_trans('receipts.labels.pending_other') }}</span>
                            <strong class="receipt-stat-tile__value">{{ $pendingSummary['other'] ?? 0 }}</strong>
                        </div>
                    </div>

                    <div class="receipt-quick-actions mt-4">
                        <a href="{{ route('receipts.pending') }}" class="btn btn-primary">
                            {{ db_trans('receipts.actions.view_pending') }}
                        </a>
                        <a href="{{ route('receipts.create') }}" class="btn btn-outline-primary">
                            {{ db_trans('receipts.actions.issue_receipt') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin/js/receipts-dashboard.js') }}"></script>
@endpush
