<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Exports\OperationsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceProviderRequest;
use App\Http\Requests\UpdateServiceProviderRequest;
use App\Models\Member;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Services\Operations\ServiceProviderService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServiceProviderController extends Controller
{
    public function __construct(private readonly ServiceProviderService $service)
    {
    }

    public function index(): View
    {
        return view('admin.operations.service-providers.index', [
            'providers' => ServiceProvider::query()->with(['category', 'member'])->latest()->get(),
            'categories' => ServiceCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'members' => Member::query()->orderBy('first_name')->orderBy('middle_name')->orderBy('last_name')->get(),
            'statuses' => ServiceProvider::statuses(),
        ]);
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.operations-report',
            $this->service->exportData(),
            'service-providers'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new OperationsReportExport($this->service->exportRowsForExcel(), db_trans('service_providers')),
            'service-providers-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreServiceProviderRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', db_trans('service_provider_created'));
    }

    public function update(UpdateServiceProviderRequest $request, ServiceProvider $service_provider): RedirectResponse
    {
        $this->service->update($service_provider, $request->validated());

        return back()->with('success', db_trans('service_provider_updated'));
    }

    public function destroy(ServiceProvider $service_provider): RedirectResponse
    {
        $service_provider->delete();

        return back()->with('success', db_trans('service_provider_deleted'));
    }
}
