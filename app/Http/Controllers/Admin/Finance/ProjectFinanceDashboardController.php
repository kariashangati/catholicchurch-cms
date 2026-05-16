<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\ProjectFinanceTopProjectsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Projects\ProjectFinanceFilterRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTransaction;
use App\Services\Finance\ProjectFinanceDashboardService;
use App\Services\Pdf\PdfReportService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectFinanceDashboardController extends Controller
{
    public function __construct(protected ProjectFinanceDashboardService $service)
    {
    }

    public function index(ProjectFinanceFilterRequest $request)
    {
        $filters = $request->validated();

        $data = $this->service->getDashboardData($filters);

        $data['categories'] = ProjectCategory::query()
            ->orderBy('name')
            ->get();

        $data['projects'] = Project::query()
            ->orderBy('name')
            ->get();

        $data['projectStatuses'] = Project::availableStatuses();
        $data['transactionTypes'] = ProjectTransaction::availableTypes();
        $data['transactionStatuses'] = ProjectTransaction::availableStatuses();

        return view('admin.finance.projects.dashboard', $data);
    }

    public function exportTopProjectsPdf(
        ProjectFinanceFilterRequest $request,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.project-finance-top-projects',
            $this->service->getTopProjectsExportPdfData($request->validated()),
            'project-finance-top-projects'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportTopProjectsExcel(ProjectFinanceFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new ProjectFinanceTopProjectsExport($this->service, $request->validated()),
            'project-finance-top-projects-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}