<div class="text-center mb-4">
    <div class="fw-bold text-uppercase">{{ db_trans('parish') }} {{ $parishName ?? config('app.name') }}</div>

    @if(!empty($title))
        <div class="fw-bold text-uppercase mt-1">{{ $title }}</div>
    @endif

    @if(!empty($subtitle))
        <div class="text-muted small mt-1">{{ $subtitle }}</div>
    @endif
</div>