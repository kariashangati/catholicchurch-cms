@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'padding' => 'p-4',
])

<div {{ $attributes->merge(['class' => 'card ui-panel border-0']) }}>
    <div class="card-body {{ $padding }}">
        @if($title || $subtitle || $icon)
            <div class="ui-panel-head">
                <div>
                    @if($title)
                        <h5 class="ui-panel-title">{{ $title }}</h5>
                    @endif
                    @if($subtitle)
                        <p class="ui-panel-subtitle mb-0">{{ $subtitle }}</p>
                    @endif
                </div>

                @if($icon)
                    <div class="ui-panel-icon">
                        <i class="{{ $icon }}"></i>
                    </div>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
