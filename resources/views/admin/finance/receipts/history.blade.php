@extends('layouts.admin')

@section('title', db_trans('receipts.history_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
@endpush

@section('content')
<div class="page-header receipts-hero mb-4">
    <div>
        <span class="section-eyebrow">{{ db_trans('receipts.module_label') }}</span>
        <h1 class="page-title mb-1">{{ db_trans('receipts.history_title') }}</h1>
        <p class="page-subtitle mb-0">{{ db_trans('receipts.history_subtitle') }}</p>
    </div>
</div>

@include('admin.finance.receipts.partials._filters', ['action' => route('receipts.history')])

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">{{ db_trans('receipts.sections.history_results') }}</h5>
            <p class="text-muted small mb-0">{{ db_trans('receipts.sections.history_results_hint') }}</p>
        </div>
        <span class="badge text-bg-light">{{ ($receipts ?? collect())->count() }} {{ db_trans('receipts.fields.records') }}</span>
    </div>
    <div class="card-body">
        @if(($receipts ?? collect())->isEmpty())
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3"><i class="fas fa-search"></i></div>
                <h6>{{ db_trans('receipts.empty.no_history_results') }}</h6>
                <p class="text-muted mb-0">{{ db_trans('receipts.empty.no_history_results_hint') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ db_trans('receipts.fields.receipt_no') }}</th>
                            <th>{{ db_trans('receipts.fields.recipient') }}</th>
                            <th>{{ db_trans('receipts.fields.type') }}</th>
                            <th>{{ db_trans('receipts.fields.scope') }}</th>
                            <th>{{ db_trans('receipts.fields.amount') }}</th>
                            <th>{{ db_trans('receipts.fields.status') }}</th>
                            <th>{{ db_trans('receipts.fields.issued_at') }}</th>
                            <th class="text-end">{{ db_trans('receipts.fields.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipts as $receipt)
                            <tr>
                                <td class="fw-semibold">{{ $receipt->receipt_no }}</td>
                                <td>{{ $receipt->recipient_name ?? '—' }}</td>
                                <td>{{ $receipt->receipt_type_label ?? $receipt->receipt_type ?? '—' }}</td>
                                <td>{{ $receipt->scope_label ?? $receipt->scope_type ?? '—' }}</td>
                                <td>{{ $receipt->formatted_amount ?? number_format((float) ($receipt->amount ?? 0), 2) }}</td>
                                <td>@include('admin.finance.receipts.partials._status_badge', ['status' => $receipt->status ?? null])</td>
                                <td>{{ optional($receipt->issued_at)->format('d M Y H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('receipts.show', $receipt) }}" class="btn btn-light">{{ db_trans('receipts.actions.view') }}</a>
                                        <a href="{{ route('receipts.pdf.download', $receipt) }}" class="btn btn-outline-secondary">{{ db_trans('receipts.actions.download') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
