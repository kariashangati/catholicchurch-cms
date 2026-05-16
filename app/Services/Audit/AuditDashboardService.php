<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuditDashboardService
{
    public function getDataFor(User $user): array
    {
        abort_unless($user->can('audit.view'), 403);

        return [
            'page' => [
                'title' => db_trans('audit_access_center'),
                'updated_at' => now(),
            ],
            'kpis' => $this->getKpis(),
            'activityTrendChart' => $this->getActivityTrendChart(),
            'loginTrendChart' => $this->getLoginTrendChart(),
            'moduleBreakdownChart' => $this->getModuleBreakdownChart(),
            'eventBreakdownChart' => $this->getEventBreakdownChart(),
            'riskBreakdownChart' => $this->getRiskBreakdownChart(),
            'browserUsageChart' => $this->getBrowserUsageChart(),
            'liveFeed' => $this->getLiveFeed(),
            'topActors' => $this->getTopActors(),
            'riskHighlights' => $this->getRiskHighlights(),
        ];
    }

    protected function getKpis(): array
    {
        return [
            [
                'title' => db_trans('activities_today'),
                'value' => AuditLog::query()->whereDate('created_at', today())->count(),
                'icon' => 'fas fa-wave-square',
                'tone' => 'primary',
            ],
            [
                'title' => db_trans('unique_actors_today'),
                'value' => AuditLog::query()
                    ->whereDate('created_at', today())
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count('user_id'),
                'icon' => 'fas fa-user-shield',
                'tone' => 'info',
            ],
            [
                'title' => db_trans('logins_24h'),
                'value' => LoginHistory::query()
                    ->where('status', 'success')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'icon' => 'fas fa-sign-in-alt',
                'tone' => 'success',
            ],
            [
                'title' => db_trans('failed_logins_24h'),
                'value' => LoginHistory::query()
                    ->whereIn('status', ['failed', 'locked_out'])
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'icon' => 'fas fa-exclamation-triangle',
                'tone' => 'danger',
            ],
            [
                'title' => db_trans('suspicious_logins_7d'),
                'value' => LoginHistory::query()
                    ->where('is_suspicious', true)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'icon' => 'fas fa-user-secret',
                'tone' => 'warning',
            ],
            [
                'title' => db_trans('active_ips_7d'),
                'value' => LoginHistory::query()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->whereNotNull('ip_address')
                    ->distinct('ip_address')
                    ->count('ip_address'),
                'icon' => 'fas fa-network-wired',
                'tone' => 'secondary',
            ],
        ];
    }

    protected function getActivityTrendChart(): array
    {
        $days = collect(range(13, 0))->map(function (int $offset) {
            $date = today()->subDays($offset);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
            ];
        });

        $counts = AuditLog::query()
            ->selectRaw('DATE(created_at) as audit_date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'audit_date');

        return [
            'labels' => $days->pluck('label')->all(),
            'values' => $days->map(fn (array $day) => (int) ($counts[$day['date']] ?? 0))->all(),
        ];
    }

    protected function getLoginTrendChart(): array
    {
        $days = collect(range(13, 0))->map(function (int $offset) {
            $date = today()->subDays($offset);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
            ];
        });

        $success = LoginHistory::query()
            ->selectRaw('DATE(created_at) as login_date, COUNT(*) as total')
            ->where('status', 'success')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'login_date');

        $failed = LoginHistory::query()
            ->selectRaw('DATE(created_at) as login_date, COUNT(*) as total')
            ->whereIn('status', ['failed', 'locked_out'])
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'login_date');

        return [
            'labels' => $days->pluck('label')->all(),
            'success' => $days->map(fn (array $day) => (int) ($success[$day['date']] ?? 0))->all(),
            'failed' => $days->map(fn (array $day) => (int) ($failed[$day['date']] ?? 0))->all(),
        ];
    }

    protected function getModuleBreakdownChart(): array
    {
        $rows = AuditLog::query()
            ->selectRaw('`module`, COUNT(*) as total')
            ->groupBy('module')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows
                ->map(fn ($row) => $this->translateModuleLabel($this->safeGroupLabel($row->module, 'system')))
                ->all(),
            'values' => $rows
                ->map(fn ($row) => (int) $row->total)
                ->all(),
        ];
    }

    protected function getEventBreakdownChart(): array
    {
        $rows = AuditLog::query()
            ->selectRaw('`event`, COUNT(*) as total')
            ->groupBy('event')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows
                ->map(fn ($row) => $this->translateEventLabel($this->safeGroupLabel($row->event, 'unknown')))
                ->all(),
            'values' => $rows
                ->map(fn ($row) => (int) $row->total)
                ->all(),
        ];
    }

    protected function getRiskBreakdownChart(): array
    {
        $rows = LoginHistory::query()
            ->selectRaw('`risk_level`, COUNT(*) as total')
            ->groupBy('risk_level')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows
                ->map(fn ($row) => $this->translateRiskLabel($this->safeGroupLabel($row->risk_level, 'low')))
                ->all(),
            'values' => $rows
                ->map(fn ($row) => (int) $row->total)
                ->all(),
        ];
    }

    protected function getBrowserUsageChart(): array
    {
        $rows = LoginHistory::query()
            ->selectRaw('`browser`, COUNT(*) as total')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows
                ->map(fn ($row) => $this->safeGroupLabel($row->browser, 'Unknown'))
                ->all(),
            'values' => $rows
                ->map(fn ($row) => (int) $row->total)
                ->all(),
        ];
    }

    protected function getLiveFeed(): array
    {
        return AuditLog::query()
            ->with('user')
            ->latest()
            ->take(12)
            ->get()
            ->map(function (AuditLog $log) {
                return [
                    'icon' => $this->resolveFeedIcon((string) $log->event),
                    'tone' => $this->resolveFeedTone((string) $log->risk_level),
                    'title' => $this->translateAuditText($log->description ?: $log->display_action),
                    'actor' => $log->actor_name,
                    'meta' => trim(collect([
                        $log->module ? $this->translateModuleLabel((string) $log->module) : null,
                        $log->ip_address,
                    ])->filter()->implode(' • ')),
                    'time' => optional($log->created_at)->diffForHumans(),
                ];
            })
            ->all();
    }

    protected function getTopActors(): array
    {
        return AuditLog::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->with('user:id,name,email')
            ->whereNotNull('user_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function (AuditLog $row) {
                return [
                    'name' => $row->user?->name ?: db_trans('unknown'),
                    'email' => $row->user?->email,
                    'total' => (int) $row->total,
                ];
            })
            ->all();
    }

    protected function getRiskHighlights(): array
    {
        $lastSuccessful = LoginHistory::query()
            ->where('status', 'success')
            ->latest('logged_in_at')
            ->first();

        $lastFailed = LoginHistory::query()
            ->whereIn('status', ['failed', 'locked_out'])
            ->latest('created_at')
            ->first();

        $topIp = LoginHistory::query()
            ->selectRaw('ip_address, COUNT(*) as total')
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderByDesc('total')
            ->first();

        $topModule = AuditLog::query()
            ->selectRaw('`module`, COUNT(*) as total')
            ->groupBy('module')
            ->orderByDesc('total')
            ->first();

        $topModuleName = $topModule
            ? $this->translateModuleLabel($this->safeGroupLabel($topModule->module, 'system'))
            : 'N/A';

        return [
            [
                'label' => db_trans('last_successful_login'),
                'value' => $lastSuccessful?->display_user ?: 'N/A',
                'meta' => $lastSuccessful?->logged_in_at?->diffForHumans() ?: '-',
            ],
            [
                'label' => db_trans('last_failed_login'),
                'value' => $lastFailed?->display_email ?: 'N/A',
                'meta' => $lastFailed?->created_at?->diffForHumans() ?: '-',
            ],
            [
                'label' => db_trans('top_login_ip'),
                'value' => $topIp?->ip_address ?: 'N/A',
                'meta' => $topIp ? ((int) $topIp->total . ' ' . db_trans('attempts')) : '-',
            ],
            [
                'label' => db_trans('most_active_module'),
                'value' => $topModuleName,
                'meta' => $topModule ? ((int) $topModule->total . ' ' . db_trans('events')) : '-',
            ],
        ];
    }

    protected function safeGroupLabel(mixed $value, string $fallback): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    protected function translateAuditText(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return db_trans('not_available');
        }

        return match ($value) {
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
            default => db_trans($value),
        };
    }

    protected function translateModuleLabel(string $module): string
    {
        return match ($module) {
            'auth' => db_trans('auth'),
            'system_config' => db_trans('system_config'),
            'cms' => db_trans('cms'),
            'access' => db_trans('access'),
            'users' => db_trans('users'),
            'roles' => db_trans('roles'),
            'permissions' => db_trans('permissions'),
            'system' => db_trans('system'),
            default => db_trans(strtolower(str_replace(' ', '_', trim($module)))),
        };
    }

    protected function translateEventLabel(string $event): string
    {
        return match ($event) {
            'login' => db_trans('login'),
            'logout' => db_trans('logout'),
            'failed_login' => db_trans('failed_login'),
            'locked_out' => db_trans('locked_out'),
            'created' => db_trans('created'),
            'updated' => db_trans('updated'),
            'deleted' => db_trans('deleted'),
            'unknown' => db_trans('unknown'),
            default => db_trans(strtolower(str_replace(' ', '_', trim($event)))),
        };
    }

    protected function translateRiskLabel(string $risk): string
    {
        return match ($risk) {
            'low' => db_trans('low'),
            'medium' => db_trans('medium'),
            'high' => db_trans('high'),
            'critical' => db_trans('critical'),
            default => db_trans('not_available'),
        };
    }

    protected function resolveFeedIcon(string $event): string
    {
        return match ($event) {
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'failed_login', 'locked_out' => 'fas fa-exclamation-triangle',
            'created' => 'fas fa-plus-circle',
            'updated' => 'fas fa-edit',
            'deleted' => 'fas fa-trash',
            default => 'fas fa-shield-alt',
        };
    }

    protected function resolveFeedTone(string $riskLevel): string
    {
        return match ($riskLevel) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            default => 'primary',
        };
    }
}