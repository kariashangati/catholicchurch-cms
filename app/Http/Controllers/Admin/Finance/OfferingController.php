<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\OfferingExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceFilterRequest;
use App\Http\Requests\Finance\StoreOfferingRequest;
use App\Http\Requests\Finance\UpdateOfferingRequest;
use App\Models\CentreDetail;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\MassType;
use App\Models\Offering;
use App\Models\OfferingType;
use App\Services\Finance\FinanceDashboardService;
use App\Services\Pdf\PdfReportService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OfferingController extends Controller
{
    public function __construct(protected FinanceDashboardService $service)
    {
    }

    public function index(FinanceFilterRequest $request)
    {
        $data = $this->service->getOfferingsIndexData($request->user(), $request->validated());
        $data['offeringTypes'] = OfferingType::where('is_active', true)->orderBy('name')->get();
        $data['massTypes'] = MassType::where('is_active', true)->orderBy('name')->get();
        $data['centres'] = CentreDetail::where('is_active', true)->orderBy('centre_name')->get();
        $data['kandas'] = Kanda::orderBy('name')->get();
        $data['jumuiyas'] = Jumuiya::orderBy('name')->get();
        $data['statuses'] = Offering::availableStatuses();
        $data['scopes'] = Offering::availableScopes();

        return view('admin.finance.offerings.index', $data);
    }

    public function exportPdf(FinanceFilterRequest $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view') || $request->user()->can('finance.offerings.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.offerings-list',
            $this->service->getOfferingsExportPdfData($request->user(), $request->validated()),
            'offerings'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(FinanceFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view') || $request->user()->can('finance.offerings.view'), 403);

        return Excel::download(
            new OfferingExport($this->service, $request->user(), $request->validated()),
            'offerings-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreOfferingRequest $request)
    {
        $data = $this->normalizePayload($request->validated());
        $data['recorded_by'] = $request->user()->id;

        if (($data['status'] ?? null) === Offering::STATUS_APPROVED) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        }

        Offering::create($data);

        return redirect()
            ->route('finance.offerings.index')
            ->with('success', db_trans('offering_created_successfully'));
    }

    public function update(UpdateOfferingRequest $request, Offering $offering)
    {
        $data = $this->normalizePayload($request->validated());

        if (($data['status'] ?? null) === Offering::STATUS_APPROVED && ! $offering->approved_at) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        }

        if (($data['status'] ?? null) !== Offering::STATUS_APPROVED) {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $offering->update($data);

        return redirect()
            ->route('finance.offerings.index')
            ->with('success', db_trans('offering_updated_successfully'));
    }

    public function destroy(Offering $offering)
    {
        $offering->delete();

        return redirect()
            ->route('finance.offerings.index')
            ->with('success', db_trans('offering_deleted_successfully'));
    }

    protected function normalizePayload(array $data): array
    {
        if (($data['collection_scope'] ?? null) === Offering::SCOPE_PARISH) {
            $data['kanda_id'] = null;
            $data['jumuiya_id'] = null;
        }

        if (($data['collection_scope'] ?? null) === Offering::SCOPE_KANDA) {
            $data['centre_detail_id'] = null;
            $data['jumuiya_id'] = null;
        }

        if (($data['collection_scope'] ?? null) === Offering::SCOPE_JUMUIYA) {
            $data['centre_detail_id'] = null;

            if (! empty($data['jumuiya_id'])) {
                $jumuiya = Jumuiya::find($data['jumuiya_id']);
                $data['kanda_id'] = $jumuiya?->kanda_id;
            }
        }

        return $data;
    }
}