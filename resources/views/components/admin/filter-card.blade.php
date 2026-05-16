@props([
    'action' => '',
    'method' => 'GET',
    'title' => null,
    'subtitle' => null,
    'icon' => 'fas fa-filter',
    'resetUrl' => null,
    'submitLabel' => 'Apply filters',
])

<x-admin.panel
    {{ $attributes->merge(['class' => 'ui-filter-card']) }}
    :title="$title"
    :subtitle="$subtitle"
    :icon="$icon"
>
    <form action="{{ $action }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}">
        @if(strtoupper($method) !== 'GET')
            @csrf
            @method($method)
        @endif

        <div class="row g-3">
            {{ $slot }}
        </div>

        <div class="ui-inline-actions mt-3">
            <button type="submit" class="btn ui-btn-primary">{{ $submitLabel }}</button>

            @if($resetUrl)
                <a href="{{ $resetUrl }}" class="btn ui-btn-light">{{ db_trans('reset') }}</a>
            @endif
        </div>
    </form>
</x-admin.panel>
