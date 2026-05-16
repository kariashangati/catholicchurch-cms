@props([
    'title',
    'value',
    'meta' => null,
    'icon' => 'fas fa-chart-line',
    'tone' => 'primary',
    'chip' => null,
])

<div {{ $attributes->merge(['class' => "card ui-stat-card ui-tone-{$tone} border-0"]) }}>
    <div class="card-body">
        <div class="ui-stat-top">
            <div class="ui-stat-icon">
                <i class="{{ $icon }}"></i>
            </div>

            @if($chip)
                <span class="ui-chip">{{ $chip }}</span>
            @endif
        </div>

        <div class="ui-stat-label">{{ $title }}</div>
        <div class="ui-stat-value">{{ $value }}</div>

        @if($meta)
            <div class="ui-stat-meta">{{ $meta }}</div>
        @endif
    </div>
</div>
