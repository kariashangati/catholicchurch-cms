<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Exports\OperationsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChurchAssetRequest;
use App\Http\Requests\UpdateChurchAssetRequest;
use App\Models\AssetCategory;
use App\Models\ChurchAsset;
use App\Services\Operations\ChurchAssetService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChurchAssetController extends Controller
{
    public function __construct(private readonly ChurchAssetService $service)
    {
    }

    public function index(): View
    {
        return view('admin.operations.church-assets.index', [
            'assets' => ChurchAsset::query()->with('category')->latest()->get(),
            'categories' => AssetCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'conditionStatuses' => ChurchAsset::conditionStatuses(),
        ]);
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.operations-report',
            $this->service->exportData(),
            'church-assets'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new OperationsReportExport($this->service->exportRowsForExcel(), db_trans('church_assets')),
            'church-assets-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreChurchAssetRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', db_trans('church_asset_created'));
    }

    public function update(UpdateChurchAssetRequest $request, ChurchAsset $church_asset): RedirectResponse
    {
        $this->service->update($church_asset, $request->validated());

        return back()->with('success', db_trans('church_asset_updated'));
    }

    public function destroy(ChurchAsset $church_asset): RedirectResponse
    {
        $church_asset->delete();

        return back()->with('success', db_trans('church_asset_deleted'));
    }
}
