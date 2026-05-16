<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeachingTypeRequest;
use App\Http\Requests\UpdateTeachingTypeRequest;
use App\Models\TeachingType;
use App\Services\Mafundisho\TeachingTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingTypeController extends Controller
{
    public function __construct(protected TeachingTypeService $service)
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('mafundisho-types.view'), 403);

        return view('admin.mafundisho.types.index', [
            'pageTitle' => db_trans('mafundisho_teaching_types'),
            'teachingTypes' => $this->service->paginated($request->only(['search', 'status'])),
            'sacramentOptions' => TeachingType::sacramentOptions(),
            'eligibilityOptions' => TeachingType::eligibilityOptions(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(StoreTeachingTypeRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return back()->with('success', db_trans('mafundisho_type_created'));
    }

    public function update(UpdateTeachingTypeRequest $request, TeachingType $teaching_type): RedirectResponse
    {
        $this->service->update($teaching_type, $request->validated(), $request->user());

        return back()->with('success', db_trans('mafundisho_type_updated'));
    }

    public function destroy(Request $request, TeachingType $teaching_type): RedirectResponse
    {
        abort_unless($request->user()?->can('mafundisho-types.delete'), 403);

        $this->service->deleteOrDeactivate($teaching_type);

        return back()->with('success', db_trans('mafundisho_type_deleted_or_deactivated'));
    }
}
