<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReportDashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->can('reports.view'), 403);

        return view('admin.reports.index', [
            'pageTitle' => db_trans('reports_dashboard'),
        ]);
    }
}
