<?php

namespace App\Services\Audit;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class LoginSecurityService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {
    }

    public function recordSuccessfulLogin(User $user, Request $request): LoginHistory
    {
        [$browser, $platform, $deviceType] = $this->resolveAgentData($request);

        $risk = $this->determineRiskLevel($user, $request, $browser, $platform);
        $isSuspicious = $risk !== 'low';
        $reasons = $this->buildSuspicionReasons($user, $request, $browser, $platform);

        $history = LoginHistory::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'email' => $user->email,
            'status' => 'success',
            'is_suspicious' => $isSuspicious,
            'risk_level' => $risk,
            'suspicion_reasons' => $reasons,
            'ip_address' => $request->ip(),
            'country' => null,
            'city' => null,
            'browser' => $browser,
            'platform' => $platform,
            'device_type' => $deviceType,
            'device_name' => trim($browser . ' on ' . $platform),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'logged_in_at' => now(),
            'last_seen_at' => now(),
            'meta' => [
                'route_name' => optional($request->route())->getName(),
                'url' => $request->fullUrl(),
            ],
        ]);

        if (Schema::hasColumn('users', 'last_login_at')) {
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
        }

        $this->auditLogService->log([
            'user' => $user,
            'log_name' => 'activity',
            'event' => 'login',
            'module' => 'auth',
            'action' => 'User login',
            'subject' => $user,
            'subject_label' => $user->name,
            'description' => $isSuspicious ? 'Suspicious login detected' : 'User logged in',
            'properties' => [
                'login_history_id' => $history->id,
                'status' => 'success',
                'suspicion_reasons' => $reasons,
            ],
            'risk_level' => $risk,
        ], $request);

        return $history;
    }

    public function recordFailedLogin(string $email, Request $request, ?string $reason = null): LoginHistory
    {
        [$browser, $platform, $deviceType] = $this->resolveAgentData($request);

        $history = LoginHistory::query()->create([
            'user_id' => null,
            'user_name' => null,
            'email' => $email,
            'status' => 'failed',
            'is_suspicious' => true,
            'risk_level' => 'medium',
            'suspicion_reasons' => ['invalid_credentials'],
            'ip_address' => $request->ip(),
            'country' => null,
            'city' => null,
            'browser' => $browser,
            'platform' => $platform,
            'device_type' => $deviceType,
            'device_name' => trim($browser . ' on ' . $platform),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'failure_reason' => $reason ?: 'Invalid credentials',
            'meta' => [
                'route_name' => optional($request->route())->getName(),
                'url' => $request->fullUrl(),
            ],
        ]);

        $this->auditLogService->log([
            'user' => null,
            'log_name' => 'activity',
            'event' => 'failed_login',
            'module' => 'auth',
            'action' => 'Failed login',
            'subject_type' => User::class,
            'subject_id' => null,
            'subject_label' => $email,
            'description' => 'Failed login attempt',
            'properties' => [
                'login_history_id' => $history->id,
                'email' => $email,
                'reason' => $reason ?: 'Invalid credentials',
            ],
            'risk_level' => 'medium',
        ], $request);

        return $history;
    }

    public function recordLockout(string $email, Request $request, int $seconds): LoginHistory
    {
        [$browser, $platform, $deviceType] = $this->resolveAgentData($request);

        $history = LoginHistory::query()->create([
            'user_id' => null,
            'user_name' => null,
            'email' => $email,
            'status' => 'locked_out',
            'is_suspicious' => true,
            'risk_level' => 'high',
            'suspicion_reasons' => ['too_many_attempts'],
            'ip_address' => $request->ip(),
            'browser' => $browser,
            'platform' => $platform,
            'device_type' => $deviceType,
            'device_name' => trim($browser . ' on ' . $platform),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'failure_reason' => 'Too many login attempts',
            'meta' => [
                'seconds' => $seconds,
                'route_name' => optional($request->route())->getName(),
                'url' => $request->fullUrl(),
            ],
        ]);

        $this->auditLogService->log([
            'user' => null,
            'log_name' => 'activity',
            'event' => 'locked_out',
            'module' => 'auth',
            'action' => 'Login lockout',
            'subject_type' => User::class,
            'subject_id' => null,
            'subject_label' => $email,
            'description' => 'Login temporarily locked due to too many attempts',
            'properties' => [
                'login_history_id' => $history->id,
                'email' => $email,
                'seconds' => $seconds,
            ],
            'risk_level' => 'high',
        ], $request);

        return $history;
    }

    public function recordLogout(?User $user, Request $request): void
    {
        if (! $user) {
            return;
        }

        $latestSession = LoginHistory::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->where('session_id', $request->session()->getId())
            ->latest('id')
            ->first();

        if ($latestSession) {
            $latestSession->forceFill([
                'logged_out_at' => now(),
                'last_seen_at' => now(),
            ])->save();
        }

        $this->auditLogService->log([
            'user' => $user,
            'log_name' => 'activity',
            'event' => 'logout',
            'module' => 'auth',
            'action' => 'User logout',
            'subject' => $user,
            'subject_label' => $user->name,
            'description' => 'User logged out',
            'properties' => [
                'session_id' => $request->session()->getId(),
                'login_history_id' => $latestSession?->id,
            ],
            'risk_level' => 'low',
        ], $request);
    }

    protected function determineRiskLevel(User $user, Request $request, string $browser, string $platform): string
    {
        $reasons = $this->buildSuspicionReasons($user, $request, $browser, $platform);

        return match (count($reasons)) {
            0 => 'low',
            1 => 'medium',
            2 => 'high',
            default => 'critical',
        };
    }

    protected function buildSuspicionReasons(User $user, Request $request, string $browser, string $platform): array
    {
        $reasons = [];

        $knownIp = LoginHistory::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->where('ip_address', $request->ip())
            ->exists();

        if (! $knownIp) {
            $reasons[] = 'new_ip_address';
        }

        $knownAgent = LoginHistory::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->where('browser', $browser)
            ->where('platform', $platform)
            ->exists();

        if (! $knownAgent) {
            $reasons[] = 'new_browser_platform';
        }

        $recentFailures = LoginHistory::query()
            ->where('email', $user->email)
            ->whereIn('status', ['failed', 'locked_out'])
            ->where('created_at', '>=', now()->subHours(12))
            ->count();

        if ($recentFailures >= 3) {
            $reasons[] = 'recent_failed_attempts';
        }

        return $reasons;
    }

    protected function resolveAgentData(Request $request): array
    {
        $agent = strtolower((string) $request->userAgent());

        $browser = match (true) {
            str_contains($agent, 'edg') => 'Edge',
            str_contains($agent, 'opr') || str_contains($agent, 'opera') => 'Opera',
            str_contains($agent, 'chrome') && ! str_contains($agent, 'edg') => 'Chrome',
            str_contains($agent, 'firefox') => 'Firefox',
            str_contains($agent, 'safari') && ! str_contains($agent, 'chrome') => 'Safari',
            default => 'Unknown',
        };

        $platform = match (true) {
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'mac os') || str_contains($agent, 'macintosh') => 'macOS',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'iphone') || str_contains($agent, 'ipad') || str_contains($agent, 'ios') => 'iOS',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Unknown',
        };

        $deviceType = match (true) {
            str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone') => 'mobile',
            str_contains($agent, 'tablet') || str_contains($agent, 'ipad') => 'tablet',
            default => 'desktop',
        };

        return [$browser, $platform, $deviceType];
    }
}