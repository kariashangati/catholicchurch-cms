@props([
    'title',
    'text',
])

<div {{ $attributes->merge(['class' => 'ui-note-box']) }}>
    <div class="ui-note-title">{{ $title }}</div>
    <p class="ui-note-text">{{ $text }}</p>
</div>
