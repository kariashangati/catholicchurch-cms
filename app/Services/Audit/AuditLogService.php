<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class AuditLogService
{
    public function log(array $data, ?Request $request = null): AuditLog
    {
        $request ??= request();

        $subject = $data['subject'] ?? null;
        $user = $data['user'] ?? auth()->user();

        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'log_name' => $data['log_name'] ?? 'activity',
            'event' => $data['event'],
            'module' => $data['module'] ?? null,
            'action' => $data['action'] ?? null,
            'subject_type' => $subject instanceof Model ? $subject->getMorphClass() : ($data['subject_type'] ?? null),
            'subject_id' => $subject instanceof Model ? $subject->getKey() : ($data['subject_id'] ?? null),
            'subject_label' => $data['subject_label'] ?? $this->resolveSubjectLabel($subject),
            'description' => $data['description'] ?? null,
            'old_values' => $this->normalizeArrayData($data['old_values'] ?? null),
            'new_values' => $this->normalizeArrayData($data['new_values'] ?? null),
            'properties' => $this->normalizeArrayData($data['properties'] ?? null),
            'ip_address' => $data['ip_address'] ?? $request->ip(),
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            'browser' => $data['browser'] ?? $this->detectBrowser($request->userAgent()),
            'platform' => $data['platform'] ?? $this->detectPlatform($request->userAgent()),
            'device_type' => $data['device_type'] ?? $this->detectDeviceType($request->userAgent()),
            'method' => $data['method'] ?? $request->method(),
            'route_name' => $data['route_name'] ?? optional($request->route())->getName(),
            'url' => $data['url'] ?? $request->fullUrl(),
            'risk_level' => $data['risk_level'] ?? 'low',
            'occurred_at' => $data['occurred_at'] ?? Carbon::now(),
        ]);
    }

    public function logModelEvent(
        string $event,
        string $module,
        Model $subject,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $user = null,
        ?Request $request = null,
        string $riskLevel = 'low'
    ): AuditLog {
        return $this->log([
            'user' => $user,
            'event' => $event,
            'module' => $module,
            'action' => ucfirst($event),
            'subject' => $subject,
            'subject_label' => $this->resolveSubjectLabel($subject),
            'description' => $description ?? sprintf('%s %s', class_basename($subject), $event),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'risk_level' => $riskLevel,
        ], $request);
    }

    protected function resolveSubjectLabel(mixed $subject): ?string
    {
        if (! $subject instanceof Model) {
            return null;
        }

        foreach (['name', 'title', 'receipt_no', 'member_code', 'email'] as $field) {
            if (isset($subject->{$field}) && filled($subject->{$field})) {
                return (string) $subject->{$field};
            }
        }

        return class_basename($subject) . ' #' . $subject->getKey();
    }

    protected function normalizeArrayData(mixed $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            return Arr::where($value, fn ($item) => ! is_null($item));
        }

        return null;
    }

    protected function detectBrowser(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return 'Unknown';
        }

        $agent = strtolower($userAgent);

        return match (true) {
            str_contains($agent, 'edg') => 'Edge',
            str_contains($agent, 'opr') || str_contains($agent, 'opera') => 'Opera',
            str_contains($agent, 'chrome') && ! str_contains($agent, 'edg') => 'Chrome',
            str_contains($agent, 'firefox') => 'Firefox',
            str_contains($agent, 'safari') && ! str_contains($agent, 'chrome') => 'Safari',
            default => 'Unknown',
        };
    }

    protected function detectPlatform(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return 'Unknown';
        }

        $agent = strtolower($userAgent);

        return match (true) {
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'mac os') || str_contains($agent, 'macintosh') => 'macOS',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'iphone') || str_contains($agent, 'ipad') || str_contains($agent, 'ios') => 'iOS',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Unknown',
        };
    }

    protected function detectDeviceType(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return 'unknown';
        }

        $agent = strtolower($userAgent);

        return match (true) {
            str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone') => 'mobile',
            str_contains($agent, 'ipad') || str_contains($agent, 'tablet') => 'tablet',
            default => 'desktop',
        };
    }
}