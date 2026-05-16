@php
    $items = collect($items ?? []);
    $colors = $colors ?? ['blue', 'green', 'purple', 'yellow', 'slate'];
    $max = (float) ($items->max('value') ?? 0);
    $max = $max > 0 ? $max : 1;
@endphp

@if($items->isNotEmpty())
    <div class="bar-chart">
        @foreach($items as $index => $item)
            @php
                $value = (float) ($item['value'] ?? 0);
                $width = max(($value / $max) * 100, 2);
                $color = $colors[$index % count($colors)];
            @endphp
            <div class="bar-row">
                <div class="bar-label-wrap">
                    <span>{{ $item['label'] ?? '' }}</span>
                    <span>{{ $item['formatted'] ?? number_format($value, 2) }}</span>
                </div>
                <div class="bar-track">
                    <div class="bar-fill bar-fill-{{ $color }}" style="width: {{ $width }}%;"></div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-note">{{ db_trans('no_data_available') ?: 'No data available' }}</div>
@endif
