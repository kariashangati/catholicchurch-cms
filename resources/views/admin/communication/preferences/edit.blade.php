@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ db_trans('communication.preferences.edit_title') }}</h1>
        <a href="{{ route('admin.communication.preferences.index') }}" class="btn btn-outline-secondary">
            {{ db_trans('communication.common.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.communication.preferences.update', $member) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ db_trans('communication.preferences.member') }}</label>
                        <input type="text" class="form-control" value="{{ $member->name ?? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}" disabled>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ db_trans('communication.preferences.preferred_locale') }}</label>
                        <select name="preferred_locale" class="form-select">
                            <option value="en" @selected(($preference->preferred_locale ?? app()->getLocale()) === 'en')>EN</option>
                            <option value="sw" @selected(($preference->preferred_locale ?? app()->getLocale()) === 'sw')>SW</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ db_trans('communication.preferences.preferred_phone') }}</label>
                        <input type="text" name="preferred_phone" class="form-control" value="{{ old('preferred_phone', $preference->preferred_phone) }}">
                    </div>

                    <div class="col-md-3"><div class="form-check mt-4 pt-2"><input class="form-check-input" type="checkbox" name="allow_sms" value="1" @checked($preference->allow_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check mt-4 pt-2"><input class="form-check-input" type="checkbox" name="allow_general_sms" value="1" @checked($preference->allow_general_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_general_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check mt-4 pt-2"><input class="form-check-input" type="checkbox" name="allow_finance_sms" value="1" @checked($preference->allow_finance_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_finance_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check mt-4 pt-2"><input class="form-check-input" type="checkbox" name="allow_reminder_sms" value="1" @checked($preference->allow_reminder_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_reminder_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="allow_announcement_sms" value="1" @checked($preference->allow_announcement_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_announcement_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="allow_automated_sms" value="1" @checked($preference->allow_automated_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_automated_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="allow_manual_sms" value="1" @checked($preference->allow_manual_sms)><label class="form-check-label">{{ db_trans('communication.preferences.allow_manual_sms') }}</label></div></div>
                    <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_phone_verified" value="1" @checked($preference->is_phone_verified)><label class="form-check-label">{{ db_trans('communication.preferences.is_phone_verified') }}</label></div></div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('communication.preferences.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $preference->notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ db_trans('communication.common.save') }}</button>
                </div>
            </form>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.communication.preferences.opt-out', $member) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">{{ db_trans('communication.preferences.opt_out') }}</button>
                </form>

                <form method="POST" action="{{ route('admin.communication.preferences.opt-in', $member) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-success">{{ db_trans('communication.preferences.opt_in') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
