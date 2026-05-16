<div class="d-flex flex-wrap gap-2 justify-content-end">
    @canany(['finance.update', 'finance.tithes.update'])
        <button
            type="button"
            class="btn btn-sm ui-btn-light px-3"
            data-bs-toggle="modal"
            data-bs-target="#editTitheModal-{{ $item->id }}"
        >
            {{ db_trans('edit') }}
        </button>
    @endcanany

    @canany(['finance.delete', 'finance.tithes.delete'])
        <form method="POST" action="{{ route('finance.tithes.destroy', $item) }}" class="delete-tithe-form d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm ui-btn-danger px-3">
                {{ db_trans('delete') }}
            </button>
        </form>
    @endcanany
</div>