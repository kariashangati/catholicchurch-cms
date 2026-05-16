<div class="modal fade finance-modal" id="editChurchAssetModal{{ $asset->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content ui-modal-card border-0" method="POST" action="{{ route('operations.church-assets.update', $asset) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title mb-0">{{ db_trans('edit_church_asset') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
            </div>
            <div class="modal-body">
                @include('admin.operations.church-assets.partials.form', ['asset' => $asset])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ db_trans('update') }}</button>
            </div>
        </form>
    </div>
</div>
