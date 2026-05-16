<?php

namespace App\Http\Middleware;

use App\Services\SystemConfig\MaintenanceModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckConfiguredMaintenanceMode
{
    public function __construct(protected MaintenanceModeService $maintenanceModeService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $routeName = $route?->getName();

        if ($this->shouldBypass($request, $routeName)) {
            return $next($request);
        }

        $global = $this->maintenanceModeService->globalSettings();

        if (! empty($global['enabled'])) {
            return $this->maintenanceResponse($global['title'], $global['message']);
        }

        if ($this->maintenanceModeService->isRouteBlocked($routeName)) {
            $page = $this->maintenanceModeService->routeSettings((string) $routeName);

            return $this->maintenanceResponse($page['title'], $page['message']);
        }

        return $next($request);
    }

    protected function shouldBypass(Request $request, ?string $routeName): bool
    {
        if ($routeName === null) {
            return false;
        }

        $bypassPrefixes = [
            'login',
            'logout',
            'register',
            'password.',
            'verification.',
            'auth.',
            'lang.',
            'system-config.',
        ];

        foreach ($bypassPrefixes as $prefix) {
            if ($routeName === rtrim($prefix, '.') || str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return $request->user()?->can('system.config.maintenance.update') ?? false;
    }

    protected function maintenanceResponse(?string $title, ?string $message): Response
    {
        return response()->view('errors.maintenance', [
            'title' => $title ?: db_trans('maintenance_mode'),
            'message' => $message ?: db_trans('system_is_currently_under_maintenance'),
        ], 503);
    }
}
