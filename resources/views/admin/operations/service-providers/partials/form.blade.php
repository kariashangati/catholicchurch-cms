<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('category') }}</label>
        <select name="service_category_id" class="form-select" required>
            <option value="">{{ db_trans('select_option') }}</option>
            @foreach($categories as $categoryOption)
                <option value="{{ $categoryOption->id }}" @selected(old('service_category_id', $provider->service_category_id ?? '') == $categoryOption->id)>{{ $categoryOption->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('linked_member_optional') }}</label>
        <select name="member_id" class="form-select">
            <option value="">{{ db_trans('none') }}</option>
            @foreach($members as $member)
                @php($memberName = trim(($member->first_name ?? '') . ' ' . ($member->middle_name ?? '') . ' ' . ($member->last_name ?? '')))
                <option value="{{ $member->id }}" @selected(old('member_id', $provider->member_id ?? '') == $member->id)>{{ $memberName ?: ($member->name ?? ('#' . $member->id)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $provider->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('phone') }}</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $provider->phone ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('email') }}</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $provider->email ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('status') }}</label>
        <select name="status" class="form-select" required>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $provider->status ?? \App\Models\ServiceProvider::STATUS_ACTIVE) === $status)>{{ db_trans($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">{{ db_trans('address') }}</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $provider->address ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">{{ db_trans('notes') }}</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $provider->notes ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="{{ ($provider->id ?? 'create') }}_provider_internal" @checked(old('is_internal', $provider->is_internal ?? false))>
            <label class="form-check-label" for="{{ ($provider->id ?? 'create') }}_provider_internal">{{ db_trans('internal') }}</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ ($provider->id ?? 'create') }}_provider_active" @checked(old('is_active', $provider->is_active ?? true))>
            <label class="form-check-label" for="{{ ($provider->id ?? 'create') }}_provider_active">{{ db_trans('active') }}</label>
        </div>
    </div>
</div>
