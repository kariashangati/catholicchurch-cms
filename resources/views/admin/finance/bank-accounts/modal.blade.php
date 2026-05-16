@php
    $bankAccount = $bankAccount ?? null;
    $selectedStatus = old('status', $bankAccount ? \App\Models\BankAccount::normalizeStatus($bankAccount->status ?? null) : \App\Models\BankAccount::STATUS_ACTIVE);
    $isActiveId = $modalId . '_is_active';
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
                            <label class="form-label">{{ db_trans('bank_name') }}</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bankAccount->bank_name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('account_name') }}</label>
                            <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $bankAccount->account_name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('account_number') }}</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bankAccount->account_number ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('branch_name') }}</label>
                            <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $bankAccount->branch_name ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($selectedStatus === $status)>
                                        {{ db_trans($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block">{{ db_trans('active') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="{{ $isActiveId }}" name="is_active" value="1" @checked(old('is_active', $bankAccount->is_active ?? true))>
                                <label class="form-check-label" for="{{ $isActiveId }}">{{ db_trans('mark_as_active') }}</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $bankAccount->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
