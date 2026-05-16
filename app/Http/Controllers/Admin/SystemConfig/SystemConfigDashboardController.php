<?php

namespace App\Http\Controllers\Admin\SystemConfig;

use App\Http\Controllers\Controller;
use App\Services\SystemConfig\SystemConfigurationDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SystemConfigDashboardController extends Controller
{
    public function __construct(
        protected SystemConfigurationDashboardService $dashboardService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('system.config.dashboard.view'), 403);

        return view('admin.system-config.dashboard', $this->dashboardService->data());
    }
}