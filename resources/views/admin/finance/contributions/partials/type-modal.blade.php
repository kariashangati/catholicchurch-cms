@php
    $type = $type ?? null;
    $hasInstallmentsId = $modalId . '_has_installments';
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
                    <div>
                        <h5 class="modal-title mb-1">{{ $title }}</h5>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('name') }}</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $type->name ?? '') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select">
                                <option value="1" @selected((string) old('is_active', $type->is_active ?? true) === '1' || old('is_active', $type->is_active ?? true) === true)>
                                    {{ db_trans('active') }}
                                </option>

                                <option value="0" @selected((string) old('is_active', $type->is_active ?? true) === '0')>
                                    {{ db_trans('inactive') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $type->description ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input installment-toggle"
                                    type="checkbox"
                                    name="has_installments"
                                    value="1"
                                    id="{{ $hasInstallmentsId }}"
                                    @checked(old('has_installments', $type->has_installments ?? false))
                                >

                                <label class="form-check-label" for="{{ $hasInstallmentsId }}">
                                    {{ db_trans('has_installments') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 installment-fields">
                            <label class="form-label">{{ db_trans('target_amount') }}</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="target_amount"
                                class="form-control"
                                value="{{ old('target_amount', $type?->plan?->target_amount ?? '') }}"
                            >
                        </div>

                        <div class="col-md-4 installment-fields">
                            <label class="form-label">{{ db_trans('installments_count') }}</label>
                            <input
                                type="number"
                                min="1"
                                name="installments_count"
                                class="form-control"
                                value="{{ old('installments_count', $type?->plan?->installments_count ?? '') }}"
                            >
                        </div>

                        <div class="col-md-4 installment-fields">
                            <label class="form-label">{{ db_trans('due_date') }}</label>
                            <input
                                type="date"
                                name="due_date"
                                class="form-control"
                                value="{{ old('due_date', $type?->plan?->due_date ? $type->plan->due_date->format('Y-m-d') : '') }}"
                            >
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ db_trans('cancel') }}
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ db_trans('save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>