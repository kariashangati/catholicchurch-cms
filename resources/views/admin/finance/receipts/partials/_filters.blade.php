<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ $action ?? url()->current() }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ db_trans('receipts.filters.search') }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ db_trans('receipts.filters.search_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('receipts.filters.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ db_trans('common.all') }}</option>
                        @foreach(($statuses ?? []) as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('receipts.filters.type') }}</label>
                    <select name="receipt_type" class="form-select">
                        <option value="">{{ db_trans('common.all') }}</option>
                        @foreach(($receiptTypes ?? []) as $value => $label)
                            <option value="{{ $value }}" @selected(request('receipt_type') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('receipts.filters.from_date') }}</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('receipts.filters.to_date') }}</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>
