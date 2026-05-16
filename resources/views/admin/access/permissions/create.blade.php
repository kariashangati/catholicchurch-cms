@extends('layouts.admin')

@section('title', db_trans('create_permission'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
@endpush
@section('content')
    <div class="d-flex flex-column gap-4">
        <div class="p-4 rounded-4 text-white" style="background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 45%,#0ea5e9 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2">{{ db_trans('create_permission') }}</h2>
                    <p class="mb-0 text-white-50">{{ db_trans('create_new_system_permission') }}</p>
                </div>
                <a href="{{ route('system-access.permissions.index') }}" class="btn btn-light">{{ db_trans('back_to_permissions') }}</a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-access.permissions.store') }}">
            @csrf

            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('permission_name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('guard') }}</label>
                            <input type="text" name="guard_name" value="{{ old('guard_name', 'web') }}" class="form-control @error('guard_name') is-invalid @enderror" required>
                            @error('guard_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('module') }}</label>
                            <input type="text" name="module" value="{{ old('module') }}" class="form-control @error('module') is-invalid @enderror">
                            @error('module')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <input type="text" name="description" value="{{ old('description') }}" class="form-control @error('description') is-invalid @enderror">
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" @selected((string)old('is_active', '1') === '1')>{{ db_trans('active') }}</option>
                                <option value="0" @selected((string)old('is_active') === '0')>{{ db_trans('inactive') }}</option>
                            </select>
                            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary px-4" type="submit">{{ db_trans('create_permission') }}</button>
                <a href="{{ route('system-access.permissions.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/access-control.js') }}"></script>
@endpush