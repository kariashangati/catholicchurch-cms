<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if(($method ?? 'POST') !== 'POST') @method($method) @endif
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">{{ $title }}</h5>
                        <p class="text-muted mb-0">{{ db_trans('fill_the_details_below_to_save_the_offering_record') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">{{ db_trans('offering_type') }}</label><select name="offering_type_id" class="form-select" required><option value="">{{ db_trans('select_offering_type') }}</option>@foreach($offeringTypes as $type)<option value="{{ $type->id }}" @selected(old('offering_type_id', $item->offering_type_id ?? null) == $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('mass_type') }}</label><select name="mass_type_id" class="form-select"><option value="">{{ db_trans('not_applicable') }}</option>@foreach($massTypes as $massType)<option value="{{ $massType->id }}" @selected(old('mass_type_id', $item->mass_type_id ?? null) == $massType->id)>{{ $massType->name }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('collection_date') }}</label><input type="date" name="collection_date" class="form-control" value="{{ old('collection_date', isset($item) && $item?->collection_date ? $item->collection_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('collection_scope') }}</label><select name="collection_scope" class="form-select offering-scope-select" required><option value="">{{ db_trans('select_scope') }}</option>@foreach($scopes as $scope)<option value="{{ $scope }}" @selected(old('collection_scope', $item->collection_scope ?? 'parish') === $scope)>{{ db_trans($scope) }}</option>@endforeach</select></div>
                        <div class="col-md-4 scope-block scope-centre"><label class="form-label">{{ db_trans('parish') }}</label><select name="centre_detail_id" class="form-select"><option value="">{{ db_trans('select_parish') }}</option>@foreach($centres as $centre)<option value="{{ $centre->id }}" @selected(old('centre_detail_id', $item->centre_detail_id ?? null) == $centre->id)>{{ $centre->centre_name }}</option>@endforeach</select></div>
                        <div class="col-md-4 scope-block scope-kanda"><label class="form-label">{{ db_trans('kanda') }}</label><select name="kanda_id" class="form-select"><option value="">{{ db_trans('select_kanda') }}</option>@foreach($kandas as $kanda)<option value="{{ $kanda->id }}" @selected(old('kanda_id', $item->kanda_id ?? null) == $kanda->id)>{{ $kanda->name }}</option>@endforeach</select></div>
                        <div class="col-md-4 scope-block scope-jumuiya"><label class="form-label">{{ db_trans('jumuiya') }}</label><select name="jumuiya_id" class="form-select"><option value="">{{ db_trans('select_jumuiya') }}</option>@foreach($jumuiyas as $jumuiya)<option value="{{ $jumuiya->id }}" @selected(old('jumuiya_id', $item->jumuiya_id ?? null) == $jumuiya->id)>{{ $jumuiya->name }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('amount') }}</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $item->amount ?? '') }}" required></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('payment_method') }}</label><select name="payment_method" class="form-select">@foreach(['cash' => 'Cash', 'bank' => 'Bank', 'mobile_money' => 'Mobile Money', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('payment_method', $item->payment_method ?? 'cash') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('status') }}</label><select name="status" class="form-select">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $item->status ?? 'approved') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label">{{ db_trans('reference_no') }}</label><input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $item->reference_no ?? '') }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ db_trans('receipt_no') }}</label><input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no', $item->receipt_no ?? '') }}"></div>
                        <div class="col-12"><label class="form-label">{{ db_trans('description') }}</label><textarea name="description" rows="2" class="form-control">{{ old('description', $item->description ?? '') }}</textarea></div>
                        <div class="col-12"><label class="form-label">{{ db_trans('notes') }}</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $item->notes ?? '') }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button><button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button></div>
            </form>
        </div>
    </div>
</div>
