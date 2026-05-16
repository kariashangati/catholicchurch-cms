@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'fas fa-table',
    'tableClass' => 'table align-middle',
    'responsive' => true,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'card ui-table-card border-0']) }}>
    <div class="card-body p-0">
        @if($title || $subtitle)
            <div class="p-4 pb-0">
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
            </div>
        @endif

        @if($responsive)
            <div class="table-responsive">
                <table class="{{ $tableClass }}">
                    {{ $slot }}
                </table>
            </div>
        @else
            <table class="{{ $tableClass }}">
                {{ $slot }}
            </table>
        @endif

        @if($footer)
            <div class="px-4 pb-4 pt-3">
                {!! $footer !!}
            </div>
        @endif
    </div>
</div>
