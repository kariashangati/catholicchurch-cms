<form method="GET" class="card tithe-panel mb-4">
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
            <div class="col-md-2">
                <label class="form-label">{{ db_trans('status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ db_trans('all_statuses') }}</option>
                    @foreach($statuses ?? [] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ db_trans('payment_method') }}</label>
                <select name="payment_method" class="form-select">
                    <option value="">{{ db_trans('all_methods') }}</option>
                    @foreach($paymentMethods ?? [] as $method)
                        <option value="{{ $method }}" @selected(($filters['payment_method'] ?? '') === $method)>{{ ucfirst($method) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ db_trans('jumuiya') }}</label>
                <select name="jumuiya_id" class="form-select">
                    <option value="">{{ db_trans('all_jumuiyas') }}</option>
                    @foreach($jumuiyas ?? [] as $jumuiya)
                        <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>{{ $jumuiya->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(!empty($showMembers))
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('member') }}</label>
                    <select name="member_id" class="form-select">
                        <option value="">{{ db_trans('all_members') }}</option>
                        @foreach($members ?? [] as $member)
                            <option value="{{ $member->id }}" @selected(($filters['member_id'] ?? '') == $member->id)>
                                {{ $member->full_name }}{{ $member->member_code ? ' - '.$member->member_code : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>{{ db_trans('filter_tithes') }}</button>
            </div>
        </div>
    </div>
</form>
