<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        return view('admin.dashboard.index', $this->dashboardService->getDataFor(
            $request->user(),
            $request->query('date')
        ));
    }
}
