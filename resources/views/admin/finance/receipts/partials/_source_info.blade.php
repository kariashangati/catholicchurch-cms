<div class="row g-3">
    <div class="col-md-6">
        <div class="info-card h-100">
            <div class="info-card-label">{{ db_trans('receipts.fields.recipient') }}</div>
            <div class="info-card-value">{{ $receipt->recipient_name ?? '—' }}</div>
            <div class="info-card-meta">{{ $receipt->recipient_phone ?? $receipt->recipient_identifier ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card h-100">
            <div class="info-card-label">{{ db_trans('receipts.fields.source_type') }}</div>
            <div class="info-card-value">{{ $receipt->source_type_label ?? $receipt->source_type ?? '—' }}</div>
            <div class="info-card-meta">{{ db_trans('receipts.fields.source_reference') }}: {{ $receipt->source_reference ?? $receipt->source_id ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card h-100">
            <div class="info-card-label">{{ db_trans('receipts.fields.member') }}</div>
            <div class="info-card-value">{{ $receipt->member_name ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card h-100">
            <div class="info-card-label">{{ db_trans('receipts.fields.jumuiya') }}</div>
            <div class="info-card-value">{{ $receipt->jumuiya_name ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card h-100">
            <div class="info-card-label">{{ db_trans('receipts.fields.kanda') }}</div>
            <div class="info-card-value">{{ $receipt->kanda_name ?? '—' }}</div>
        </div>
    </div>
</div>
