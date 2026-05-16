<?php

namespace App\Http\Controllers\Admin\Liturgy;

use App\Exports\LiturgyReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Liturgy\StoreOfferingTypeRequest;
use App\Http\Requests\Liturgy\UpdateOfferingTypeRequest;
use App\Models\OfferingType;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OfferingTypeController extends Controller
{
    public function index()
    {
        $offeringTypes = OfferingType::query()->latest()->get();
        $categories = OfferingType::availableCategories();

        return view('admin.liturgy.offering-types.index', compact('offeringTypes', 'categories'));
    }

    public function store(StoreOfferingTypeRequest $request)
    {
        OfferingType::create($request->validated());

        return redirect()->route('liturgy.offering-types.index')
            ->with('success', db_trans('offering_type_created_successfully'));
    }

    public function update(UpdateOfferingTypeRequest $request, OfferingType $offering_type)
    {
        $offering_type->update($request->validated());

        return redirect()->route('liturgy.offering-types.index')
            ->with('success', db_trans('offering_type_updated_successfully'));
    }

    public function destroy(OfferingType $offering_type)
    {
        if ($offering_type->offerings()->exists()) {
            return redirect()->route('liturgy.offering-types.index')
                ->withErrors(['delete' => db_trans('offering_type_delete_blocked')]);
        }

        $offering_type->delete();

        return redirect()->route('liturgy.offering-types.index')
            ->with('success', db_trans('offering_type_deleted_successfully'));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('liturgy.offering-types.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.liturgy-report',
            $this->exportData(db_trans('offering_types')),
            'offering-types'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('liturgy.offering-types.view'), 403);

        return Excel::download(
            new LiturgyReportExport($this->excelRows()),
            'offering-types-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    protected function exportData(string $title): array
    {
        $records = OfferingType::query()->latest()->get();

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
            'rows' => $records->values()->map(fn (OfferingType $offeringType, int $index) => [
                $index + 1,
                $offeringType->name,
                $offeringType->category_label ?? $offeringType->category ?? '—',
                $offeringType->slug,
                $offeringType->is_active ? db_trans('active') : db_trans('inactive'),
            ])->all(),
        ];
    }

    protected function excelRows(): array
    {
        return array_merge([$this->columns()], $this->exportData(db_trans('offering_types'))['rows']);
    }

    protected function columns(): array
    {
        return [
            '#',
            db_trans('name'),
            db_trans('category'),
            db_trans('slug'),
            db_trans('status'),
        ];
    }
}
