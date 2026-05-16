<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreContributionTypeRequest;
use App\Http\Requests\Finance\UpdateContributionTypeRequest;
use App\Models\ContributionType;
use App\Services\Finance\ContributionTypeService;

class ContributionTypeController extends Controller
{
    public function __construct(protected ContributionTypeService $service)
    {
    }

    public function index()
    {
        $types = ContributionType::query()
            ->with('plan')
            ->latest()
            ->get();

        return view('admin.finance.contributions.types.index', compact('types'));
    }

    public function store(StoreContributionTypeRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('finance.contributions.types.index')
            ->with('success', db_trans('contribution_type_created_successfully'));
    }

    public function update(UpdateContributionTypeRequest $request, ContributionType $type)
    {
        $this->service->update($type, $request->validated());

        return redirect()
            ->route('finance.contributions.types.index')
            ->with('success', db_trans('contribution_type_updated_successfully'));
    }

    public function destroy(ContributionType $type)
    {
        $type->delete();

        return redirect()
            ->route('finance.contributions.types.index')
            ->with('success', db_trans('contribution_type_deleted_successfully'));
    }
}