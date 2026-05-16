<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if(($method ?? 'POST') !== 'POST') @method($method) @endif
                <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold">{{ $title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="row g-3">
                    <div class="col-md-6"><label class="form-label">{{ db_trans('cash_contributions') }}</label><select name="contribution_type_id" class="form-select" required>@foreach($contributionTypes as $type)<option value="{{ $type->id }}" @selected(($item->contribution_type_id ?? null) == $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">{{ db_trans('member_optional') }}</label><select name="member_id" class="form-select"><option value="">—</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected(($item->member_id ?? null) == $member->id)>{{ $member->full_name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('amount') }}</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $item->amount ?? '') }}" required></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('contribution_date') }}</label><input type="date" name="contribution_date" class="form-control" value="{{ old('contribution_date', isset($item) ? optional($item->contribution_date)->toDateString() : now()->toDateString()) }}" required></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('group_name') }}</label><input type="text" name="group_name" class="form-control" value="{{ old('group_name', $item->group_name ?? '') }}"></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('payment_method') }}</label><select name="payment_method" class="form-select">@foreach(['cash'=>'Cash','bank'=>'Bank','mobile_money'=>'Mobile Money','other'=>'Other'] as $value=>$label)<option value="{{ $value }}" @selected(old('payment_method', $item->payment_method ?? 'cash') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('reference_no') }}</label><input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $item->reference_no ?? '') }}"></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('receipt_no') }}</label><input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no', $item->receipt_no ?? '') }}"></div>
                    <div class="col-md-4"><label class="form-label">{{ db_trans('status') }}</label><select name="status" class="form-select">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $item->status ?? 'approved') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">{{ db_trans('notes') }}</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $item->notes ?? '') }}</textarea></div>
                </div></div>
                <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button><button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button></div>
            </form>
        </div>
    </div>
</div>
