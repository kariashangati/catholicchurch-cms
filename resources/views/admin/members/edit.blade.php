@extends('layouts.admin')

@section('title', db_trans('edit_member'))

@section('content')
    <div class="dashboard-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="dashboard-hero-badge">{{ db_trans('edit_member') }}</span>
                <h2 class="dashboard-title mb-2">{{ $member->full_name }}</h2>
                <p class="dashboard-subtitle mb-0">
                    {{ db_trans('update_member_information') }}
                </p>
            </div>

            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <a href="{{ route('members.show', $member) }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-eye me-2"></i>{{ db_trans('view') }}
                    </a>
                    <a href="{{ route('members.index') }}" class="btn btn-primary btn-lg admin-main-btn">
                        <i class="fas fa-arrow-left me-2"></i>{{ db_trans('back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('members.update', $member) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card dashboard-panel">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h5 class="fw-bold mb-0">{{ db_trans('personal_information') }}</h5>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ db_trans('first_name') }}</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $member->first_name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ db_trans('middle_name') }}</label>
                                <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $member->middle_name) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ db_trans('last_name') }}</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $member->last_name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ db_trans('phone') }}</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ db_trans('gender') }}</label>
                                <select name="gender" class="form-select">
                                    <option value="">{{ db_trans('select_gender') }}</option>
                                    <option value="Male" @selected(old('gender', $member->gender) === 'Male')>{{ db_trans('male') }}</option>
                                    <option value="Female" @selected(old('gender', $member->gender) === 'Female')>{{ db_trans('female') }}</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ db_trans('date_of_birth') }}</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                       value="{{ old('date_of_birth', optional($member->date_of_birth)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('occupation') }}</label>
                                <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $member->occupation) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('family_role') }}</label>
                                <select name="family_role" class="form-select">
                                    <option value="">{{ db_trans('select_family_role') }}</option>
                                    <option value="Father" @selected(old('family_role', $member->family_role) === 'Father')>{{ db_trans('father') }}</option>
                                    <option value="Mother" @selected(old('family_role', $member->family_role) === 'Mother')>{{ db_trans('mother') }}</option>
                                    <option value="Child" @selected(old('family_role', $member->family_role) === 'Child')>{{ db_trans('child') }}</option>
                                    <option value="Other" @selected(old('family_role', $member->family_role) === 'Other')>{{ db_trans('other') }}</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('familia') }}</label>
                                <select name="familia_id" class="form-select" required>
                                    <option value="">{{ db_trans('select_familia') }}</option>
                                    @foreach($familias as $familia)
                                        <option value="{{ $familia->id }}"
                                            @selected((string) old('familia_id', $member->familia_id) === (string) $familia->id)>
                                            {{ $familia->name }} — {{ $familia->jumuiya?->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('bahasha') }}</label>
                                <input type="text" name="bahasha" class="form-control" value="{{ old('bahasha', $member->bahasha) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ db_trans('notes') }}</label>
                                <textarea name="notes" rows="4" class="form-control">{{ old('notes', $member->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card dashboard-panel mt-4">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h5 class="fw-bold mb-0">{{ db_trans('sacraments') }}</h5>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_baptized" value="1" id="is_baptized"
                                           @checked(old('is_baptized', $member->is_baptized))>
                                    <label class="form-check-label" for="is_baptized">{{ db_trans('baptized') }}</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="has_communion" value="1" id="has_communion"
                                           @checked(old('has_communion', $member->has_communion))>
                                    <label class="form-check-label" for="has_communion">{{ db_trans('communion') }}</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="has_confirmation" value="1" id="has_confirmation"
                                           @checked(old('has_confirmation', $member->has_confirmation))>
                                    <label class="form-check-label" for="has_confirmation">{{ db_trans('confirmation') }}</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="receives_eucharist" value="1" id="receives_eucharist"
                                           @checked(old('receives_eucharist', $member->receives_eucharist))>
                                    <label class="form-check-label" for="receives_eucharist">{{ db_trans('eucharist') }}</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_married" value="1" id="is_married"
                                           @checked(old('is_married', $member->is_married))>
                                    <label class="form-check-label" for="is_married">{{ db_trans('married') }}</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                           @checked(old('is_active', $member->is_active))>
                                    <label class="form-check-label" for="is_active">{{ db_trans('active') }}</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('marriage_type') }}</label>
                                <input type="text" name="marriage_type" class="form-control" value="{{ old('marriage_type', $member->marriage_type) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('member_code') }}</label>
                                <input type="text" name="member_code" class="form-control" value="{{ old('member_code', $member->member_code) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('baptism_certificate_number') }}</label>
                                <input type="text" name="baptism_certificate_number" class="form-control" value="{{ old('baptism_certificate_number', $member->baptism_certificate_number) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('marriage_certificate_number') }}</label>
                                <input type="text" name="marriage_certificate_number" class="form-control" value="{{ old('marriage_certificate_number', $member->marriage_certificate_number) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('baptism_parish') }}</label>
                                <input type="text" name="baptism_parish" class="form-control" value="{{ old('baptism_parish', $member->baptism_parish) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ db_trans('baptism_diocese') }}</label>
                                <input type="text" name="baptism_diocese" class="form-control" value="{{ old('baptism_diocese', $member->baptism_diocese) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-panel">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h5 class="fw-bold mb-0">{{ db_trans('save_changes') }}</h5>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <div class="dashboard-note-box mb-4">
                            <div class="fw-bold mb-1">{{ db_trans('important') }}</div>
                            <div class="text-muted small">
                                {{ db_trans('review_member_information_before_saving') }}
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                            </button>

                            <a href="{{ route('members.show', $member) }}" class="btn btn-light">
                                {{ db_trans('cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection