@extends('layouts.admin')

@section('title', db_trans('admin_user_profile'))



@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
	   <link rel="stylesheet" href="{{ asset('admin/css/usershow.css') }}">
@endpush
@section('content')
    <div class="profile-shell">
        <div class="profile-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <h2 class="mb-2">{{ $adminUser->name }}</h2>
                    <p class="mb-2 text-white-50">{{ $adminUser->email }} • {{ $adminUser->phone ?: db_trans('not_available') }}</p>
                    <div>
                        @foreach($adminUser->roles as $role)
                            <span class="profile-chip">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('access.users.update')
                        <a href="{{ route('system-access.users.edit', $adminUser->id) }}" class="btn btn-light">
                            <i class="fas fa-pen me-2"></i>{{ db_trans('edit') }}
                        </a>
                    @endcan
                    <a href="{{ route('system-access.users.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>{{ db_trans('back_to_admin_users') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="card profile-card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">{{ db_trans('profile_details') }}</h5>

                        <div class="mb-3">
                            <div class="profile-label">{{ db_trans('member') }}</div>
                            <div class="profile-value">{{ $adminUser->member?->member_code ?: db_trans('not_available') }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="profile-label">{{ db_trans('kanda') }}</div>
                            <div class="profile-value">{{ $adminUser->kanda?->name ?: db_trans('not_available') }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="profile-label">{{ db_trans('jumuiya') }}</div>
                            <div class="profile-value">{{ $adminUser->jumuiya?->name ?: db_trans('not_available') }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="profile-label">{{ db_trans('locale') }}</div>
                            <div class="profile-value">{{ strtoupper($adminUser->locale ?: '-') }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="profile-label">{{ db_trans('status') }}</div>
                            <div class="profile-value">{{ $adminUser->is_active ? db_trans('active') : db_trans('inactive') }}</div>
                        </div>

                        <div>
                            <div class="profile-label">{{ db_trans('last_login') }}</div>
                            <div class="profile-value">{{ $adminUser->last_login_at ? $adminUser->last_login_at->format('d M Y, h:i A') : db_trans('never') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card profile-card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">{{ db_trans('activity_logs') }}</h5>

                        <div class="table-responsive">
                            <table class="table profile-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('time') }}</th>
                                        <th>{{ db_trans('action') }}</th>
                                        <th>{{ db_trans('details') }}</th>
                                        <th>{{ db_trans('risk') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activityLogs as $log)
                                        <tr>
                                            <td>{{ optional($log->created_at)->format('d M Y, h:i A') }}</td>
                                            <td>{{ $log->action ?: ucfirst(str_replace('_', ' ', $log->event)) }}</td>
                                            <td>{{ $log->description ?: '-' }}</td>
                                            <td>{{ ucfirst($log->risk_level) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">{{ db_trans('no_activity_logs_found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($activityLogs->hasPages())
                            <div class="mt-3">{{ $activityLogs->links() }}</div>
                        @endif
                    </div>
                </div>

                <div class="card profile-card mt-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">{{ db_trans('login_history') }}</h5>

                        <div class="table-responsive">
                            <table class="table profile-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('time') }}</th>
                                        <th>{{ db_trans('status') }}</th>
                                        <th>{{ db_trans('ip_country') }}</th>
                                        <th>{{ db_trans('device') }}</th>
                                        <th>{{ db_trans('risk') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loginHistories as $login)
                                        <tr>
                                            <td>{{ optional($login->logged_in_at ?: $login->created_at)->format('d M Y, h:i A') }}</td>
                                            <td>{{ ucfirst(str_replace('_',' ', $login->status)) }}</td>
                                            <td>{{ $login->ip_address ?: '-' }} • {{ $login->country ?: db_trans('unknown_country') }}</td>
                                            <td>{{ $login->display_device }}</td>
                                            <td>{{ ucfirst($login->risk_level) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">{{ db_trans('no_login_history_found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($loginHistories->hasPages())
                            <div class="mt-3">{{ $loginHistories->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection