@extends('layouts.admin')
@section('title', db_trans('receipt_review'))
@section('disable_default_alerts')@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
@endpush
@section('content')
@php $currency = fn($amount) => number_format((float)$amount, 2); @endphp
<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4"><div class="ui-hero-pattern"></div><div class="row g-4 align-items-center position-relative"><div class="col-xl-8"><span class="ui-page-badge"><i class="fas fa-eye"></i>{{ db_trans('receipt_review') }}</span><h1 class="ui-page-title mt-3 mb-2">{{ db_trans('receipt_review') }}</h1><div class="ui-meta-wrap mt-3"><span class="ui-meta-pill">{{ number_format($stats['rows_count'] ?? 0) }} {{ db_trans('receipts') }}</span><span class="ui-meta-pill ui-meta-pill-warning">{{ $currency($stats['total_amount'] ?? 0) }}</span></div></div><div class="col-xl-4"><div class="ui-actions-grid"><a href="{{ route('receipts.create') }}" class="ui-hero-action"><span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span><span class="ui-hero-action-text">{{ db_trans('back') }}</span></a></div></div></div></div>

    <form method="POST" action="{{ route('receipts.issue') }}">
        @csrf
        @foreach(['receipt_type','receipt_layout','kanda_id','jumuiya_id','member_id','contribution_type_id','year','month'] as $field)
            <input type="hidden" name="{{ $field }}" value="{{ $filters[$field] ?? '' }}">
        @endforeach
        <div class="ui-table-card p-4">
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ db_trans('recipient') }}</th><th>{{ db_trans('jumuiya') }}</th><th>{{ db_trans('items') }}</th><th>{{ db_trans('amount') }}</th></tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ $loop->iteration }}</td><td><strong>{{ $row['recipient_name'] }}</strong></td><td>{{ data_get($row, 'items.0.jumuiya_name', '—') }}</td><td>{{ count($row['items'] ?? []) }}</td><td class="ui-amount">{{ $currency($row['amount']) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">{{ db_trans('no_pending_receipts_found') }}</td></tr>@endforelse</tbody></table></div>
            <div class="d-flex justify-content-end gap-3 mt-3"><div class="form-check form-switch align-self-center"><input class="form-check-input" type="checkbox" name="send_sms" value="1" id="sendSms"><label for="sendSms" class="form-check-label">{{ db_trans('send_sms_link') }}</label></div><button class="btn ui-btn-primary" @disabled($rows->isEmpty())><i class="fas fa-file-pdf me-1"></i>{{ db_trans('download_pdf') }}</button></div>
        </div>
    </form>
</div>
@endsection
