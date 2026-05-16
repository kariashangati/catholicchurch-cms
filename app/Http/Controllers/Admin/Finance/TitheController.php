<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\TitheActivityDateExport;
use App\Exports\TitheActivityLogExport;
use App\Exports\TitheActivityRecorderExport;
use App\Exports\TitheExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\BulkStoreTitheRequest;
use App\Http\Requests\Finance\StoreTitheRequest;
use App\Http\Requests\Finance\TitheDuplicateReviewRequest;
use App\Http\Requests\Finance\TitheFilterRequest;
use App\Http\Requests\Finance\UpdateBahashaRequest;
use App\Http\Requests\Finance\UpdateTitheRequest;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Finance\TitheDashboardService;
use App\Services\Finance\TitheService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TitheController extends Controller
{
    public function __construct(
        protected TitheDashboardService $dashboard,
        protected TitheService $service,
    ) {
    }

    public function index(TitheFilterRequest $request)
    {
        $data = $this->dashboard->indexData($request->user(), $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.index', $data);
    }

    public function exportPdf(TitheFilterRequest $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.tithes-list',
            $this->dashboard->getTithesExportPdfData($request->user(), $request->validated()),
            'tithes'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(TitheFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new TitheExport($this->dashboard, $request->user(), $request->validated()),
            'tithes-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function activityLog(TitheFilterRequest $request)
    {
        $data = $this->dashboard->activityLogData($request->user(), $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.activity-log', $data);
    }

    public function exportActivityLogPdf(TitheFilterRequest $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.tithe-activity-log',
            $this->dashboard->getTitheActivityLogExportPdfData($request->user(), $request->validated()),
            'tithe-activity-log'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportActivityLogExcel(TitheFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new TitheActivityLogExport($this->dashboard, $request->user(), $request->validated()),
            'tithe-activity-log-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function activityDate(TitheFilterRequest $request, string $date)
    {
        $data = $this->dashboard->activityDateData($request->user(), $date, $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.activity-date', $data);
    }

    public function exportActivityDatePdf(
        TitheFilterRequest $request,
        PdfReportService $pdfReportService,
        string $date
    ): BinaryFileResponse {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.tithe-activity-date',
            $this->dashboard->getTitheActivityDateExportPdfData($request->user(), $date, $request->validated()),
            'tithe-activity-date-' . $date
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportActivityDateExcel(TitheFilterRequest $request, string $date): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new TitheActivityDateExport($this->dashboard, $request->user(), $date, $request->validated()),
            'tithe-activity-date-' . $date . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function activityRecorder(TitheFilterRequest $request, string $date, User $recorder)
    {
        $data = $this->dashboard->activityRecorderData($request->user(), $date, $recorder, $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.activity-recorder', $data);
    }

    public function exportActivityRecorderPdf(
        TitheFilterRequest $request,
        PdfReportService $pdfReportService,
        string $date,
        User $recorder
    ): BinaryFileResponse {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.tithe-activity-recorder',
            $this->dashboard->getTitheActivityRecorderExportPdfData($request->user(), $date, $recorder, $request->validated()),
            'tithe-activity-recorder-' . $date . '-' . $recorder->id
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportActivityRecorderExcel(TitheFilterRequest $request, string $date, User $recorder): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new TitheActivityRecorderExport($this->dashboard, $request->user(), $date, $recorder, $request->validated()),
            'tithe-activity-recorder-' . $date . '-' . $recorder->id . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function bulkEntry(TitheFilterRequest $request)
    {
        $data = $this->dashboard->bulkEntryData($request->user(), $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.bulk-entry', $data);
    }

    public function bulkPreview(Request $request): JsonResponse
    {
        return response()->json($this->service->bulkPreview($request->all(), $request->user()));
    }

    public function duplicates(TitheDuplicateReviewRequest $request)
    {
        $data = $this->dashboard->duplicateReviewData($request->user(), $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.duplicates', $data);
    }

    public function showJumuiya(TitheFilterRequest $request, Jumuiya $jumuiya)
    {
        $data = $this->dashboard->jumuiyaDetail($request->user(), $jumuiya, $request->validated());
        $data += $this->shared();

        return view('admin.finance.tithes.jumuiya-show', $data);
    }

    public function monthly(TitheFilterRequest $request, int $month)
    {
        $year = (int) ($request->validated()['year'] ?? now()->year);
        $data = $this->dashboard->monthlyBreakdown($request->user(), $month, $year);

        return view('admin.finance.tithes.monthly', $data);
    }

    public function matrix(TitheFilterRequest $request, Jumuiya $jumuiya, ?int $year = null)
    {
        $data = $this->dashboard->memberMatrix($request->user(), $jumuiya, $year ?: now()->year);

        return view('admin.finance.tithes.member-matrix', $data);
    }

    public function store(StoreTitheRequest $request)
    {
        $this->service->store($request->validated(), $request->user());

        return back()->with('success', db_trans('tithe_saved_successfully'));
    }

    public function update(UpdateTitheRequest $request, Tithe $tithe)
    {
        $this->service->update($tithe, $request->validated(), $request->user());

        return back()->with('success', db_trans('tithe_updated_successfully'));
    }

    public function destroy(Tithe $tithe)
    {
        $this->service->delete($tithe, request()->user());

        return back()->with('success', db_trans('tithe_deleted_successfully'));
    }

    public function bulkStore(BulkStoreTitheRequest $request)
    {
        $summary = $this->service->bulkStore($request->validated(), $request->user());

        return redirect()
            ->route('finance.tithes.bulk.entry', [
                'date' => $request->input('contribution_date'),
                'kanda_id' => $request->integer('kanda_id'),
                'jumuiya_id' => $request->integer('jumuiya_id'),
            ])
            ->with('success', db_trans('bulk_tithe_batch_saved_successfully') . ' ' . ($summary['saved_count'] ?? 0));
    }

    public function updateBahasha(UpdateBahashaRequest $request, Member $member)
    {
        $member->update(['bahasha' => $request->validated()['bahasha'] ?? null]);

        return response()->json([
            'success' => true,
            'message' => db_trans('bahasha_updated_successfully'),
        ]);
    }

    protected function shared(): array
    {
        return [
            'statuses' => Tithe::availableStatuses(),
            'paymentMethods' => Tithe::availablePaymentMethods(),
            'kandas' => Kanda::orderBy('name')->get(['id', 'name']),
            'jumuiyas' => Jumuiya::with('kanda')->orderBy('name')->get(['id', 'name', 'kanda_id']),
            'members' => Member::with('familia.jumuiya.kanda')
                ->orderBy('first_name')
                ->get(['id', 'familia_id', 'first_name', 'middle_name', 'last_name', 'member_code', 'phone', 'bahasha']),
        ];
    }
}