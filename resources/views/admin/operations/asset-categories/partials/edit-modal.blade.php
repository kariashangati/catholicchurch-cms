<div class="modal fade finance-modal" id="editAssetCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content ui-modal-card border-0" method="POST" action="{{ route('operations.asset-categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title mb-0">{{ db_trans('edit_asset_category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
            </div>
            <div class="modal-body">
                @include('admin.operations.asset-categories.partials.form', ['category' => $category])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ db_trans('update') }}</button>
            </div>
        </form>
    </div>
</div>
