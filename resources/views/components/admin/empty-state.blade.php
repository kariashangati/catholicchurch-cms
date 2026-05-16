@props([
    'title' => null,
    'message' => 'No records found.',
    'icon' => 'fas fa-inbox',
])

<div {{ $attributes->merge(['class' => 'ui-empty-state']) }}>
    <div class="ui-empty-icon">
        <i class="{{ $icon }}"></i>
    </div>

    @if($title)
        <div class="fw-bold mb-1">{{ $title }}</div>
    @endif

    <div>{{ $message }}</div>
</div>
