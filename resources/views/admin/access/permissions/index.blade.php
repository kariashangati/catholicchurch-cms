@extends('layouts.admin')

@section('title', db_trans('permissions'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .access-standard-hero{background:linear-gradient(135deg,#7c3aed 0%,#8b5cf6 52%,#a855f7 100%)!important;border-radius:24px;color:#fff;padding:32px 24px;position:relative;overflow:hidden;margin-bottom:24px}.access-standard-hero:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:18px 18px;opacity:.45}.access-standard-hero>*{position:relative;z-index:1}.access-table-card{border:0;border-radius:22px;box-shadow:0 16px 36px rgba(15,23,42,.08)}
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column gap-4">
        <div class="access-standard-hero">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="access-hero-badge"><i class="fas fa-key"></i>{{ db_trans('permissions') }}</span>
                    <h2 class="mt-3 mb-0">{{ db_trans('permissions') }}</h2>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @can('access.users.view')
                        <a href="{{ route('system-access.users.index') }}" class="btn btn-outline-light"><i class="fas fa-users-cog me-2"></i>{{ db_trans('admin_users') }}</a>
                    @endcan
                    @can('access.roles.view')
                        <a href="{{ route('system-access.roles.index') }}" class="btn btn-outline-light"><i class="fas fa-id-badge me-2"></i>{{ db_trans('roles') }}</a>
                    @endcan
                    @can('access.permissions.create')
                        <a href="{{ route('system-access.permissions.create') }}" class="btn btn-light"><i class="fas fa-plus me-2"></i>{{ db_trans('create_permission') }}</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('system-access.permissions.index') }}" class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">{{ db_trans('search') }}</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ db_trans('permission_name_or_description') }}">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">{{ db_trans('module') }}</label>
                        <select name="module" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ ucfirst($module) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="is_active" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            <option value="1" @selected((string)($filters['is_active'] ?? '') === '1')>{{ db_trans('active') }}</option>
                            <option value="0" @selected((string)($filters['is_active'] ?? '') === '0')>{{ db_trans('inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-1">
                        <label class="form-label">{{ db_trans('per_page') }}</label>
                        <select name="per_page" class="form-select">
                            @foreach([5,10,25,50,100,500,1000] as $size)
                                <option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 1000) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i>{{ db_trans('apply_filters') }}</button>
                        <a class="btn btn-outline-secondary" href="{{ route('system-access.permissions.index') }}">{{ db_trans('reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card access-table-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle" id="accessPermissionsTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('sn') }}</th>
                                <th>{{ db_trans('permission') }}</th>
                                <th>{{ db_trans('module') }}</th>
                                <th>{{ db_trans('guard') }}</th>
                                <th>{{ db_trans('roles') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ ($permissions->firstItem() ?? 1) + $loop->index }}</td>
                                    <td><div class="fw-semibold">{{ $permission->name }}</div><div class="small text-muted">{{ $permission->description ?: '-' }}</div></td>
                                    <td>{{ ucfirst($permission->module ?: 'general') }}</td>
                                    <td>{{ $permission->guard_name }}</td>
                                    <td>{{ number_format($permission->roles_count) }}</td>
                                    <td><span class="badge {{ $permission->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $permission->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                                    <td><div class="d-flex flex-wrap gap-2">
                                        @can('access.permissions.update')<a href="{{ route('system-access.permissions.edit', $permission->id) }}" class="btn btn-sm btn-outline-dark">{{ db_trans('edit') }}</a>@endcan
                                        @can('access.permissions.toggle')<form method="POST" action="{{ route('system-access.permissions.toggle', $permission->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $permission->is_active ? 'warning' : 'success' }}" type="submit">{{ $permission->is_active ? db_trans('disable') : db_trans('enable') }}</button></form>@endcan
                                        @can('access.permissions.delete')<form method="POST" action="{{ route('system-access.permissions.destroy', $permission->id) }}" onsubmit="return confirm('{{ db_trans('are_you_sure') }}');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">{{ db_trans('delete') }}</button></form>@endcan
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">{{ db_trans('no_permissions_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/access-control.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && $.fn.DataTable && $('#accessPermissionsTable').length) {
                $('#accessPermissionsTable').DataTable({
                    paging: true, info: true, searching: true, responsive: true, pageLength: 10,
                    lengthMenu: [[5,10,25,50,100,-1],[5,10,25,50,100,@json(db_trans('all'))]],
                    order: [], autoWidth: false,
                    language: { search: @json(db_trans('search')) + ':', lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')), info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')), infoEmpty: @json(db_trans('no_records_found')), zeroRecords: @json(db_trans('no_records_found')), paginate: { previous: '‹', next: '›' } }
                });
            }
        });
    </script>
@endpush
