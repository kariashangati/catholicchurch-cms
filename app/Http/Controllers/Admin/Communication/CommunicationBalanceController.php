<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\CommunicationDashboardService;

class CommunicationBalanceController extends Controller
{
    public function __construct(private readonly CommunicationDashboardService $dashboardService)
    {
    }

    public function index()
    {
        abort_unless(auth()->user()?->can('communication.manage_balance') || auth()->user()?->can('communication.view'), 403);

        $data = $this->dashboardService->getBalanceSummary();

        return view('admin.communication.balance.index', $data);
    }
}
