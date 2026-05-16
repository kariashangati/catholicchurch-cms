<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Exports\OperationsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use App\Services\Operations\AssetCategoryService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetCategoryController extends Controller
{
    public function __construct(private readonly AssetCategoryService $service)
    {
    }

    public function index(): View
    {
        return view('admin.operations.asset-categories.index', [
            'categories' => AssetCategory::query()->latest()->get(),
        ]);
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.operations-report',
            $this->service->exportData(),
            'asset-categories'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new OperationsReportExport($this->service->exportRowsForExcel(), db_trans('asset_categories')),
            'asset-categories-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreAssetCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', db_trans('asset_category_created'));
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $asset_category): RedirectResponse
    {
        $this->service->update($asset_category, $request->validated());

        return back()->with('success', db_trans('asset_category_updated'));
    }

    public function destroy(AssetCategory $asset_category): RedirectResponse
    {
        if ($asset_category->assets()->exists()) {
            return back()->withErrors(['delete' => db_trans('asset_category_has_assets')]);
        }

        $asset_category->delete();

        return back()->with('success', db_trans('asset_category_deleted'));
    }
}
