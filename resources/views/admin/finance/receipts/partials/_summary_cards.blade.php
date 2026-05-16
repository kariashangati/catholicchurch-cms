@php
    $cards = $summaryCards ?? [];
@endphp

<div class="row g-4">
    @forelse($cards as $card)
        <div class="col-sm-6 col-xl-3">
            <div class="card summary-kpi-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="summary-label">{{ $card['label'] ?? '—' }}</div>
                        <div class="summary-value">{{ $card['value'] ?? 0 }}</div>
                        @if(!empty($card['hint']))
                            <div class="summary-hint">{{ $card['hint'] }}</div>
                        @endif
                    </div>
                    <div class="summary-icon">
                        <i class="{{ $card['icon'] ?? 'fas fa-chart-bar' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body text-muted text-center py-4">{{ db_trans('receipts.empty.no_summary_cards') }}</div>
            </div>
        </div>
    @endforelse
</div>
