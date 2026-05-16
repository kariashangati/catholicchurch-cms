@extends('layouts.admin')

@section('title', db_trans('access_control_center'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/accessdasboard.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .access-standard-hero{background:linear-gradient(135deg,#7c3aed 0%,#8b5cf6 52%,#a855f7 100%)!important;border-radius:24px;color:#fff;padding:32px 24px;position:relative;overflow:hidden;margin-bottom:24px}.access-standard-hero:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:18px 18px;opacity:.45}.access-standard-hero>*{position:relative;z-index:1}.access-hero-actions{display:grid;gap:12px;grid-template-columns:repeat(2,minmax(0,1fr))}.access-hero-action{display:flex;align-items:center;gap:10px;padding:14px 16px;border:1px solid rgba(255,255,255,.25);border-radius:16px;color:#fff;text-decoration:none;background:rgba(255,255,255,.12)}.access-hero-action:hover{color:#fff;background:rgba(255,255,255,.2)}.access-kpi-card-link{text-decoration:none;color:inherit;display:block}.access-kpi-card-link:hover .access-kpi-card{transform:translateY(-2px);box-shadow:0 18px 38px rgba(15,23,42,.12)}.access-chart-shell{height:300px;position:relative}.access-table-card{border:0;border-radius:22px;box-shadow:0 16px 36px rgba(15,23,42,.08)}@media(max-width:991.98px){.access-hero-actions{grid-template-columns:1fr}.access-chart-shell{height:260px}}
    </style>
@endpush

@section('content')
    <div class="access-shell">
        <div class="access-standard-hero">
              <link rel="stylesheet" href="{{ asset('admin/css/access-standard-hero-fix.css') }}">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <span class="access-hero-badge">
                        <i class="fas fa-user-lock"></i>
                        {{ db_trans('system') }}
                    </span>
                    <h2 class="access-hero-title mt-3 mb-0">{{ $page['title'] ?? db_trans('access_control_center') }}</h2>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="access-meta-pill"><i class="fas fa-clock"></i>{{ db_trans('last_updated') }}: {{ optional($page['updated_at'] ?? now())->format('d M Y, h:i A') }}</span>
                        <span class="access-meta-pill"><i class="fas fa-user-shield"></i>{{ db_trans('logged_in_as') }}: {{ auth()->user()->name }}</span>
                    </div>
                </div>
                <div class="access-hero-actions">
                    @can('access.users.view')
                        <a href="{{ route('system-access.users.index') }}" class="access-hero-action"><i class="fas fa-users-cog"></i><span>{{ db_trans('admin_users') }}</span></a>
                    @endcan
                    @can('access.roles.view')
                        <a href="{{ route('system-access.roles.index') }}" class="access-hero-action"><i class="fas fa-id-badge"></i><span>{{ db_trans('roles') }}</span></a>
                    @endcan
                    @can('access.permissions.view')
                        <a href="{{ route('system-access.permissions.index') }}" class="access-hero-action"><i class="fas fa-key"></i><span>{{ db_trans('permissions') }}</span></a>
                    @endcan
                    @can('access.users.create')
                        <a href="{{ route('system-access.users.create') }}" class="access-hero-action"><i class="fas fa-user-plus"></i><span>{{ db_trans('create_admin_user') }}</span></a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($kpis as $card)
                <div class="col-xxl-2 col-xl-4 col-md-6">
                    <a href="{{ $card['url'] ?? '#' }}" class="access-kpi-card-link">
                        <div class="card access-kpi-card h-100">
                            <div class="card-body">
                                <div class="access-kpi-top">
                                    <span class="access-kpi-icon {{ $card['tone'] ? 'tone-'.$card['tone'] : 'tone-primary' }}"><i class="{{ $card['icon'] }}"></i></span>
                                    <span class="access-kpi-chip">{{ db_trans('summary') }}</span>
                                </div>
                                <div class="access-kpi-title">{{ $card['title'] }}</div>
                                <div class="access-kpi-value">{{ number_format((int) $card['value']) }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mt-1">
            <div class="col-xl-8">
                <div class="card access-panel h-100">
                    <div class="card-body">
                        <div class="access-panel-head">
                            <div><h4 class="access-panel-title">{{ db_trans('recent_roles') }}</h4></div>
                            <span class="access-badge">{{ db_trans('roles') }}</span>
                        </div>
                        <div class="access-chart-shell"><canvas id="accessRoleChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card access-panel h-100">
                    <div class="card-body">
                        <div class="access-panel-head">
                            <div><h4 class="access-panel-title">{{ db_trans('permission_modules') }}</h4></div>
                            <span class="access-badge">{{ db_trans('permissions') }}</span>
                        </div>
                        <div class="access-chart-shell"><canvas id="accessPermissionModuleChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card access-table-card mt-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h4 class="mb-0">{{ db_trans('recent_admin_users') }}</h4>
                    @can('access.users.view')
                        <a href="{{ route('system-access.users.index') }}" class="btn btn-sm btn-outline-primary">{{ db_trans('view_all') }}</a>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table align-middle" id="accessRecentUsersTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('sn') }}</th>
                                <th>{{ db_trans('user') }}</th>
                                <th>{{ db_trans('roles') }}</th>
                                <th>{{ db_trans('scope') }}</th>
                                <th>{{ db_trans('status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $user->name }}</strong><div class="small text-muted">{{ $user->email }}</div></td>
                                    <td>
                                        @forelse($user->roles as $role)
                                            <span class="access-chip">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted">{{ db_trans('no_roles_assigned') }}</span>
                                        @endforelse
                                    </td>
                                    <td>{{ $user->display_scope ?? ($user->jumuiya?->name ?: ($user->kanda?->name ?: db_trans('global'))) }}</td>
                                    <td><span class="access-status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">{{ $user->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">{{ db_trans('no_admin_users_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 mt-4">
            @can('access.users.create')
                <a href="{{ route('system-access.users.create') }}" class="btn btn-primary px-4"><i class="fas fa-user-plus me-2"></i>{{ db_trans('create_admin_user') }}</a>
            @endcan
            @can('access.roles.create')
                <a href="{{ route('system-access.roles.create') }}" class="btn btn-outline-dark px-4"><i class="fas fa-id-badge me-2"></i>{{ db_trans('create_role') }}</a>
            @endcan
            @can('access.permissions.create')
                <a href="{{ route('system-access.permissions.create') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-key me-2"></i>{{ db_trans('create_permission') }}</a>
            @endcan
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = $('#accessRecentUsersTable');
            if (window.jQuery && $.fn.DataTable && table.length) {
                table.DataTable({
                    paging: true,
                    info: true,
                    searching: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                    order: [],
                    language: {
                        search: @json(db_trans('search')) + ':',
                        lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                        info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
                        infoEmpty: @json(db_trans('no_records_found')),
                        zeroRecords: @json(db_trans('no_records_found')),
                        paginate: { previous: '‹', next: '›' }
                    }
                });
            }

            if (typeof Chart !== 'undefined') {
                const roleCtx = document.getElementById('accessRoleChart');
                if (roleCtx) {
                    new Chart(roleCtx, {
                        type: 'line',
                        data: {
                            labels: @json(data_get($roleChart ?? [], 'labels', [])),
                            datasets: [
                                { label: @json(db_trans('permissions')), data: @json(data_get($roleChart ?? [], 'permissions', [])), borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.14)', fill: true, tension: .35, borderWidth: 3 },
                                { label: @json(db_trans('users')), data: @json(data_get($roleChart ?? [], 'users', [])), borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.10)', fill: true, tension: .35, borderWidth: 3 }
                            ]
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { position: 'bottom' } } }
                    });
                }

                const moduleCtx = document.getElementById('accessPermissionModuleChart');
                if (moduleCtx) {
                    new Chart(moduleCtx, {
                        type: 'bar',
                        data: {
                            labels: @json(data_get($permissionModuleChart ?? [], 'labels', [])),
                            datasets: [{ label: @json(db_trans('permissions')), data: @json(data_get($permissionModuleChart ?? [], 'values', [])), backgroundColor: '#8b5cf6', borderRadius: 10 }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
                    });
                }
            }
        });
    </script>
@endpush
