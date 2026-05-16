@php
    $item = $item ?? null;
    $context = $context ?? 'create';

    $selectedScope = old('form_context') === $context
        ? old('collection_scope', $item->collection_scope ?? 'parish')
        : ($item->collection_scope ?? 'parish');

    $paymentOptions = [
        'taslimu' => db_trans('cash'),
        'benki' => db_trans('bank'),
        'simu' => db_trans('mobile_money'),
        'nyingine' => db_trans('other'),
    ];

    $statusOptions = [
        'inasubiri' => db_trans('pending'),
        'imeidhinishwa' => db_trans('approved'),
        'imekataliwa' => db_trans('rejected'),
    ];

    $currentPayment = old('form_context') === $context
        ? old('payment_method', $item->payment_method ?? 'taslimu')
        : ($item->payment_method ?? 'taslimu');

    $currentStatus = old('form_context') === $context
        ? old('status', $item->status ?? 'imeidhinishwa')
        : ($item->status ?? 'imeidhinishwa');

    $currentPayment = match($currentPayment) {
        'cash' => 'taslimu',
        'bank' => 'benki',
        'mobile_money' => 'simu',
        'other' => 'nyingine',
        default => $currentPayment,
    };

    $currentStatus = match($currentStatus) {
        'pending' => 'inasubiri',
        'approved' => 'imeidhinishwa',
        'rejected' => 'imekataliwa',
        default => $currentStatus,
    };
@endphp

<div class="modal fade offering-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable offering-modal-dialog">
        <div class="modal-content ui-modal-card">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if(($method ?? 'POST') !== 'POST')
                    @method($method)
                @endif

                <input type="hidden" name="form_context" value="{{ $context }}">

                <div class="modal-header ui-modal-header px-4 py-3">
                    <div>
                        <h5 class="modal-title ui-modal-title mb-0">{{ $title }}</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="offerings-form-section-title">{{ db_trans('basic_information') }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('offering_type') }}</label>
                            <select name="offering_type_id" class="form-select" required>
                                <option value="">{{ db_trans('select') }}</option>
                                @foreach($offeringTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('form_context') === $context ? old('offering_type_id', $item->offering_type_id ?? null) == $type->id : ($item->offering_type_id ?? null) == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('offering_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('mass_type') }}</label>
                            <select name="mass_type_id" class="form-select">
                                <option value="">{{ db_trans('not_applicable') }}</option>
                                @foreach($massTypes as $massType)
                                    <option value="{{ $massType->id }}" @selected(old('form_context') === $context ? old('mass_type_id', $item->mass_type_id ?? null) == $massType->id : ($item->mass_type_id ?? null) == $massType->id)>
                                        {{ $massType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mass_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('collection_date') }}</label>
                            <input
                                type="date"
                                name="collection_date"
                                class="form-control"
                                value="{{ old('form_context') === $context ? old('collection_date', isset($item) && $item?->collection_date ? $item->collection_date->format('Y-m-d') : now()->format('Y-m-d')) : (isset($item) && $item?->collection_date ? $item->collection_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                required>
                            @error('collection_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-2">
                            <div class="offerings-form-section-title">{{ db_trans('collection_scope') }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('collection_scope') }}</label>
                            <select name="collection_scope" class="form-select offering-scope-select" required>
                                <option value="">{{ db_trans('select_scope') }}</option>
                                @foreach($scopes as $scope)
                                    <option value="{{ $scope }}" @selected($selectedScope === $scope)>
                                        {{ db_trans($scope) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('collection_scope')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 scope-block scope-kanda {{ $selectedScope === 'kanda' ? '' : 'd-none' }}">
                            <label class="form-label">{{ db_trans('kanda') }}</label>
                            <select name="kanda_id" class="form-select">
                                <option value="">{{ db_trans('select_kanda') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" @selected(old('form_context') === $context ? old('kanda_id', $item->kanda_id ?? null) == $kanda->id : ($item->kanda_id ?? null) == $kanda->id)>
                                        {{ $kanda->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kanda_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 scope-block scope-jumuiya {{ $selectedScope === 'jumuiya' ? '' : 'd-none' }}">
                            <label class="form-label">{{ db_trans('jumuiya') }}</label>
                            <select name="jumuiya_id" class="form-select">
                                <option value="">{{ db_trans('select_jumuiya') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" @selected(old('form_context') === $context ? old('jumuiya_id', $item->jumuiya_id ?? null) == $jumuiya->id : ($item->jumuiya_id ?? null) == $jumuiya->id)>
                                        {{ $jumuiya->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jumuiya_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-2">
                            <div class="offerings-form-section-title">{{ db_trans('financial_summary') }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('amount') }}</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="amount"
                                class="form-control"
                                value="{{ old('form_context') === $context ? old('amount', $item->amount ?? '') : ($item->amount ?? '') }}"
                                required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('payment_method') }}</label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($paymentOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($currentPayment === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('reference_no') }}</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ old('form_context') === $context ? old('reference_no', $item->reference_no ?? '') : ($item->reference_no ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipt_no') }}</label>
                            <input type="text" name="receipt_no" class="form-control" value="{{ old('form_context') === $context ? old('receipt_no', $item->receipt_no ?? '') : ($item->receipt_no ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('form_context') === $context ? old('description', $item->description ?? '') : ($item->description ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea name="notes" class="form-control" rows="4">{{ old('form_context') === $context ? old('notes', $item->notes ?? '') : ($item->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer ui-modal-footer px-4 py-3">
                    <button type="button" class="ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="ui-btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>