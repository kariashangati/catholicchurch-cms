<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Services\Access\AccessDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccessDashboardController extends Controller
{
    public function __construct(
        protected AccessDashboardService $accessDashboardService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('access.dashboard.view'), 403);

        return view('admin.access.dashboard', $this->accessDashboardService->getDataFor($request->user()));
    }
}