<?php

namespace App\Services\SystemConfig;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class MaintenanceModeService
{
    public function __construct(
        protected SiteSettingsService $siteSettingsService
    ) {
    }

    public function isGloballyEnabled(): bool
    {
        return App::isDownForMaintenance()
            || (bool) $this->siteSettingsService->get('maintenance.global.enabled', false);
    }

    public function globalSettings(): array
    {
        $token = (string) $this->siteSettingsService->get('maintenance.laravel.secret', '');

        return [
            'enabled' => (bool) $this->siteSettingsService->get('maintenance.global.enabled', false),
            'laravel_down' => App::isDownForMaintenance(),
            'laravel_secret' => $token,
            'laravel_bypass_url' => $token !== '' ? url($token) : null,
            'title' => $this->siteSettingsService->get('maintenance.global.title', db_trans('maintenance_mode')),
            'message' => $this->siteSettingsService->get('maintenance.global.message', db_trans('system_is_currently_under_maintenance')),
        ];
    }

    public function enableGlobal(string $title, string $message, bool $useLaravelDown = false): void
    {
        $this->siteSettingsService->set('maintenance.global.enabled', true, 'boolean', 'maintenance');
        $this->siteSettingsService->set('maintenance.global.title', $title, 'string', 'maintenance');
        $this->siteSettingsService->set('maintenance.global.message', $message, 'string', 'maintenance');

        if ($useLaravelDown && ! App::isDownForMaintenance()) {
            $secret = Str::random(40);
            $this->siteSettingsService->set('maintenance.laravel.secret', $secret, 'string', 'maintenance', false, false);

            try {
                Artisan::call('down', [
                    '--secret' => $secret,
                    '--retry' => 60,
                ]);
            } catch (\Throwable) {
                Artisan::call('down');
            }
        }
    }

    public function disableGlobal(bool $alsoRunLaravelUp = true): void
    {
        $this->siteSettingsService->set('maintenance.global.enabled', false, 'boolean', 'maintenance');

        if ($alsoRunLaravelUp && App::isDownForMaintenance()) {
            $this->turnLaravelUp();
        }
    }

    public function turnLaravelUp(): void
    {
        if (App::isDownForMaintenance()) {
            Artisan::call('up');
        }

        $this->siteSettingsService->set('maintenance.laravel.secret', null, 'string', 'maintenance', false, false);
    }

    public function setRouteMaintenance(string $routeName, bool $enabled, ?string $title = null, ?string $message = null): void
    {
        $routeName = trim($routeName);

        if ($routeName === '') {
            return;
        }

        $base = "maintenance.routes.{$routeName}";

        $this->siteSettingsService->set("{$base}.enabled", $enabled, 'boolean', 'maintenance');
        $this->siteSettingsService->set("{$base}.title", $title ?: db_trans('maintenance_mode'), 'string', 'maintenance');
        $this->siteSettingsService->set("{$base}.message", $message ?: db_trans('this_page_is_under_maintenance'), 'string', 'maintenance');
    }

    public function routeSettings(string $routeName): array
    {
        $base = "maintenance.routes.{$routeName}";

        return [
            'enabled' => (bool) $this->siteSettingsService->get("{$base}.enabled", false),
            'title' => $this->siteSettingsService->get("{$base}.title", db_trans('maintenance_mode')),
            'message' => $this->siteSettingsService->get("{$base}.message", db_trans('this_page_is_under_maintenance')),
        ];
    }

    public function isRouteBlocked(?string $routeName): bool
    {
        if (blank($routeName)) {
            return false;
        }

        return (bool) $this->siteSettingsService->get("maintenance.routes.{$routeName}.enabled", false);
    }
}
