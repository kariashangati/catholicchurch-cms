<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 hb-modal">
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @if($hall) @method('PUT') @endif
                <div class="modal-header"><h5 class="modal-title">{{ $title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">{{ db_trans('name') }}</label><input name="name" class="form-control" required value="{{ old('name', $hall?->name) }}"></div>
                        <div class="col-md-3"><label class="form-label">{{ db_trans('capacity') }}</label><input type="number" name="capacity" class="form-control" value="{{ old('capacity', $hall?->capacity) }}"></div>
                        <div class="col-md-3"><label class="form-label">{{ db_trans('default_price') }}</label><input type="number" step="0.01" name="default_price" class="form-control" required value="{{ old('default_price', $hall?->default_price ?? 0) }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ db_trans('location') }}</label><input name="location" class="form-control" value="{{ old('location', $hall?->location) }}"></div>
                        <div class="col-md-6"><label class="form-label">{{ db_trans('hall_pictures') }}</label><input type="file" name="images[]" multiple class="form-control" accept="image/*"></div>
                        <div class="col-12"><label class="form-label">{{ db_trans('description') }}</label><textarea name="description" class="form-control" rows="2">{{ old('description', $hall?->description) }}</textarea></div>
                        <div class="col-12"><label class="form-label">{{ db_trans('conditions') }}</label><textarea name="conditions" class="form-control" rows="3">{{ old('conditions', $hall?->conditions) }}</textarea></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('bank_name') }}</label><input name="bank_name" class="form-control" value="{{ old('bank_name', $hall?->bank_name) }}"></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('bank_account_name') }}</label><input name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $hall?->bank_account_name) }}"></div>
                        <div class="col-md-4"><label class="form-label">{{ db_trans('bank_account_number') }}</label><input name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $hall?->bank_account_number) }}"></div>
                        <div class="col-12"><label class="form-label">{{ db_trans('payment_instructions') }}</label><textarea name="payment_instructions" class="form-control" rows="2">{{ old('payment_instructions', $hall?->payment_instructions) }}</textarea></div>
                        <div class="col-12"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $hall?->is_active ?? true))> {{ db_trans('active') }}</label></div>
                        @if($hall?->images?->count())
                            <div class="col-12"><div class="hb-image-grid">
                                @foreach($hall->images as $image)
                                    <div class="hb-image-thumb"><img src="{{ $image->url }}" alt="{{ $hall->name }}"></div>
                                @endforeach
                            </div></div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('close') }}</button><button class="btn btn-primary">{{ db_trans('save') }}</button></div>
            </form>
        </div>
    </div>
</div>
