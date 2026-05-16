<div class="modal fade finance-modal" id="editAgeGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form id="editAgeGroupForm" method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title mb-1">{{ db_trans('edit') }} {{ db_trans('age_group') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none js-form-errors"></div>
                    @include('admin.membership.age-groups.partials.form-fields', ['genderScopes' => $genderScopes])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
