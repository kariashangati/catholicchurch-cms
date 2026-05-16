@extends('layouts.admin')

@section('title', db_trans('receipts'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
@endpush

@section('content')
@php
    $filters = $filters ?? [];
    $currency = fn($amount) => number_format((float) $amount, 2);
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-receipt"></i>{{ db_trans('receipts') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('receipts') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format(data_get($stats, 'total_rows', 0)) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-clock"></i>{{ db_trans('pending') }}: {{ number_format(data_get($stats, 'pending_rows', 0)) }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-wallet"></i>{{ $currency(data_get($stats, 'total_amount', 0)) }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('receipts.create') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-print"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('print_receipts') }}</span>
                    </a>
                    <a href="{{ (Route::has('receipt.verify.search') ? route('receipt.verify.search') : url('/receipts/verify')) }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-qrcode"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('verify_receipt') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('receipts.index') }}" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('receipt_type') }}</label>
                    <select name="receipt_type" class="form-select">
                        <option value="zaka" @selected(($filters['receipt_type'] ?? '') === 'zaka')>{{ db_trans('tithes') }}</option>
                        <option value="mchango" @selected(($filters['receipt_type'] ?? '') === 'mchango')>{{ db_trans('contributions') }}</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select">
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ db_trans('pending') }}</option>
                        <option value="printed" @selected(($filters['status'] ?? '') === 'printed')>{{ db_trans('printed') }}</option>
                        <option value="all" @selected(($filters['status'] ?? '') === 'all')>{{ db_trans('all') }}</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" id="receiptKanda" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected((string)($filters['kanda_id'] ?? '') === (string)$kanda->id)>{{ $kanda->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" id="receiptJumuiya" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}" @selected((string)($filters['jumuiya_id'] ?? '') === (string)$jumuiya->id)>{{ $jumuiya->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-1 col-md-3">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <input type="number" min="2000" max="2100" name="year" class="form-control" value="{{ $filters['year'] ?? now()->year }}">
                </div>
                <div class="col-xl-1 col-md-3">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach(range(1,12) as $month)
                            <option value="{{ $month }}" @selected((string)($filters['month'] ?? '') === (string)$month)>{{ $month }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 d-flex gap-2">
                    <button class="btn ui-btn-primary w-100"><i class="fas fa-filter me-1"></i>{{ db_trans('filter_records') }}</button>
                    <a href="{{ route('receipts.index') }}" class="btn ui-btn-light w-100">{{ db_trans('reset') }}</a>
                </div>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('receipts.issue') }}">
        @csrf
        @foreach(['receipt_type','kanda_id','jumuiya_id','year','month'] as $field)
            <input type="hidden" name="{{ $field }}" value="{{ $filters[$field] ?? '' }}">
        @endforeach
        <input type="hidden" name="receipt_layout" value="mwanajumuiya">

        <div class="ui-table-card p-4">
            <div class="ui-section-heading">
                <div><h5 class="mb-1">{{ db_trans('pending_receipts') }}</h5></div>
                <span class="ui-section-badge">{{ number_format($rows->total()) }} {{ db_trans('records') }}</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAllReceipts"></th>
                        <th>{{ db_trans('member') }}</th>
                        <th>{{ db_trans('phone') }}</th>
                        <th>{{ db_trans('jumuiya') }}</th>
                        <th>{{ db_trans('type') }}</th>
                        <th>{{ db_trans('date') }}</th>
                        <th>{{ db_trans('amount') }}</th>
                        <th>{{ db_trans('status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>
                                @if(empty($row['receipt']))
                                    <input type="checkbox" class="receipt-row-check" name="source_keys[]" value="{{ $row['source_key'] }}">
                                @endif
                            </td>
                            <td><strong>{{ $row['member_name'] ?: '—' }}</strong></td>
                            <td>{{ $row['phone'] ?: '—' }}</td>
                            <td>{{ $row['jumuiya_name'] ?: '—' }}</td>
                            <td>{{ $row['contribution_type_name'] }}</td>
                            <td>{{ $row['contribution_date'] ? \Carbon\Carbon::parse($row['contribution_date'])->format('d M Y') : '—' }}</td>
                            <td class="ui-amount">{{ $currency($row['amount']) }}</td>
                            <td>
                                <span class="ui-status-pill {{ empty($row['receipt']) ? 'ui-status-pending' : 'ui-status-approved' }}">
                                    {{ empty($row['receipt']) ? db_trans('pending') : db_trans('printed') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                <div>{{ $rows->links() }}</div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="form-check form-switch me-2">
                        <input class="form-check-input" type="checkbox" name="send_sms" value="1" id="sendReceiptSms">
                        <label class="form-check-label" for="sendReceiptSms">{{ db_trans('send_sms_link') }}</label>
                    </div>
                    <button type="submit" class="btn ui-btn-primary"><i class="fas fa-print me-1"></i>{{ db_trans('print_selected') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kanda = document.getElementById('receiptKanda');
    const jumuiya = document.getElementById('receiptJumuiya');
    const sync = function () {
        if (!kanda || !jumuiya) return;
        Array.from(jumuiya.options).forEach(function(option){
            if (!option.value) return;
            option.hidden = !!kanda.value && option.dataset.kanda !== kanda.value;
        });
        if (jumuiya.selectedOptions[0] && jumuiya.selectedOptions[0].hidden) jumuiya.value = '';
    };
    sync(); if (kanda) kanda.addEventListener('change', sync);

    const checkAll = document.getElementById('checkAllReceipts');
    if (checkAll) checkAll.addEventListener('change', function(){
        document.querySelectorAll('.receipt-row-check').forEach(function(el){ el.checked = checkAll.checked; });
    });
});
</script>
@endpush
