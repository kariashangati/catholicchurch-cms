<?php

namespace App\Http\Controllers\Admin\Liturgy;

use App\Exports\LiturgyReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Liturgy\StoreMassTypeRequest;
use App\Http\Requests\Liturgy\UpdateMassTypeRequest;
use App\Models\MassType;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MassTypeController extends Controller
{
    public function index()
    {
        $massTypes = MassType::query()->latest()->get();

        return view('admin.liturgy.mass-types.index', compact('massTypes'));
    }

    public function store(StoreMassTypeRequest $request)
    {
        MassType::create($request->validated());

        return redirect()->route('liturgy.mass-types.index')
            ->with('success', db_trans('mass_type_created_successfully'));
    }

    public function update(UpdateMassTypeRequest $request, MassType $mass_type)
    {
        $mass_type->update($request->validated());

        return redirect()->route('liturgy.mass-types.index')
            ->with('success', db_trans('mass_type_updated_successfully'));
    }

    public function destroy(MassType $mass_type)
    {
        if ($mass_type->offerings()->exists() || $mass_type->schedules()->exists()) {
            return redirect()->route('liturgy.mass-types.index')
                ->withErrors(['delete' => db_trans('mass_type_delete_blocked')]);
        }

        $mass_type->delete();

        return redirect()->route('liturgy.mass-types.index')
            ->with('success', db_trans('mass_type_deleted_successfully'));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('liturgy.mass-types.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.liturgy-report',
            $this->exportData(db_trans('mass_types')),
            'mass-types'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('liturgy.mass-types.view'), 403);

        return Excel::download(
            new LiturgyReportExport($this->excelRows()),
            'mass-types-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    protected function exportData(string $title): array
    {
        $records = MassType::query()->latest()->get();

        return [
            'pageTitle' => $title,
            'reportTitle' => $title,
            'sectionTitle' => $title,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'metaItems' => [
                ['label' => db_trans('records'), 'value' => number_format($records->count())],
                ['label' => db_trans('active'), 'value' => number_format($records->where('is_active', true)->count())],
                ['label' => db_trans('inactive'), 'value' => number_format($records->where('is_active', false)->count())],
            ],
            'columns' => $this->columns(),
            'rows' => $records->values()->map(fn (MassType $massType, int $index) => [
                $index + 1,
                $massType->name,
                $massType->slug,
                $massType->description ?: '—',
                $massType->is_active ? db_trans('active') : db_trans('inactive'),
            ])->all(),
        ];
    }

    protected function excelRows(): array
    {
        return array_merge([$this->columns()], $this->exportData(db_trans('mass_types'))['rows']);
    }

    protected function columns(): array
    {
        return [
            '#',
            db_trans('name'),
            db_trans('slug'),
            db_trans('description'),
            db_trans('status'),
        ];
    }
}
