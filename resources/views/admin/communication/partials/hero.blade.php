<div class="cc-premium-hero mb-4 position-relative overflow-hidden">
    <div class="cc-premium-hero__overlay"></div>
    <div class="position-relative d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="cc-premium-kicker">{{ $kicker ?? db_trans('communication.common.kicker') }}</span>
            <h2 class="cc-premium-title mb-1">{{ $title }}</h2>
            @if(!empty($subtitle))
                <p class="cc-premium-subtitle mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if(!empty($stats ?? []))
            <div class="d-flex flex-wrap gap-2">
                @foreach($stats as $stat)
                    <div class="cc-premium-mini-stat">
                        <span class="cc-premium-mini-stat__label">{{ $stat['label'] }}</span>
                        <strong>{{ $stat['value'] }}</strong>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
