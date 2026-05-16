@php
    $meta = $viewData['meta'] ?? [];
@endphp

<div class="receipt-meta-grid">
    @foreach($meta as $item)
        <div class="receipt-stat-tile">
            <span class="receipt-stat-tile__label">{{ $item['label'] ?? '-' }}</span>
            <strong class="receipt-stat-tile__value">{{ $item['value'] ?? '-' }}</strong>
        </div>
    @endforeach
</div>
