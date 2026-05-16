<div class="row g-3">
    @foreach(($cards ?? []) as $card)
        <div class="col-md-6 col-xl-3">
            <div class="receipt-summary-card">
                <div class="receipt-summary-card__label">{{ $card['label'] ?? '-' }}</div>
                <div class="receipt-summary-card__value">{{ $card['value'] ?? 0 }}</div>
                @if(!empty($card['hint']))
                    <div class="receipt-summary-card__hint">{{ $card['hint'] }}</div>
                @endif
            </div>
        </div>
    @endforeach
</div>
