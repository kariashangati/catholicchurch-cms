<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SacramentLegacyReportRequest;
use App\Services\Reports\SacramentLegacyReportsService;
use Illuminate\View\View;

class SacramentLegacyReportController extends Controller
{
    public function __construct(protected SacramentLegacyReportsService $service)
    {
    }

    public function index(): View
    {
        abort_unless(auth()->user()?->can('reports.sacraments.view'), 403);

        return view('admin.reports.sacraments.index', $this->service->indexData());
    }

    public function generate(SacramentLegacyReportRequest $request): View
    {
        abort_unless(auth()->user()?->can('reports.sacraments.view'), 403);

        $report = $this->service->generate(auth()->user(), $request->validated());

        return view($report['view'], $report['data']);
    }
}
