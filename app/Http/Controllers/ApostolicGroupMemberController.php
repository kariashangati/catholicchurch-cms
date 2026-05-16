<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApostolicGroupMemberRequest;
use App\Http\Requests\UpdateApostolicGroupMemberRequest;
use App\Models\ApostolicGroup;
use App\Models\ApostolicGroupMember;
use App\Services\ApostolicGroup\ApostolicGroupService;
use Illuminate\Http\RedirectResponse;

class ApostolicGroupMemberController extends Controller
{
    public function __construct(
        protected ApostolicGroupService $service
    ) {
    }

    public function store(StoreApostolicGroupMemberRequest $request, ApostolicGroup $apostolicGroup): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->addMembers(
            $apostolicGroup,
            $validated['member_ids'],
            $validated,
            auth()->user()
        );

        return back()->with('success', db_trans('apostolic_group_members_added_successfully'));
    }

    public function update(UpdateApostolicGroupMemberRequest $request, ApostolicGroup $apostolicGroup, ApostolicGroupMember $membership): RedirectResponse
    {
        $this->service->updateMembership($membership, $request->validated());

        return back()->with('success', db_trans('apostolic_group_member_updated_successfully'));
    }

    public function destroy(ApostolicGroup $apostolicGroup, ApostolicGroupMember $membership): RedirectResponse
    {
        $this->service->removeMembership($membership);

        return back()->with('success', db_trans('apostolic_group_member_removed_successfully'));
    }
}
