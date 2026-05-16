<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMafundishoEnrollmentRequest;
use App\Http\Requests\UpdateMafundishoEnrollmentRequest;
use App\Models\MafundishoEnrollment;
use App\Services\Mafundisho\MafundishoEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MafundishoEnrollmentController extends Controller
{
    public function __construct(
        protected MafundishoEnrollmentService $service
    ) {
    }

    public function summary(): View
    {
        return view('admin.mafundisho.summary', [
            'pageTitle' => db_trans('teaching_enrollments'),
            ...$this->service->getSummaryData(auth()->user()),
        ]);
    }

    public function index(Request $request, string $type, int $year = null): View
    {
        $year = $year ?: (int) now()->year;

        return view('admin.mafundisho.index', [
            'pageTitle' => db_trans('teaching_students'),
            ...$this->service->getTypeIndexData(
                auth()->user(),
                $type,
                $year,
                $request->string('status')->toString() ?: null,
            ),
        ]);
    }

    public function store(StoreMafundishoEnrollmentRequest $request): RedirectResponse
    {
        $this->service->store($request->validated(), auth()->user());

        return back()->with('success', db_trans('teaching_enrollment_created_successfully'));
    }

    public function update(UpdateMafundishoEnrollmentRequest $request, MafundishoEnrollment $mafundisho_enrollment): RedirectResponse
    {
        $this->service->update($mafundisho_enrollment, $request->validated(), auth()->user());

        return back()->with('success', db_trans('teaching_enrollment_updated_successfully'));
    }

    public function destroy(MafundishoEnrollment $mafundisho_enrollment): RedirectResponse
    {
        $this->service->delete($mafundisho_enrollment, auth()->user());

        return back()->with('success', db_trans('teaching_enrollment_deleted_successfully'));
    }
}
