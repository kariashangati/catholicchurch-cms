<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditDashboardController extends Controller
{
    public function __construct(
        protected AuditDashboardService $auditDashboardService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('audit.view'), 403);

        $data = $this->auditDashboardService->getDataFor($request->user());

        return view('admin.audit.dashboard', $data);
    }
}