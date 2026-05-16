@props([
    'items' => [],
])

<div {{ $attributes->merge(['class' => 'ui-data-stack']) }}>
    @foreach($items as $item)
        <div class="ui-data-row">
            <div>
                <div class="ui-data-label">{{ $item['label'] ?? '' }}</div>
                @if(!empty($item['meta']))
                    <div class="ui-helper-text">{{ $item['meta'] }}</div>
                @endif
            </div>

            <div class="fw-bold text-dark">{{ $item['value'] ?? '' }}</div>
        </div>
    @endforeach
</div>
