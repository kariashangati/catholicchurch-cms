<?php

namespace App\Services\SystemConfig;

class SystemConfigurationDashboardService
{
    public function __construct(
        protected SiteSettingsService $siteSettingsService,
        protected EnvironmentSettingsService $environmentSettingsService,
        protected MaintenanceModeService $maintenanceModeService,
        protected SystemInformationService $systemInformationService,
        protected CommunicationConfigurationService $communicationConfigurationService
    ) {
    }

    public function data(): array
    {
        $system = $this->systemInformationService->data();
        $smsBalance = $this->communicationConfigurationService->smsBalance();

        return [
            'page' => [
                'title' => db_trans('system_configuration_center'),
                'subtitle' => db_trans('manage_general_branding_environment_maintenance_and_communication_settings'),
                'updated_at' => now(),
            ],

            'branding' => [
                'name' => $this->siteSettingsService->get('site.name', config('app.name')),
                'tagline' => $this->siteSettingsService->get('site.tagline'),
                'description' => $this->siteSettingsService->get('site.description'),
                'logo' => $this->siteSettingsService->get('site.logo'),
                'favicon' => $this->siteSettingsService->get('site.favicon'),
                'image' => $this->siteSettingsService->get('site.image'),
            ],

            'kpis' => [
                [
                    'title' => db_trans('app_environment'),
                    'value' => strtoupper((string) ($system['app_env'] ?? 'unknown')),
                    'icon' => 'fas fa-layer-group',
                    'tone' => 'info',
                ],
                [
                    'title' => db_trans('debug_mode'),
                    'value' => ! empty($system['app_debug']) ? db_trans('enabled') : db_trans('disabled'),
                    'icon' => 'fas fa-bug',
                    'tone' => ! empty($system['app_debug']) ? 'warning' : 'success',
                ],
                [
                    'title' => db_trans('maintenance_mode'),
                    'value' => $this->maintenanceModeService->isGloballyEnabled() ? db_trans('enabled') : db_trans('disabled'),
                    'icon' => 'fas fa-tools',
                    'tone' => $this->maintenanceModeService->isGloballyEnabled() ? 'warning' : 'success',
                ],
                [
                    'title' => db_trans('php_version'),
                    'value' => $system['php_version'] ?? '-',
                    'icon' => 'fab fa-php',
                    'tone' => 'primary',
                ],
                [
                    'title' => db_trans('laravel_version'),
                    'value' => $system['laravel_version'] ?? '-',
                    'icon' => 'fab fa-laravel',
                    'tone' => 'danger',
                ],
                [
                    'title' => db_trans('sms_balance'),
                    'value' => $smsBalance['success'] ? (string) ($smsBalance['balance'] ?? '-') : db_trans('unavailable'),
                    'icon' => 'fas fa-sms',
                    'tone' => $smsBalance['success'] ? 'success' : 'secondary',
                ],
            ],

            'environment' => $this->environmentSettingsService->grouped(),
            'system' => $system,
            'sms_balance' => $smsBalance,
            'maintenance' => $this->maintenanceModeService->globalSettings(),
        ];
    }
}