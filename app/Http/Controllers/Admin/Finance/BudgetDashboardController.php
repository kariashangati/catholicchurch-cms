<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\BudgetEstimateService;
use Illuminate\Http\Request;

class BudgetDashboardController extends Controller
{
    public function __construct(protected BudgetEstimateService $service)
    {
    }

    public function index(Request $request)
    {
        return view('admin.finance.budgets.dashboard', $this->service->dashboardData($request->integer('year', now()->year)));
    }
}
