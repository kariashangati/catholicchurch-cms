<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\ProjectFinanceProjectsExport;
use App\Exports\ProjectFinanceTransactionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Projects\ProjectFinanceFilterRequest;
use App\Http\Requests\Finance\Projects\StoreProjectCategoryRequest;
use App\Http\Requests\Finance\Projects\StoreProjectRequest;
use App\Http\Requests\Finance\Projects\StoreProjectTransactionRequest;
use App\Http\Requests\Finance\Projects\UpdateProjectCategoryRequest;
use App\Http\Requests\Finance\Projects\UpdateProjectRequest;
use App\Http\Requests\Finance\Projects\UpdateProjectTransactionRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTransaction;
use App\Services\Finance\ProjectFinanceDashboardService;
use App\Services\Finance\ProjectFinanceService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectFinanceController extends Controller
{
    public function __construct(
        protected ProjectFinanceDashboardService $dashboardService,
        protected ProjectFinanceService $service,
    ) {
    }

    public function index(ProjectFinanceFilterRequest $request)
    {
        $filters = $request->validated();

        $data = $this->dashboardService->getIndexData($filters);

        $data['categoryOptions'] = ProjectCategory::query()
            ->orderBy('name')
            ->get();

        $data['projectOptions'] = Project::query()
            ->with('category')
            ->orderBy('name')
            ->get();

        $data['projectStatuses'] = Project::availableStatuses();
        $data['transactionTypes'] = ProjectTransaction::availableTypes();
        $data['transactionStatuses'] = ProjectTransaction::availableStatuses();
        $data['paymentMethods'] = ProjectTransaction::availablePaymentMethods();

        return view('admin.finance.projects.index', $data);
    }

    public function exportProjectsPdf(
        ProjectFinanceFilterRequest $request,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.project-finance-projects',
            $this->dashboardService->getProjectsExportPdfData($request->validated()),
            'project-finance-projects'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportProjectsExcel(ProjectFinanceFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new ProjectFinanceProjectsExport($this->dashboardService, $request->validated()),
            'project-finance-projects-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportTransactionsPdf(
        ProjectFinanceFilterRequest $request,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.project-finance-transactions',
            $this->dashboardService->getTransactionsExportPdfData($request->validated()),
            'project-finance-transactions'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportTransactionsExcel(ProjectFinanceFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new ProjectFinanceTransactionsExport($this->dashboardService, $request->validated()),
            'project-finance-transactions-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function storeCategory(StoreProjectCategoryRequest $request): RedirectResponse
    {
        $this->service->createCategory($request->validated(), $request->user()->id);

        return back()->with('status', db_trans('project_category_created_successfully'));
    }

    public function updateCategory(UpdateProjectCategoryRequest $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $this->service->updateCategory($projectCategory, $request->validated(), $request->user()->id);

        return back()->with('status', db_trans('project_category_updated_successfully'));
    }

    public function destroyCategory(ProjectCategory $projectCategory): RedirectResponse
    {
        try {
            $this->service->deleteCategory($projectCategory);

            return back()->with('status', db_trans('project_category_deleted_successfully'));
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'project_category_delete' => db_trans('cannot_delete_project_category_with_projects'),
            ]);
        }
    }

    public function storeProject(StoreProjectRequest $request): RedirectResponse
    {
        $this->service->createProject($request->validated(), $request->user()->id);

        return back()->with('status', db_trans('project_created_successfully'));
    }

    public function updateProject(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->service->updateProject($project, $request->validated(), $request->user()->id);

        return back()->with('status', db_trans('project_updated_successfully'));
    }

    public function destroyProject(Project $project): RedirectResponse
    {
        try {
            $this->service->deleteProject($project);

            return back()->with('status', db_trans('project_deleted_successfully'));
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'project_delete' => db_trans('cannot_delete_project_with_transactions'),
            ]);
        }
    }

    public function storeTransaction(StoreProjectTransactionRequest $request): RedirectResponse
    {
        $this->service->createTransaction($request->validated(), $request->user()->id);

        return back()->with('status', db_trans('project_transaction_created_successfully'));
    }

    public function updateTransaction(UpdateProjectTransactionRequest $request, ProjectTransaction $projectTransaction): RedirectResponse
    {
        $this->service->updateTransaction($projectTransaction, $request->validated(), $request->user()->id);

        return back()->with('status', db_trans('project_transaction_updated_successfully'));
    }

    public function destroyTransaction(ProjectTransaction $projectTransaction): RedirectResponse
    {
        $this->service->deleteTransaction($projectTransaction);

        return back()->with('status', db_trans('project_transaction_deleted_successfully'));
    }
}