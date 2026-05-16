<?php

namespace App\Http\Controllers\Admin\Liturgy;

use App\Exports\LiturgyReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Liturgy\StoreMassScheduleRequest;
use App\Http\Requests\Liturgy\UpdateMassScheduleRequest;
use App\Models\ApostolicGroup;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\LeadershipAssignment;
use App\Models\MassSchedule;
use App\Models\MassType;
use App\Models\Member;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MassScheduleController extends Controller
{
    public function index()
    {
        $massSchedules = $this->schedulesQuery()->get();

        $massTypes = MassType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $assignmentTargets = [
            'member' => Member::query()
                ->orderBy('first_name')
                ->orderBy('middle_name')
                ->orderBy('last_name')
                ->limit(500)
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'member_code', 'phone']),
            'jumuiya' => Jumuiya::query()->orderBy('name')->get(['id', 'name']),
            'kanda' => Kanda::query()->orderBy('name')->get(['id', 'name']),
            'apostolic_group' => ApostolicGroup::query()->orderBy('name')->get(['id', 'name']),
            'leadership_assignment' => LeadershipAssignment::query()
                ->with(['position', 'member'])
                ->latest()
                ->limit(500)
                ->get(),
        ];

        $statusOptions = MassSchedule::availableStatuses();

        return view('admin.liturgy.mass-schedules.index', compact(
            'massSchedules',
            'massTypes',
            'assignmentTargets',
            'statusOptions'
        ));
    }

    public function store(StoreMassScheduleRequest $request)
    {
        MassSchedule::create($request->validated() + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('liturgy.mass-schedules.index')
            ->with('success', db_trans('mass_schedule_created_successfully'));
    }

    public function update(UpdateMassScheduleRequest $request, MassSchedule $mass_schedule)
    {
        $mass_schedule->update($request->validated() + [
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('liturgy.mass-schedules.index')
            ->with('success', db_trans('mass_schedule_updated_successfully'));
    }

    public function destroy(MassSchedule $mass_schedule)
    {
        $mass_schedule->assignments()->delete();
        $mass_schedule->delete();

        return redirect()->route('liturgy.mass-schedules.index')
            ->with('success', db_trans('mass_schedule_deleted_successfully'));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('liturgy.mass-schedules.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.liturgy-report',
            $this->exportData(db_trans('mass_schedules')),
            'mass-schedules'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('liturgy.mass-schedules.view'), 403);

        return Excel::download(
            new LiturgyReportExport($this->excelRows()),
            'mass-schedules-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    protected function schedulesQuery()
    {
        return MassSchedule::query()
            ->with(['massType', 'assignments.assignable'])
            ->latest('scheduled_at');
    }

    protected function exportData(string $title): array
    {
        $records = $this->schedulesQuery()->get();

        return [
            'pageTitle' => $title,
            'reportTitle' => $title,
            'sectionTitle' => $title,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'metaItems' => [
                ['label' => db_trans('records'), 'value' => number_format($records->count())],
                ['label' => db_trans('imechapishwa'), 'value' => number_format($records->where('status', MassSchedule::STATUS_PUBLISHED)->count())],
                ['label' => db_trans('assignments'), 'value' => number_format($records->sum(fn ($schedule) => $schedule->assignments?->count() ?? 0))],
            ],
            'columns' => $this->columns(),
            'rows' => $records->values()->map(fn (MassSchedule $schedule, int $index) => [
                $index + 1,
                $schedule->title,
                $schedule->massType?->name ?: '—',
                optional($schedule->scheduled_at)->format('d M Y H:i') ?: '—',
                $schedule->location ?: '—',
                $schedule->status_label ?? $schedule->status ?? '—',
                $schedule->assignments->map(fn ($assignment) => trim(($assignment->role_name ?? '') . ': ' . $assignment->assignableLabel()))->filter()->implode('; ') ?: '—',
            ])->all(),
        ];
    }

    protected function excelRows(): array
    {
        return array_merge([$this->columns()], $this->exportData(db_trans('mass_schedules'))['rows']);
    }

    protected function columns(): array
    {
        return [
            '#',
            db_trans('title'),
            db_trans('mass_type'),
            db_trans('date'),
            db_trans('location'),
            db_trans('status'),
            db_trans('assignments'),
        ];
    }
}
