<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\TitheFilterRequest;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Tithe;
use App\Services\Finance\TitheDashboardService;

class TitheDashboardController extends Controller
{
    public function __construct(protected TitheDashboardService $service)
    {
    }

    public function index(TitheFilterRequest $request)
    {
        $filters = $request->validated();
        $data = $this->service->dashboardData($request->user(), $filters);
        $data += $this->sharedFilters();

        return view('admin.finance.tithes.dashboard', $data);
    }

    protected function sharedFilters(): array
    {
        return [
            'statuses' => Tithe::availableStatuses(),
            'paymentMethods' => Tithe::availablePaymentMethods(),
            'kandas' => Kanda::orderBy('name')->get(['id', 'name']),
            'jumuiyas' => Jumuiya::orderBy('name')->get(['id', 'name', 'kanda_id']),
            'members' => Member::orderBy('first_name')->limit(300)->get(['id', 'first_name', 'middle_name', 'last_name', 'member_code']),
        ];
    }
}
