@php
    $sourceInfo = $viewData['source'] ?? [];
@endphp

<div class="receipt-info-panel">
    <div class="receipt-info-panel__header">
        <h3 class="receipt-section-title mb-1">{{ db_trans('receipts.show.source_information') }}</h3>
        <p class="receipt-section-subtitle mb-0">{{ db_trans('receipts.show.source_information_hint') }}</p>
    </div>

    <div class="receipt-meta-grid">
        @foreach($sourceInfo as $item)
            <div class="receipt-stat-tile">
                <span class="receipt-stat-tile__label">{{ $item['label'] ?? '-' }}</span>
                <strong class="receipt-stat-tile__value">{{ $item['value'] ?? '-' }}</strong>
            </div>
        @endforeach
    </div>
</div>
