@php
    $offeringType = $offeringType ?? null;
@endphp

<div class="modal fade finance-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <h5 class="modal-title mb-0">{{ $offeringType ? db_trans('edit') : db_trans('new_offering_type') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $offeringType->name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('category') }}</label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category', $offeringType->category ?? \App\Models\OfferingType::CATEGORY_GENERAL) === $category)>{{ db_trans($category) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ $modalId }}_active" @checked(old('is_active', $offeringType->is_active ?? true))>
                                <label class="form-check-label" for="{{ $modalId }}_active">{{ db_trans('active') }}</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $offeringType->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
