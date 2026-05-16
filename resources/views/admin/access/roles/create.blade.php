@extends('layouts.admin')

@section('title', db_trans('create_role'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
@endpush
@section('content')
    <div class="d-flex flex-column gap-4">
        <div class="p-4 rounded-4 text-white" style="background:linear-gradient(135deg,#111827 0%,#1f2937 35%,#4338ca 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2">{{ db_trans('create_role') }}</h2>
                    <p class="mb-0 text-white-50">{{ db_trans('create_role_and_assign_permissions') }}</p>
                </div>
                <a href="{{ route('system-access.roles.index') }}" class="btn btn-light">{{ db_trans('back_to_roles') }}</a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-access.roles.store') }}">
            @csrf

            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('role_name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('guard') }}</label>
                            <input type="text" name="guard_name" value="{{ old('guard_name', 'web') }}" class="form-control @error('guard_name') is-invalid @enderror" required>
                            @error('guard_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 rounded-4 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">{{ db_trans('assign_permissions') }}</h5>

                    @foreach($permissions->groupBy('module') as $module => $items)
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary">{{ ucfirst($module ?: 'general') }}</h6>
                            <div class="row g-3 mt-1">
                                @foreach($items as $permission)
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <div class="form-check border rounded-3 p-3">
                                            <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                                   id="perm_create_{{ $permission->id }}" @checked(collect(old('permission_ids', []))->contains($permission->id))>
                                            <label class="form-check-label fw-semibold" for="perm_create_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                            @if($permission->description)
                                                <div class="small text-muted mt-1">{{ $permission->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary px-4" type="submit">{{ db_trans('create_role') }}</button>
                <a href="{{ route('system-access.roles.index') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/access-control.js') }}"></script>
@endpush