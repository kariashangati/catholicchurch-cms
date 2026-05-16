<?php

namespace App\Services\Communication;

use Carbon\CarbonImmutable;

class CommunicationGuardService
{
    public function __construct(
        private readonly CommunicationControlSettingsService $settingsService,
    ) {}

    public function shouldRequireApproval(int $recipientCount, int $segmentCount): bool
    {
        $settings = $this->settingsService->all();

        if (($settings['default_requires_approval'] ?? false) === true) {
            return true;
        }

        return $recipientCount >= (int) ($settings['approval_threshold_recipients'] ?? 250)
            || $segmentCount >= (int) ($settings['approval_threshold_segments'] ?? 350);
    }

    public function isWithinQuietHours(?string $start = null, ?string $end = null, ?CarbonImmutable $at = null): bool
    {
        $settings = $this->settingsService->all();

        if (! ($settings['quiet_hours_enabled'] ?? false)) {
            return false;
        }

        $now = $at ?? CarbonImmutable::now(config('app.timezone'));
        $startTime = $start ?? $settings['quiet_hours_start'] ?? '21:00';
        $endTime = $end ?? $settings['quiet_hours_end'] ?? '06:00';

        $current = $now->format('H:i');

        if ($startTime <= $endTime) {
            return $current >= $startTime && $current < $endTime;
        }

        return $current >= $startTime || $current < $endTime;
    }

    public function nextAllowedTime(?string $start = null, ?string $end = null, ?CarbonImmutable $at = null): CarbonImmutable
    {
        $settings = $this->settingsService->all();
        $now = $at ?? CarbonImmutable::now(config('app.timezone'));
        $endTime = $end ?? $settings['quiet_hours_end'] ?? '06:00';

        [$endHour, $endMinute] = array_map('intval', explode(':', $endTime));

        $candidate = $now->setTime($endHour, $endMinute);

        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate = $candidate->addDay();
        }

        return $candidate;
    }
}
