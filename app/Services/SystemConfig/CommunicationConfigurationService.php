<?php

namespace App\Services\SystemConfig;

use App\Services\Communication\Providers\BeemSmsService;

class CommunicationConfigurationService
{
    public function __construct(
        protected EnvironmentSettingsService $environmentSettingsService,
        protected BeemSmsService $beemSmsService
    ) {
    }

    public function smsSettings(): array
    {
        $grouped = $this->environmentSettingsService->grouped();

        return $grouped['sms'] ?? [];
    }

    public function mailSettings(): array
    {
        $grouped = $this->environmentSettingsService->grouped();

        return $grouped['mail'] ?? [];
    }

    public function smsBalance(): array
    {
        try {
            return $this->beemSmsService->getBalance();
        } catch (\Throwable $e) {
            return [
                'provider' => 'beem',
                'success' => false,
                'balance' => null,
                'currency' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateSmsSettings(array $items): void
    {
        $this->environmentSettingsService->updateMany($items);
    }

    public function updateMailSettings(array $items): void
    {
        $this->environmentSettingsService->updateMany($items);
    }
}