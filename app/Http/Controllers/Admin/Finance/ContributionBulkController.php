<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\BulkStoreContributionRequest;
use App\Services\Finance\ContributionBulkService;
use Illuminate\Http\Request;

class ContributionBulkController extends Controller
{
    public function __construct(protected ContributionBulkService $service)
    {
    }

    public function index(Request $request)
    {
        $data = $this->service->pageData($request->user(), $request->all());

        return view('admin.finance.contributions.bulk.index', $data);
    }

    public function store(BulkStoreContributionRequest $request)
    {
        $result = $this->service->store($request->validated(), $request->user());

        return redirect()
            ->route('finance.contributions.bulk.index', $request->only([
                'kanda_id',
                'jumuiya_id',
                'contribution_type_id',
                'source_type',
                'bank_account_id',
                'contribution_date',
            ]))
            ->with('success', db_trans('bulk_contributions_saved_successfully') . ' (' . number_format($result['saved_count']) . ')');
    }
}
