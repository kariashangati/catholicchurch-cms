@php
    $metaSource = $receipt ?? null;

    $receiptNo = data_get($metaSource, 'receipt_no')
        ?? data_get($viewData ?? [], 'receipt_no')
        ?? data_get($viewData ?? [], 'generated_number')
        ?? '—';

    $receiptType = data_get($metaSource, 'receipt_type_label')
        ?? data_get($metaSource, 'receipt_type')
        ?? data_get($viewData ?? [], 'receipt_type_label')
        ?? data_get($viewData ?? [], 'type')
        ?? '—';

    $receiptLayout = data_get($metaSource, 'receipt_layout_label')
        ?? data_get($metaSource, 'receipt_layout')
        ?? data_get($viewData ?? [], 'receipt_layout_label')
        ?? data_get($viewData ?? [], 'layout')
        ?? '—';

    $issuedAt = data_get($metaSource, 'issued_at')
        ?? data_get($viewData ?? [], 'issued_at')
        ?? now();

    $amount = data_get($metaSource, 'formatted_amount')
        ?? data_get($viewData ?? [], 'formatted_amount')
        ?? number_format((float) (
            data_get($metaSource, 'amount')
            ?? data_get($viewData ?? [], 'amount')
            ?? data_get($viewData ?? [], 'totals.amount')
            ?? 0
        ), 2);

    $channel = data_get($metaSource, 'channel_label')
        ?? data_get($metaSource, 'delivery_channel')
        ?? data_get($viewData ?? [], 'channel_label')
        ?? data_get($viewData ?? [], 'delivery_channel')
        ?? db_trans('common.not_available');
@endphp

<div class="receipt-meta-grid">
    <div class="meta-item">
        <span class="meta-label">{{ db_trans('receipts.fields.receipt_no') }}</span>
        <strong class="meta-value">{{ $receiptNo }}</strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">{{ db_trans('receipts.fields.receipt_type') }}</span>
        <strong class="meta-value">{{ $receiptType }}</strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">{{ db_trans('receipts.fields.layout') }}</span>
        <strong class="meta-value">{{ $receiptLayout }}</strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">{{ db_trans('receipts.fields.issued_at') }}</span>
        <strong class="meta-value">
            {{ $issuedAt ? \Illuminate\Support\Carbon::parse($issuedAt)->format('d M Y H:i') : '—' }}
        </strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">{{ db_trans('receipts.fields.amount') }}</span>
        <strong class="meta-value">{{ $amount }}</strong>
    </div>

    <div class="meta-item">
        <span class="meta-label">{{ db_trans('receipts.fields.channel') }}</span>
        <strong class="meta-value">{{ $channel }}</strong>
    </div>
</div>