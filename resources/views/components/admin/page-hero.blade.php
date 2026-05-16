@props([
    'badge' => null,
    'title',
    'subtitle' => null,
    'meta' => [],
    'actions' => [],
    'actionColumn' => 4,
])

<div {{ $attributes->merge(['class' => 'ui-page-hero mb-4']) }}>
    <div class="ui-hero-pattern"></div>

    <div class="row align-items-center g-4 position-relative">
        <div class="col-lg-{{ max(1, 12 - (int) $actionColumn) }}">
            @if($badge)
                <span class="ui-page-badge">{{ $badge }}</span>
            @endif

            <h2 class="ui-page-title">{{ $title }}</h2>

            @if($subtitle)
                <p class="ui-page-subtitle mb-3">{{ $subtitle }}</p>
            @endif

            @if(count($meta))
                <div class="ui-meta-wrap">
                    @foreach($meta as $item)
                        <span class="ui-meta-pill {{ ($item['tone'] ?? null) === 'warning' ? 'ui-meta-pill-warning' : '' }}">
                            @if(!empty($item['icon']))
                                <i class="{{ $item['icon'] }}"></i>
                            @endif
                            {{ $item['label'] ?? '' }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if(count($actions))
            <div class="col-lg-{{ (int) $actionColumn }}">
                <div class="ui-actions-grid">
                    @foreach($actions as $action)
                        <a href="{{ $action['href'] ?? '#' }}"
                           class="ui-hero-action {{ !empty($action['class']) ? $action['class'] : '' }}">
                            <span class="ui-hero-action-icon">
                                <i class="{{ $action['icon'] ?? 'fas fa-arrow-right' }}"></i>
                            </span>
                            <span class="ui-hero-action-text">{{ $action['label'] ?? '' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
