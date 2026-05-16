<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <div class="receipt-kpi">
            <div class="small text-muted">{{ db_trans('receipts.kpis.issued_today') }}</div>
            <div class="h4 mb-0">{{ $summary['issued_today'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="receipt-kpi">
            <div class="small text-muted">{{ db_trans('receipts.kpis.issued_month') }}</div>
            <div class="h4 mb-0">{{ $summary['issued_this_month'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="receipt-kpi">
            <div class="small text-muted">{{ db_trans('receipts.kpis.pending_issue') }}</div>
            <div class="h4 mb-0">{{ $pendingSummary['total'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="receipt-kpi">
            <div class="small text-muted">{{ db_trans('receipts.kpis.downloaded') }}</div>
            <div class="h4 mb-0">{{ $summary['downloaded'] ?? 0 }}</div>
        </div>
    </div>
</div>

@if(!empty($statusCards))
    <div class="row g-3 mt-1">
        @foreach($statusCards as $card)
            <div class="col-md-6 col-xl-3">
                <div class="receipt-card card h-100">
                    <div class="card-body">
                        <div class="small text-muted">{{ $card['label'] ?? '-' }}</div>
                        <div class="h5 mb-0">{{ $card['value'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
