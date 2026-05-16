<form method="GET" class="card dashboard-panel mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">{{ db_trans('year') }}</label>
                <select name="year" class="form-select">
                    @for($yr = now()->year; $yr >= 2020; $yr--)
                        <option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>{{ $yr }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ db_trans('month') }}</label>
                <select name="month" class="form-select">
                    <option value="">{{ db_trans('all_months') }}</option>
                    @foreach(range(1, 12) as $monthNumber)
                        <option value="{{ $monthNumber }}" @selected(($filters['month'] ?? '') == $monthNumber)>
                            {{ \Carbon\Carbon::create()->month($monthNumber)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ db_trans('offering_type') }}</label>
                <select name="offering_type_id" class="form-select">
                    <option value="">{{ db_trans('all_types') }}</option>
                    @foreach($offeringTypes ?? [] as $type)
                        <option value="{{ $type->id }}" @selected(($filters['offering_type_id'] ?? '') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ db_trans('mass_type') }}</label>
                <select name="mass_type_id" class="form-select">
                    <option value="">{{ db_trans('all_mass_types') }}</option>
                    @foreach($massTypes ?? [] as $massType)
                        <option value="{{ $massType->id }}" @selected(($filters['mass_type_id'] ?? '') == $massType->id)>
                            {{ $massType->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ db_trans('collection_scope') }}</label>
                <select name="collection_scope" class="form-select">
                    <option value="">{{ db_trans('all_scopes') }}</option>
                    @foreach($scopes ?? [] as $scope)
                        <option value="{{ $scope }}" @selected(($filters['collection_scope'] ?? '') === $scope)>
                            {{ db_trans($scope) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ db_trans('kanda') }}</label>
                <select name="kanda_id" class="form-select">
                    <option value="">{{ db_trans('all_kandas') }}</option>
                    @foreach($kandas ?? [] as $kanda)
                        <option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? '') == $kanda->id)>
                            {{ $kanda->name ?? $kanda->jina_la_kanda ?? 'Kanda' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ db_trans('jumuiya') }}</label>
                <select name="jumuiya_id" class="form-select">
                    <option value="">{{ db_trans('all_jumuiyas') }}</option>
                    @foreach($jumuiyas ?? [] as $jumuiya)
                        <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>
                            {{ $jumuiya->name ?? $jumuiya->jina_la_jumuiya ?? 'Jumuiya' }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(!empty($showStatus))
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ db_trans('all_statuses') }}</option>
                        @foreach($statuses ?? [] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>{{ db_trans('filter_records') }}
                </button>
            </div>
        </div>
    </div>
</form>