<?php

namespace App\Http\Controllers\Admin\Leadership;

use App\Exports\LeadershipReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leadership\StoreLeadershipAssignmentRequest;
use App\Http\Requests\Leadership\UpdateLeadershipAssignmentRequest;
use App\Models\ApostolicGroup;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\LeadershipAssignment;
use App\Models\LeadershipPosition;
use App\Models\Member;
use App\Models\User;
use App\Services\Leadership\LeadershipService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadershipAssignmentController extends Controller
{
    public function __construct(protected LeadershipService $service)
    {
    }

    public function index()
    {
        $assignments = LeadershipAssignment::query()
            ->with(['member', 'user', 'position', 'kanda', 'jumuiya', 'apostolicGroup'])
            ->latest()
            ->get();

        $positions = LeadershipPosition::query()->where('is_active', true)->orderBy('display_order')->orderBy('name')->get();
        $members = Member::query()->orderBy('first_name')->orderBy('last_name')->get();
        $users = User::query()->orderBy('name')->get();
        $kandas = Kanda::query()->orderBy('name')->get();
        $jumuiyas = Jumuiya::query()->orderBy('name')->get();
        $apostolicGroups = ApostolicGroup::query()->orderBy('name')->get();

        return view('admin.leadership.assignments.index', compact('assignments', 'positions', 'members', 'users', 'kandas', 'jumuiyas', 'apostolicGroups'));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('leadership.assignments.view'), 403);

        $data = $this->service->assignmentsExportData();

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.leadership-report',
            $data,
            'leadership-assignments'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('leadership.assignments.view'), 403);

        $data = $this->service->assignmentsExportData();

        return Excel::download(
            new LeadershipReportExport(
                $this->service->assignmentsExportSheets($data),
                db_trans('leadership_assignments')
            ),
            'leadership-assignments-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreLeadershipAssignmentRequest $request)
    {
        $this->service->createAssignment($request->validated(), (int) $request->user()->id);
        return redirect()->route('leadership.assignments.index')->with('success', db_trans('leadership_assignment_created_successfully'));
    }

    public function update(UpdateLeadershipAssignmentRequest $request, LeadershipAssignment $assignment)
    {
        $this->service->updateAssignment($assignment, $request->validated());
        return redirect()->route('leadership.assignments.index')->with('success', db_trans('leadership_assignment_updated_successfully'));
    }

    public function destroy(LeadershipAssignment $assignment)
    {
        $this->service->deleteAssignment($assignment);
        return redirect()->route('leadership.assignments.index')->with('success', db_trans('leadership_assignment_deleted_successfully'));
    }
}
