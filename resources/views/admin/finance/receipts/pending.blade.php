@extends('layouts.admin')

@section('title', db_trans('receipts.pending_title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/receipts-module-v1.css') }}">
@endpush

@section('content')
<div class="page-header receipts-hero mb-4">
    <div>
        <span class="section-eyebrow">{{ db_trans('receipts.module_label') }}</span>
        <h1 class="page-title mb-1">{{ db_trans('receipts.pending_title') }}</h1>
        <p class="page-subtitle mb-0">{{ db_trans('receipts.pending_subtitle') }}</p>
    </div>
</div>

@include('admin.finance.receipts.partials._filters', ['action' => route('receipts.pending')])

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">{{ db_trans('receipts.sections.pending_results') }}</h5>
            <p class="text-muted small mb-0">{{ db_trans('receipts.sections.pending_results_hint') }}</p>
        </div>
        <span class="badge text-bg-warning">{{ ($pendingItems ?? collect())->count() }} {{ db_trans('receipts.fields.pending_records') }}</span>
    </div>
    <div class="card-body">
        @if(($pendingItems ?? collect())->isEmpty())
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3"><i class="fas fa-check-circle"></i></div>
                <h6>{{ db_trans('receipts.empty.no_pending_items') }}</h6>
                <p class="text-muted mb-0">{{ db_trans('receipts.empty.no_pending_items_hint') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ db_trans('receipts.fields.source_type') }}</th>
                            <th>{{ db_trans('receipts.fields.recipient') }}</th>
                            <th>{{ db_trans('receipts.fields.scope') }}</th>
                            <th>{{ db_trans('receipts.fields.amount') }}</th>
                            <th>{{ db_trans('receipts.fields.recorded_at') }}</th>
                            <th class="text-end">{{ db_trans('receipts.fields.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingItems as $item)
                            <tr>
                                <td>{{ $item->source_type_label ?? $item->source_type ?? '—' }}</td>
                                <td>{{ $item->recipient_name ?? '—' }}</td>
                                <td>{{ $item->scope_label ?? $item->scope_type ?? '—' }}</td>
                                <td>{{ $item->formatted_amount ?? number_format((float) ($item->amount ?? 0), 2) }}</td>
                                <td>{{ optional($item->recorded_at)->format('d M Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('receipts.create', ['source_type' => $item->source_type ?? null, 'source_id' => $item->source_id ?? null]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i>{{ db_trans('receipts.actions.issue_now') }}
                                    </a>
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
