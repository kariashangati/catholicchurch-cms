<?php

namespace App\Http\Controllers\Admin\Membership;

use App\Exports\AgeGroupReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreAgeGroupRequest;
use App\Http\Requests\Membership\UpdateAgeGroupRequest;
use App\Models\AgeGroup;
use App\Models\Member;
use App\Services\Membership\AgeGroupService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgeGroupController extends Controller
{
    public function __construct(private readonly AgeGroupService $ageGroupService)
    {
    }

    public function index(): View
    {
        $ageGroups = AgeGroup::query()
            ->withCount('members')
            ->orderBy('min_age')
            ->orderBy('name')
            ->get();

        return view('admin.membership.age-groups.index', [
            'ageGroups' => $ageGroups,
            'genderScopes' => AgeGroup::genderScopes(),
            'membersWithoutDobCount' => Member::query()->whereNull('date_of_birth')->count(),
        ]);
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('membership.age-groups.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.age-groups-report',
            $this->ageGroupService->exportData(),
            'age-groups'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('membership.age-groups.view'), 403);

        return Excel::download(
            new AgeGroupReportExport(
                $this->ageGroupService->exportRows(),
                db_trans('age_groups')
            ),
            'age-groups-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreAgeGroupRequest $request): JsonResponse
    {
        try {
            $ageGroup = $this->ageGroupService->create($request->validated());

            return response()->json([
                'message' => db_trans('age_group_created_successfully'),
                'data' => $ageGroup,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function update(UpdateAgeGroupRequest $request, AgeGroup $age_group): JsonResponse
    {
        try {
            $ageGroup = $this->ageGroupService->update($age_group, $request->validated());

            return response()->json([
                'message' => db_trans('age_group_updated_successfully'),
                'data' => $ageGroup,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(AgeGroup $age_group): JsonResponse
    {
        $this->ageGroupService->delete($age_group);

        return response()->json([
            'message' => db_trans('age_group_deleted_successfully'),
        ]);
    }
}
