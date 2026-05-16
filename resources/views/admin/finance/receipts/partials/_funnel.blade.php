<div class="receipt-card card h-100">
    <div class="card-header">{{ db_trans('receipts.charts.delivery_funnel') }}</div>

    <div class="card-body">
        @if(!empty($deliveryFunnel))
            <div class="receipt-funnel-list">
                @foreach($deliveryFunnel as $step)
                    <div class="receipt-funnel-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ db_trans('receipts.funnel.' . ($step['label'] ?? 'issued')) }}</span>
                            <strong>{{ $step['total'] ?? 0 }}</strong>
                        </div>
                        <div class="progress mt-2" role="progressbar" aria-valuenow="{{ $step['total'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ min(($step['total'] ?? 0), 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="receipt-empty-state">
                <h6>{{ db_trans('receipts.dashboard.no_funnel_data') }}</h6>
                <p class="mb-0">{{ db_trans('receipts.dashboard.no_funnel_data_message') }}</p>
            </div>
        @endif
    </div>
</div>
