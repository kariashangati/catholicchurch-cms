@props([
    'id',
    'title',
    'subtitle' => null,
    'size' => 'modal-lg',
    'footer' => null,
])

<div class="modal fade ui-modal-card" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog {{ $size }} modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header px-4 py-3">
                <div>
                    <h5 class="modal-title mb-1">{{ $title }}</h5>
                    @if($subtitle)
                        <div class="ui-helper-text">{{ $subtitle }}</div>
                    @endif
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
            </div>

            <div class="modal-body p-4">
                {{ $slot }}
            </div>

            @if($footer)
                <div class="modal-footer px-4 py-3">
                    {!! $footer !!}
                </div>
            @endif
        </div>
    </div>
</div>
