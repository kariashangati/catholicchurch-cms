<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceFilterRequest;
use App\Http\Requests\Finance\StoreMainOfferingRequest;
use App\Models\MainOffering;
use App\Models\MassType;
use App\Services\Finance\FinanceDashboardService;

class MainOfferingController extends Controller
{
    public function __construct(protected FinanceDashboardService $service)
    {
    }

    public function index(FinanceFilterRequest $request)
    {
        $data = $this->service->getMainOfferingsData($request->user(), $request->validated());
        $data['massTypes'] = MassType::where('is_active', true)->orderBy('name')->get();
        $data['statuses'] = ['pending', 'approved', 'rejected'];

        return view('admin.finance.main-offerings.index', $data);
    }

    public function store(StoreMainOfferingRequest $request)
    {
        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        if ($data['status'] === 'approved') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        }

        MainOffering::create($data);

        return redirect()->route('finance.main-offerings.index')->with('success', db_trans('record_created_successfully'));
    }

    public function update(StoreMainOfferingRequest $request, MainOffering $mainOffering)
    {
        $data = $request->validated();

        if ($data['status'] === 'approved' && !$mainOffering->approved_at) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        }

        $mainOffering->update($data);

        return redirect()->route('finance.main-offerings.index')->with('success', db_trans('record_updated_successfully'));
    }

    public function destroy(MainOffering $mainOffering)
    {
        $mainOffering->delete();

        return redirect()->route('finance.main-offerings.index')->with('success', db_trans('record_deleted_successfully'));
    }
}
