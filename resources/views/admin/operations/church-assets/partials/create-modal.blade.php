<div class="modal fade finance-modal" id="createChurchAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content ui-modal-card border-0" method="POST" action="{{ route('operations.church-assets.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title mb-0">{{ db_trans('add_church_asset') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
            </div>
            <div class="modal-body">
                @php($asset = null)
                @include('admin.operations.church-assets.partials.form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button>
            </div>
        </form>
    </div>
</div>
