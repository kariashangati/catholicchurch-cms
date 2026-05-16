@extends('layouts.admin')

@section('title', db_trans('edit_member'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ db_trans('edit_member') }}</h6>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('members.update', $member) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('first_name') }}</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $member->first_name) }}" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('last_name') }}</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $member->last_name) }}" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone', $member->phone) }}" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('gender') }}</label>
                                <select name="gender" class="form-control">
                                    <option value="">{{ db_trans('select') }}</option>
                                    <option value="Male" {{ old('gender', $member->gender) === 'Male' ? 'selected' : '' }}>{{ db_trans('male') }}</option>
                                    <option value="Female" {{ old('gender', $member->gender) === 'Female' ? 'selected' : '' }}>{{ db_trans('female') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            <button class="btn btn-success px-4">
                                {{ db_trans('update_member') }}
                            </button>

                            <a href="{{ route('members.index') }}" class="btn btn-secondary px-4">
                                {{ db_trans('cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection