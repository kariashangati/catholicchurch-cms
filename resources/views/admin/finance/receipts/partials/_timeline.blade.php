@if(!empty($timeline) && count($timeline))
    <div class="receipt-timeline">
        @foreach($timeline as $event)
            <div class="receipt-timeline-item">
                <div class="receipt-timeline-dot"></div>
                <div class="receipt-timeline-content">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold">{{ $event['title'] ?? '-' }}</div>
                            @if(!empty($event['description']))
                                <div class="text-muted small">{{ $event['description'] }}</div>
                            @endif
                        </div>
                        <span class="text-muted small">{{ $event['time'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
