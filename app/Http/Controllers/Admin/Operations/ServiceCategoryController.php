<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Exports\OperationsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Services\Operations\ServiceCategoryService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServiceCategoryController extends Controller
{
    public function __construct(private readonly ServiceCategoryService $service)
    {
    }

    public function index(): View
    {
        return view('admin.operations.service-categories.index', [
            'categories' => ServiceCategory::query()->latest()->get(),
        ]);
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.operations-report',
            $this->service->exportData(),
            'service-categories'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new OperationsReportExport($this->service->exportRowsForExcel(), db_trans('service_categories')),
            'service-categories-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', db_trans('service_category_created'));
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $service_category): RedirectResponse
    {
        $this->service->update($service_category, $request->validated());

        return back()->with('success', db_trans('service_category_updated'));
    }

    public function destroy(ServiceCategory $service_category): RedirectResponse
    {
        if ($service_category->providers()->exists()) {
            return back()->withErrors(['delete' => db_trans('service_category_has_providers')]);
        }

        $service_category->delete();

        return back()->with('success', db_trans('service_category_deleted'));
    }
}
