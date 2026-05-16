<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\FinanceReportsFilterRequest;
use App\Services\Reports\FinanceReportsService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class FinanceReportController extends Controller
{
    public function __construct(protected FinanceReportsService $service)
    {
    }

    public function index(FinanceReportsFilterRequest $request): View
    {
        abort_unless(auth()->user()?->can('reports.finance.view'), 403);

        return view('admin.reports.finance.index', $this->service->summary(
            auth()->user(),
            $request->validated()
        ));
    }

    public function compliance(FinanceReportsFilterRequest $request): View
    {
        abort_unless(auth()->user()?->can('reports.finance.compliance.view'), 403);

        return view('admin.reports.finance.compliance', $this->service->compliance(
            auth()->user(),
            $request->validated()
        ));
    }

    public function complianceCsv(FinanceReportsFilterRequest $request): StreamedResponse
    {
        abort_unless(auth()->user()?->can('reports.finance.compliance.export'), 403);

        $data = $this->service->compliance(auth()->user(), $request->validated());
        $csv = $this->service->exportComplianceCsv($data['rows']->all());

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'monthly-contribution-compliance.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
