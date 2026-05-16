<?php

namespace App\Http\Controllers\Admin\Leadership;

use App\Exports\LeadershipReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leadership\StoreLeadershipPositionRequest;
use App\Http\Requests\Leadership\UpdateLeadershipPositionRequest;
use App\Models\LeadershipPosition;
use App\Services\Leadership\LeadershipService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadershipPositionController extends Controller
{
    public function __construct(protected LeadershipService $service)
    {
    }

    public function index()
    {
        $positions = LeadershipPosition::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('admin.leadership.positions.index', compact('positions'));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('leadership.positions.view'), 403);

        $data = $this->service->positionsExportData();

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.leadership-report',
            $data,
            'leadership-positions'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('leadership.positions.view'), 403);

        $data = $this->service->positionsExportData();

        return Excel::download(
            new LeadershipReportExport(
                $this->service->positionsExportSheets($data),
                db_trans('leadership_positions')
            ),
            'leadership-positions-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreLeadershipPositionRequest $request)
    {
        $this->service->createPosition($request->validated());

        return redirect()
            ->route('leadership.positions.index')
            ->with('success', db_trans('leadership_position_created_successfully'));
    }

    public function update(UpdateLeadershipPositionRequest $request, LeadershipPosition $position)
    {
        $this->service->updatePosition($position, $request->validated());

        return redirect()
            ->route('leadership.positions.index')
            ->with('success', db_trans('leadership_position_updated_successfully'));
    }

    public function destroy(LeadershipPosition $position)
    {
        if ($position->assignments()->exists()) {
            return redirect()
                ->route('leadership.positions.index')
                ->withErrors(['delete' => db_trans('leadership_position_delete_blocked')]);
        }

        $position->delete();

        return redirect()
            ->route('leadership.positions.index')
            ->with('success', db_trans('leadership_position_deleted_successfully'));
    }
}
