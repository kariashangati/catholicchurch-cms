@extends('layouts.admin')

@section('title', db_trans('admin_users'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/userindexx.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .access-standard-hero{background:linear-gradient(135deg,#7c3aed 0%,#8b5cf6 52%,#a855f7 100%)!important;border-radius:24px;color:#fff;padding:32px 24px;position:relative;overflow:hidden;margin-bottom:24px}.access-standard-hero:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:18px 18px;opacity:.45}.access-standard-hero>*{position:relative;z-index:1}.access-table-card{border:0;border-radius:22px;box-shadow:0 16px 36px rgba(15,23,42,.08)}
    </style>
@endpush

@section('content')
    <div class="access-page-shell">
        <div class="access-standard-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <span class="access-hero-badge"><i class="fas fa-users-cog"></i>{{ db_trans('admin_users') }}</span>
                    <h2 class="access-page-hero-title mt-3 mb-0">{{ db_trans('admin_users') }}</h2>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('system-access.dashboard') }}" class="btn btn-light"><i class="fas fa-chart-line me-2"></i>{{ db_trans('access_control_center') }}</a>
                    @can('access.roles.view')
                        <a href="{{ route('system-access.roles.index') }}" class="btn btn-outline-light"><i class="fas fa-id-badge me-2"></i>{{ db_trans('roles') }}</a>
                    @endcan
                    @can('access.permissions.view')
                        <a href="{{ route('system-access.permissions.index') }}" class="btn btn-outline-light"><i class="fas fa-key me-2"></i>{{ db_trans('permissions') }}</a>
                    @endcan
                    @can('access.users.create')
                        <a href="{{ route('system-access.users.create') }}" class="btn btn-outline-light"><i class="fas fa-user-plus me-2"></i>{{ db_trans('create_admin_user') }}</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card access-filter-card">
            <div class="card-body">
                <form method="GET" action="{{ route('system-access.users.index') }}">
                    <div class="row g-3">
                        <div class="col-xl-3 col-md-6">
                            <label class="access-filter-label">{{ db_trans('search') }}</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ db_trans('name_email_phone_member') }}">
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <label class="access-filter-label">{{ db_trans('role') }}</label>
                            <select name="role_id" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string)($filters['role_id'] ?? '') === (string)$role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <label class="access-filter-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                <option value="1" @selected((string)($filters['is_active'] ?? '') === '1')>{{ db_trans('active') }}</option>
                                <option value="0" @selected((string)($filters['is_active'] ?? '') === '0')>{{ db_trans('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <label class="access-filter-label">{{ db_trans('kanda') }}</label>
                            <select name="kanda_id" id="accessUserKanda" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" @selected((string)($filters['kanda_id'] ?? '') === (string)$kanda->id)>{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <label class="access-filter-label">{{ db_trans('jumuiya') }}</label>
                            <select name="jumuiya_id" id="accessUserJumuiya" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}" @selected((string)($filters['jumuiya_id'] ?? '') === (string)$jumuiya->id)>{{ $jumuiya->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-1 col-md-6">
                            <label class="access-filter-label">{{ db_trans('per_page') }}</label>
                            <select name="per_page" class="form-select">
                                @foreach([5,10,25,50,100,500,1000] as $size)
                                    <option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 1000) === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex align-items-end gap-2">
                            <button class="btn btn-primary px-4" type="submit"><i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}</button>
                            <a href="{{ route('system-access.users.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-rotate-left me-2"></i>{{ db_trans('reset') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card access-table-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h4 class="mb-1">{{ db_trans('admin_users') }}</h4>
                        <div class="text-muted">{{ db_trans('showing') }} {{ $users->firstItem() ?? 0 }} {{ db_trans('to') }} {{ $users->lastItem() ?? 0 }} {{ db_trans('of') }} {{ $users->total() }} {{ db_trans('users') }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table access-table align-middle" id="accessUsersTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('sn') }}</th>
                                <th>{{ db_trans('user') }}</th>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('roles') }}</th>
                                <th>{{ db_trans('scope') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('last_login') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                                    <td style="min-width: 220px;"><div class="access-cell-title">{{ $user->name }}</div><div class="access-cell-meta">{{ $user->email }}</div><div class="access-cell-meta">{{ $user->phone ?: db_trans('not_available') }}</div></td>
                                    <td style="min-width: 180px;"><div class="access-cell-title">{{ $user->member?->member_code ?: db_trans('not_available') }}</div><div class="access-cell-meta">{{ $user->member ? trim(collect([$user->member->first_name, $user->member->middle_name, $user->member->last_name])->filter()->implode(' ')) : db_trans('not_available') }}</div></td>
                                    <td style="min-width: 200px;">@forelse($user->roles as $role)<span class="access-role-chip">{{ $role->name }}</span>@empty<span class="text-muted">{{ db_trans('no_roles_assigned') }}</span>@endforelse</td>
                                    <td style="min-width: 180px;"><div class="access-cell-title">{{ $user->kanda?->name ?: db_trans('not_available') }}</div><div class="access-cell-meta">{{ $user->jumuiya?->name ?: db_trans('not_available') }}</div></td>
                                    <td><span class="access-status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">{{ $user->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                                    <td style="min-width: 170px;"><div class="access-cell-title">{{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : db_trans('never') }}</div><div class="access-cell-meta">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '-' }}</div></td>
                                    <td style="min-width: 260px;"><div class="d-flex flex-wrap gap-2">
                                        @can('access.users.profile.view')<a href="{{ route('system-access.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>{{ db_trans('view') }}</a>@endcan
                                        @can('access.users.update')<a href="{{ route('system-access.users.edit', $user->id) }}" class="btn btn-sm btn-outline-dark"><i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}</a>@endcan
                                        @if($user->is_active)
                                            @can('access.users.deactivate')<form method="POST" action="{{ route('system-access.users.deactivate', $user->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning" type="submit"><i class="fas fa-user-slash me-1"></i>{{ db_trans('deactivate') }}</button></form>@endcan
                                        @else
                                            @can('access.users.activate')<form method="POST" action="{{ route('system-access.users.activate', $user->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success" type="submit"><i class="fas fa-user-check me-1"></i>{{ db_trans('activate') }}</button></form>@endcan
                                        @endif
                                        @can('access.users.delete')<form method="POST" action="{{ route('system-access.users.destroy', $user->id) }}" onsubmit="return confirm('{{ db_trans('are_you_sure') }}');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}</button></form>@endcan
                                    </div></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">{{ db_trans('no_admin_users_found') }}</td></tr>
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
            const kanda = document.getElementById('accessUserKanda');
            const jumuiya = document.getElementById('accessUserJumuiya');
            const syncJumuiya = function () {
                if (!kanda || !jumuiya) return;
                Array.from(jumuiya.options).forEach(function (option) {
                    if (!option.value) { option.hidden = false; return; }
                    option.hidden = !!kanda.value && option.dataset.kanda !== kanda.value;
                });
                const selected = jumuiya.selectedOptions[0];
                if (selected && selected.hidden) jumuiya.value = '';
            };
            syncJumuiya();
            if (kanda) kanda.addEventListener('change', syncJumuiya);

            if (window.jQuery && $.fn.DataTable && $('#accessUsersTable').length) {
                $('#accessUsersTable').DataTable({
                    paging: true, info: true, searching: true, responsive: true, pageLength: 10,
                    lengthMenu: [[5,10,25,50,100,-1],[5,10,25,50,100,@json(db_trans('all'))]],
                    order: [], autoWidth: false,
                    language: { search: @json(db_trans('search')) + ':', lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')), info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')), infoEmpty: @json(db_trans('no_records_found')), zeroRecords: @json(db_trans('no_records_found')), paginate: { previous: '‹', next: '›' } }
                });
            }
        });
    </script>
@endpush
