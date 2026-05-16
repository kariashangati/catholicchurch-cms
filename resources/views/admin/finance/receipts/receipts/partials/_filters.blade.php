<div class="receipt-filter-grid">

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.receipt_type') }}</label>
        <select name="receipt_type" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($supportedTypes ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('receipt_type') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.receipt_layout') }}</label>
        <select name="receipt_layout" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($supportedLayouts ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('receipt_layout') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.source_type') }}</label>
        <select name="source_type" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($filters['source_types'] ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('source_type') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.period') }}</label>
        <input type="month" name="period" class="form-control" value="{{ old('period') }}">
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.member') }}</label>
        <select name="member_id" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($filters['members'] ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('member_id') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.familia') }}</label>
        <select name="familia_id" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($filters['familias'] ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('familia_id') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.jumuiya') }}</label>
        <select name="jumuiya_id" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($filters['jumuiyas'] ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('jumuiya_id') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.kanda') }}</label>
        <select name="kanda_id" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($filters['kandas'] ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('kanda_id') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="receipt-field">
        <label class="form-label">{{ db_trans('receipts.fields.contribution_type') }}</label>
        <select name="contribution_type_id" class="form-select">
            <option value="">{{ db_trans('common.select_option') }}</option>
            @foreach(($filters['contribution_types'] ?? []) as $value => $label)
                <option value="{{ $value }}" @selected(old('contribution_type_id') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

</div>