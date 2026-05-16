@extends('layouts.admin')

@section('title', db_trans('receipts.dashboard.title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/receipts-dashboard.css') }}">
@endpush

@section('content')
<div class="receipt-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 mb-1">{{ db_trans('receipts.dashboard.title') }}</h1>
            <p class="mb-0 opacity-75">{{ db_trans('receipts.dashboard.subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('receipts.create') }}" class="btn btn-light">{{ db_trans('receipts.actions.issue_receipt') }}</a>
            <a href="{{ route('receipts.history') }}" class="btn btn-outline-light">{{ db_trans('receipts.actions.view_history') }}</a>
        </div>
    </div>
</div>

@include('admin.finance.receipts.partials._kpis', [
    'summary' => $summary ?? [],
    'pendingSummary' => $pendingSummary ?? [],
    'statusCards' => $statusCards ?? [],
])

<div class="row g-4 mt-1">
    <div class="col-12 col-xl-8">
        @include('admin.finance.receipts.partials._charts', ['analytics' => $analytics ?? []])
    </div>
    <div class="col-12 col-xl-4">
        @include('admin.finance.receipts.partials._funnel', ['deliveryFunnel' => $analytics['deliveryFunnel'] ?? []])
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-xl-7">
        <div class="receipt-card card">
            <div class="card-header">{{ db_trans('receipts.dashboard.recent_receipts') }}</div>
            <div class="card-body p-0">
                @if(($recentReceipts->count() ?? 0) > 0)
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ db_trans('receipts.labels.receipt_no') }}</th>
                                    <th>{{ db_trans('receipts.labels.recipient') }}</th>
                                    <th>{{ db_trans('receipts.labels.amount') }}</th>
                                    <th>{{ db_trans('receipts.labels.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReceipts as $receipt)
                                    <tr>
                                        <td>
                                            <a href="{{ route('receipts.show', $receipt) }}">{{ $receipt->receipt_no }}</a>
                                        </td>
                                        <td>{{ $receipt->recipient_name ?? $receipt->member?->full_name ?? '-' }}</td>
                                        <td>{{ number_format((float) ($receipt->amount ?? 0), 2) }}</td>
                                        <td>@include('admin.finance.receipts.partials._status_badge', ['status' => $receipt->status ?? 'issued'])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="receipt-empty-state">
                        <h5>{{ db_trans('receipts.dashboard.no_recent_receipts') }}</h5>
                        <p class="mb-0">{{ db_trans('receipts.dashboard.no_recent_receipts_message') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="receipt-card card h-100">
            <div class="card-header">{{ db_trans('receipts.dashboard.pending_overview') }}</div>
            <div class="card-body">
                <div class="receipt-meta-list">
                    <div class="receipt-meta-item">
                        <span class="receipt-access-label">{{ db_trans('receipts.labels.pending_total') }}</span>
                        <strong>{{ $pendingSummary['total'] ?? 0 }}</strong>
                    </div>
                    <div class="receipt-meta-item">
                        <span class="receipt-access-label">{{ db_trans('receipts.labels.pending_tithes') }}</span>
                        <strong>{{ $pendingSummary['tithes'] ?? 0 }}</strong>
                    </div>
                    <div class="receipt-meta-item">
                        <span class="receipt-access-label">{{ db_trans('receipts.labels.pending_contributions') }}</span>
                        <strong>{{ $pendingSummary['contributions'] ?? 0 }}</strong>
                    </div>
                    <div class="receipt-meta-item">
                        <span class="receipt-access-label">{{ db_trans('receipts.labels.pending_other') }}</span>
                        <strong>{{ $pendingSummary['other'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('receipts.pending') }}" class="btn btn-outline-primary">
                        {{ db_trans('receipts.actions.view_pending') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin/js/receipts-dashboard.js') }}"></script>
@endpush
