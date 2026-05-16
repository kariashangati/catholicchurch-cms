<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Services\Cms\CmsDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CmsDashboardController extends Controller
{
    public function __construct(
        protected CmsDashboardService $dashboardService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('cms.dashboard.view'), 403);

        return view('admin.cms.dashboard', $this->dashboardService->data());
    }
}