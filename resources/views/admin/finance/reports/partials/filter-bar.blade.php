<div class="card ui-filter-card border-0 mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-2 col-md-3">
                <label class="form-label">{{ db_trans('year') }}</label>
                <input type="number" name="year" class="form-control" value="{{ $filters['year'] ?? now()->year }}">
            </div>
            @isset($showMonth)
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select">
                        <option value="">{{ db_trans('all_months') }}</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" @selected(($filters['month'] ?? null) == $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
            @endisset
            @isset($types)
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">{{ db_trans('contribution_type') }}</label>
                    <select name="contribution_type_id" class="form-select">
                        <option value="">{{ db_trans('all_contribution_types') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(($filters['contribution_type_id'] ?? null) == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endisset
            @isset($kandas)
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" class="form-select">
                        <option value="">{{ db_trans('all_kandas') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? null) == $kanda->id)>{{ $kanda->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endisset
            @isset($jumuiyas)
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" class="form-select">
                        <option value="">{{ db_trans('all_jumuiyas') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? null) == $jumuiya->id)>{{ $jumuiya->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endisset
            @isset($showComplianceFilters)
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">{{ db_trans('payment_status') }}</label>
                    <select name="payment_status" class="form-select">
                        <option value="all" @selected(($filters['payment_status'] ?? 'all') === 'all')>{{ db_trans('all') }}</option>
                        <option value="paid" @selected(($filters['payment_status'] ?? null) === 'paid')>{{ db_trans('paid') }}</option>
                        <option value="partial" @selected(($filters['payment_status'] ?? null) === 'partial')>{{ db_trans('partial') }}</option>
                        <option value="unpaid" @selected(($filters['payment_status'] ?? null) === 'unpaid')>{{ db_trans('unpaid') }}</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">{{ db_trans('source') }}</label>
                    <select name="source" class="form-select">
                        <option value="all" @selected(($filters['source'] ?? 'all') === 'all')>{{ db_trans('all') }}</option>
                        <option value="cash" @selected(($filters['source'] ?? null) === 'cash')>{{ db_trans('cash') }}</option>
                        <option value="bank" @selected(($filters['source'] ?? null) === 'bank')>{{ db_trans('bank') }}</option>
                        <option value="mixed" @selected(($filters['source'] ?? null) === 'mixed')>{{ db_trans('mixed') }}</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">{{ db_trans('search_member') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ db_trans('member_name_or_code') }}">
                </div>
            @endisset
            <div class="col-lg-2 col-md-3">
                <button class="btn ui-btn-primary w-100">{{ db_trans('filter_records') }}</button>
            </div>
        </form>
    </div>
</div>
