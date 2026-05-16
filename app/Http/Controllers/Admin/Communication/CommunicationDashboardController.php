<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\CommunicationDashboardService;
use Illuminate\Contracts\View\View;

class CommunicationDashboardController extends Controller
{
    public function __construct(protected CommunicationDashboardService $dashboardService)
    {
    }

    public function index(): View
    {
        return view('admin.communication.dashboard.index', $this->dashboardService->getOverview());
    }
}
