<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\UpdateCommunicationPreferenceRequest;
use App\Models\Member;
use App\Services\Communication\CommunicationPreferenceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CommunicationPreferenceController extends Controller
{
    public function __construct(
        protected CommunicationPreferenceService $preferenceService,
    ) {
        $this->middleware('permission:communication.manage_preferences')->only(['edit', 'update', 'optIn', 'optOut']);
        $this->middleware('permission:communication.view')->only(['index']);
    }

  public function index(): View
{
    $members = Member::query()
        ->with('communicationPreference')
        ->orderBy('first_name')
        ->orderBy('middle_name')
        ->orderBy('last_name')
        ->paginate(20);

    return view('admin.communication.preferences.index', compact('members'));
}

    public function edit(Member $member): View
    {
        $preference = $this->preferenceService->getOrCreateForMember($member);

        return view('admin.communication.preferences.edit', compact('member', 'preference'));
    }

    public function update(UpdateCommunicationPreferenceRequest $request, Member $member): RedirectResponse
    {
        $this->preferenceService->updateForMember($member, $request->validated());

        return redirect()
            ->route('admin.communication.preferences.edit', $member)
            ->with('success', db_trans('communication.preferences.updated_successfully'));
    }

    public function optOut(Member $member): RedirectResponse
    {
        $this->preferenceService->optOut($member, request('reason'));

        return back()->with('success', db_trans('communication.preferences.opted_out_successfully'));
    }

    public function optIn(Member $member): RedirectResponse
    {
        $this->preferenceService->optIn($member);

        return back()->with('success', db_trans('communication.preferences.opted_in_successfully'));
    }
}
