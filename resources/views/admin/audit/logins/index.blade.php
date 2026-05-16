@extends('layouts.admin')

@section('title', db_trans('login_history'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/auditlogins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/audit-access-fixes.css') }}">
@endpush

@section('content')
@php
    $translateStatus = fn (?string $status): string => match ((string) $status) {
        'success' => db_trans('success'),
        'failed' => db_trans('failed'),
        'logged_out' => db_trans('logged_out'),
        'locked_out' => db_trans('locked_out'),
        default => db_trans('not_available'),
    };

    $translateRisk = fn (?string $risk): string => match ((string) $risk) {
        'low' => db_trans('low'),
        'medium' => db_trans('medium'),
        'high' => db_trans('high'),
        'critical' => db_trans('critical'),
        default => db_trans('not_available'),
    };

    $translateReason = function (?string $value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $map = [
            'Invalid credentials' => db_trans('invalid_credentials'),
            'Too many login attempts' => db_trans('too_many_login_attempts'),
            'new_ip_address' => db_trans('new_ip_address'),
            'new_browser_platform' => db_trans('new_browser_platform'),
            'recent_failed_attempts' => db_trans('recent_failed_attempts'),
            'invalid_credentials' => db_trans('invalid_credentials'),
            'too_many_attempts' => db_trans('too_many_attempts'),
            'desktop' => db_trans('desktop'),
            'mobile' => db_trans('mobile'),
            'tablet' => db_trans('tablet'),
            'unknown' => db_trans('unknown'),
        ];

        return $map[$value] ?? db_trans($value);
    };
@endphp

<div class="admin-ui-v4 login-page-shell">
    <div class="login-page-hero audit-hero-standard">
        <div class="ui-hero-pattern"></div>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 position-relative">
            <div>
                <span class="audit-hero-badge">
                    <i class="fas fa-sign-in-alt"></i>
                    {{ db_trans('login_history') }}
                </span>
                <h2 class="login-page-hero-title mt-3">{{ db_trans('login_history') }}</h2>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('system.audit.index') }}" class="btn btn-light">
                    <i class="fas fa-chart-line me-2"></i>{{ db_trans('audit_access_center') }}
                </a>
                <a href="{{ route('system.activity-logs.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-stream me-2"></i>{{ db_trans('activity_logs') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card login-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('system.login-history.index') }}">
                <div class="row g-3">
                    <div class="col-xl-3 col-md-6">
                        <label class="login-filter-label">{{ db_trans('search') }}</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ db_trans('user_ip_browser_status') }}">
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="login-filter-label">{{ db_trans('user') }}</label>
                        <select name="user_id" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="login-filter-label">{{ db_trans('status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach(['success', 'failed', 'logged_out', 'locked_out'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $translateStatus($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="login-filter-label">{{ db_trans('risk') }}</label>
                        <select name="risk_level" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach(['low', 'medium', 'high', 'critical'] as $risk)
                                <option value="{{ $risk }}" @selected(($filters['risk_level'] ?? '') === $risk)>{{ $translateRisk($risk) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label class="login-filter-label">{{ db_trans('suspicious_only') }}</label>
                        <select name="suspicious_only" class="form-select">
                            <option value="0" @selected((string) ($filters['suspicious_only'] ?? '0') === '0')>{{ db_trans('all') }}</option>
                            <option value="1" @selected((string) ($filters['suspicious_only'] ?? '0') === '1')>{{ db_trans('yes') }}</option>
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="login-filter-label">{{ db_trans('from') }}</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="login-filter-label">{{ db_trans('to') }}</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="login-filter-label">{{ db_trans('per_page') }}</label>
                        <select name="per_page" class="form-select">
                            @foreach([10, 20, 30, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 20) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}</button>
                        <a href="{{ route('system.login-history.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-rotate-left me-2"></i>{{ db_trans('reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card login-table-card">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">{{ db_trans('login_history') }}</h4>
                    <div class="text-muted">
                        {{ db_trans('showing') }} {{ $logins->firstItem() ?? 0 }}
                        {{ db_trans('to') }} {{ $logins->lastItem() ?? 0 }}
                        {{ db_trans('of') }} {{ $logins->total() }}
                        {{ db_trans('login_records') }}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table login-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ db_trans('time') }}</th>
                            <th>{{ db_trans('user') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('ip_country') }}</th>
                            <th>{{ db_trans('device') }}</th>
                            <th>{{ db_trans('risk') }}</th>
                            <th>{{ db_trans('suspicious') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logins as $login)
                            <tr>
                                <td style="min-width: 170px;">
                                    <div class="login-cell-title">{{ optional($login->logged_in_at ?: $login->created_at)->format('d M Y, h:i A') }}</div>
                                    <div class="login-cell-meta">{{ optional($login->created_at)->diffForHumans() }}</div>
                                </td>

                                <td style="min-width: 220px;">
                                    <div class="login-cell-title">{{ $login->display_user }}</div>
                                    <div class="login-cell-meta">{{ $login->display_email }}</div>
                                </td>

                                <td style="min-width: 160px;">
                                    <span class="login-status-badge status-{{ $login->status }}">{{ $translateStatus($login->status) }}</span>
                                    @if($login->failure_reason)
                                        <div class="login-cell-meta mt-2">{{ $translateReason($login->failure_reason) }}</div>
                                    @endif
                                </td>

                                <td style="min-width: 180px;">
                                    <div class="login-cell-title">{{ $login->ip_address ?: db_trans('not_available') }}</div>
                                    <div class="login-cell-meta">
                                        {{ $login->country ?: db_trans('unknown_country') }}
                                        @if($login->city) • {{ $login->city }} @endif
                                    </div>
                                </td>

                                <td style="min-width: 220px;">
                                    <div class="login-cell-title">{{ $login->display_device }}</div>
                                    <div class="login-cell-meta">{{ $translateReason($login->device_type ?: 'unknown') }}</div>

                                    @if(!empty($login->suspicion_reasons))
                                        <div class="login-cell-meta mt-1">
                                            {{ collect($login->suspicion_reasons)->map(fn ($item) => $translateReason($item))->implode(' • ') }}
                                        </div>
                                    @endif
                                </td>

                                <td style="min-width: 120px;">
                                    <span class="login-risk-badge risk-{{ $login->risk_level }}">{{ $translateRisk($login->risk_level) }}</span>
                                </td>

                                <td style="min-width: 120px;">
                                    @if($login->is_suspicious)
                                        <span class="login-suspicious-yes">{{ db_trans('yes') }}</span>
                                    @else
                                        <span class="login-suspicious-no">{{ db_trans('no') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"><div class="login-empty">{{ db_trans('no_login_history_found') }}</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logins->hasPages())
                <div class="audit-pagination-wrap border-top mt-3">
                    {{ $logins->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
