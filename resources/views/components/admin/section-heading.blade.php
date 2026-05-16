@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeIcon' => null,
])

<div {{ $attributes->merge(['class' => 'ui-section-heading']) }}>
    <div>
        <h4 class="ui-section-title">{{ $title }}</h4>
        @if($subtitle)
            <p class="ui-section-subtitle mb-0">{{ $subtitle }}</p>
        @endif
    </div>

    @if($badge)
        <span class="ui-section-badge">
            @if($badgeIcon)
                <i class="{{ $badgeIcon }}"></i>
            @endif
            {{ $badge }}
        </span>
    @endif
</div>
