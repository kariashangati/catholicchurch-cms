@props([
    'links' => [],
    'title' => null,
    'subtitle' => null,
    'badge' => null,
    'icon' => 'fas fa-bolt',
    'emptyMessage' => 'No quick actions available.',
])

<x-admin.panel {{ $attributes }} :title="$title" :subtitle="$subtitle" :icon="$icon">
    @if($badge)
        <div class="mb-3">
            <span class="ui-section-badge">{{ $badge }}</span>
        </div>
    @endif

    @forelse($links as $link)
        <a href="{{ $link['href'] ?? $link['route'] ?? '#' }}" class="ui-quick-link">
            <div class="ui-quick-link-left">
                <span class="ui-quick-link-icon">
                    <i class="{{ $link['icon'] ?? 'fas fa-arrow-right' }}"></i>
                </span>
                <span class="ui-quick-link-label">{{ $link['label'] ?? '' }}</span>
            </div>
            <i class="fas fa-arrow-right"></i>
        </a>
    @empty
        <x-admin.empty-state :message="$emptyMessage" icon="fas fa-inbox" />
    @endforelse

    {{ $slot }}
</x-admin.panel>
