@extends('layouts.admin')

@section('title', db_trans('activity_logs'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/activity.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/audit-access-fixes.css') }}">
@endpush

@section('content')
@php
    $translateAuditValue = function (?string $value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '—';
        }

        $map = [
            'User logged in' => db_trans('user_logged_in'),
            'User login' => db_trans('user_login'),
            'User logout' => db_trans('user_logout'),
            'User logged out' => db_trans('user_logged_out'),
            'Failed login' => db_trans('failed_login'),
            'Failed login attempt' => db_trans('failed_login_attempt'),
            'Login lockout' => db_trans('login_lockout'),
            'Login temporarily locked due to too many attempts' => db_trans('login_temporarily_locked_due_to_too_many_attempts'),
            'System maintenance settings updated' => db_trans('system_maintenance_settings_updated'),
            'Maintenance settings updated' => db_trans('maintenance_settings_updated'),
            'Updated' => db_trans('updated'),
            'Created' => db_trans('created'),
            'Deleted' => db_trans('deleted'),
            'Login' => db_trans('login'),
            'Logout' => db_trans('logout'),
            'Auth' => db_trans('auth'),
            'System_config' => db_trans('system_config'),
            'System config' => db_trans('system_config'),
        ];

        return $map[$value] ?? $map[ucfirst(str_replace('_', ' ', $value))] ?? db_trans($value);
    };

    $translateRisk = fn (?string $risk): string => match ((string) $risk) {
        'low' => db_trans('low'),
        'medium' => db_trans('medium'),
        'high' => db_trans('high'),
        'critical' => db_trans('critical'),
        default => db_trans('not_available'),
    };

    $translateEvent = function (?string $event) use ($translateAuditValue): string {
        $event = (string) $event;
        return $translateAuditValue(ucfirst(str_replace('_', ' ', $event)));
    };
@endphp

<div class="admin-ui-v4 audit-page-shell">
    <div class="audit-page-hero audit-hero-standard">
        <div class="ui-hero-pattern"></div>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 position-relative">
            <div>
                <span class="audit-hero-badge">
                    <i class="fas fa-stream"></i>
                    {{ db_trans('activity_logs') }}
                </span>
                <h2 class="audit-page-hero-title mt-3">{{ db_trans('activity_logs') }}</h2>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('system.audit.index') }}" class="btn btn-light">
                    <i class="fas fa-chart-line me-2"></i>{{ db_trans('audit_access_center') }}
                </a>
                <a href="{{ route('system.login-history.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-sign-in-alt me-2"></i>{{ db_trans('login_history') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card audit-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('system.activity-logs.index') }}">
                <div class="row g-3">
                    <div class="col-xl-3 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('search') }}</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ db_trans('description_event_log_name') }}">
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('module') }}</label>
                        <select name="module" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>
                                    {{ $translateAuditValue(ucfirst(str_replace('_', ' ', $module))) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('event') }}</label>
                        <select name="event" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>
                                    {{ $translateEvent($event) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('risk') }}</label>
                        <select name="risk_level" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach(['low', 'medium', 'high', 'critical'] as $risk)
                                <option value="{{ $risk }}" @selected(($filters['risk_level'] ?? '') === $risk)>{{ $translateRisk($risk) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('actor') }}</label>
                        <select name="user_id" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('from') }}</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('to') }}</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="audit-filter-label">{{ db_trans('per_page') }}</label>
                        <select name="per_page" class="form-select">
                            @foreach([10, 20, 30, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 20) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}</button>
                        <a href="{{ route('system.activity-logs.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-rotate-left me-2"></i>{{ db_trans('reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card audit-table-card">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">{{ db_trans('activity_logs') }}</h4>
                    <div class="text-muted">
                        {{ db_trans('showing') }} {{ $logs->firstItem() ?? 0 }}
                        {{ db_trans('to') }} {{ $logs->lastItem() ?? 0 }}
                        {{ db_trans('of') }} {{ $logs->total() }}
                        {{ db_trans('activity_records') }}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table audit-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ db_trans('time') }}</th>
                            <th>{{ db_trans('actor') }}</th>
                            <th>{{ db_trans('action') }}</th>
                            <th>{{ db_trans('subject') }}</th>
                            <th>{{ db_trans('source') }}</th>
                            <th>{{ db_trans('details') }}</th>
                            <th>{{ db_trans('risk') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="min-width: 170px;">
                                    <div class="audit-cell-title">{{ optional($log->occurred_at ?: $log->created_at)->format('d M Y, h:i A') }}</div>
                                    <div class="audit-cell-meta">{{ optional($log->created_at)->diffForHumans() }}</div>
                                </td>

                                <td style="min-width: 180px;">
                                    <div class="audit-cell-title">{{ $log->actor_name }}</div>
                                    <div class="audit-cell-meta">{{ $translateAuditValue(ucfirst(str_replace('_', ' ', $log->module ?: 'system'))) }}</div>
                                </td>

                                <td style="min-width: 150px;">
                                    <span class="audit-event-badge">{{ $translateEvent($log->event) }}</span>
                                    <div class="audit-cell-meta mt-2">{{ $translateAuditValue($log->display_action) }}</div>
                                </td>

                                <td style="min-width: 190px;">
                                    <div class="audit-cell-title">{{ $log->subject_label ?: db_trans('not_available') }}</div>
                                    <div class="audit-cell-meta">{{ class_basename($log->subject_type ?: '') ?: '—' }}</div>
                                </td>

                                <td style="min-width: 180px;">
                                    <div class="audit-cell-title">{{ $log->ip_address ?: db_trans('not_available') }}</div>
                                    <div class="audit-cell-meta">{{ $log->request_method }} • {{ $log->route_name ?: db_trans('not_available') }}</div>
                                </td>

                                <td style="min-width: 260px;">
                                    <div class="audit-cell-title">{{ $translateAuditValue($log->description ?: $log->display_action) }}</div>
                                    @if(!empty($log->properties))
                                        <div class="audit-cell-meta mt-1">{{ \Illuminate\Support\Str::limit(json_encode($log->properties, JSON_UNESCAPED_UNICODE), 110) }}</div>
                                    @endif
                                </td>

                                <td style="min-width: 120px;">
                                    <span class="audit-risk-badge risk-{{ $log->risk_level }}">{{ $translateRisk($log->risk_level) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"><div class="audit-empty">{{ db_trans('no_activity_logs_found') }}</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="audit-pagination-wrap border-top mt-3">
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
