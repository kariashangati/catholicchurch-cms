@extends('layouts.admin')

@section('title', db_trans('add_member'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ db_trans('add_member') }}</h6>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('members.store') }}">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('first_name') }}</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('last_name') }}</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('phone') }}</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold">{{ db_trans('gender') }}</label>
                                <select name="gender" class="form-control">
                                    <option value="">{{ db_trans('select') }}</option>
                                    <option value="Male">{{ db_trans('male') }}</option>
                                    <option value="Female">{{ db_trans('female') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            <button class="btn btn-primary px-4">
                                {{ db_trans('save_member') }}
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