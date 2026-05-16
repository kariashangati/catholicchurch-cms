@php
    $isEdit = $mode === 'edit';
    $formAction = $isEdit ? route('members.update', $member) : route('members.store');
    $title = $isEdit ? db_trans('edit_member') : db_trans('add_member');
@endphp

<div class="modal fade member-form-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content member-modal-shell">
            <div class="modal-header border-0 pb-0">
                <div>
                    <span class="dashboard-hero-badge">{{ $title }}</span>
                    <h4 class="mt-2 mb-0">{{ $title }}</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
            </div>

            <div class="modal-body pt-3">
                <form action="{{ $formAction }}" method="POST" class="member-entry-form">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <input type="hidden" name="member_form_mode" value="{{ $isEdit ? 'edit' : 'create' }}">
                    <input type="hidden" name="modal_member_id" value="{{ $member?->id }}">

                    @include('admin.members.partials.member-form-fields', [
                        'member' => $member,
                        'familias' => $familias,
                        'mode' => $mode,
                        'modalId' => $modalId,
                    ])

                    <div class="modal-footer border-0 px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            {{ $isEdit ? db_trans('save_changes') : db_trans('save_member') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>