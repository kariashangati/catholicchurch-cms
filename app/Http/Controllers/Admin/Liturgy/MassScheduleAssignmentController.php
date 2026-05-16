<?php

namespace App\Http\Controllers\Admin\Liturgy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liturgy\StoreMassScheduleAssignmentRequest;
use App\Http\Requests\Liturgy\UpdateMassScheduleAssignmentRequest;
use App\Models\LeadershipAssignment;
use App\Models\MassScheduleAssignment;

class MassScheduleAssignmentController extends Controller
{
    public function store(StoreMassScheduleAssignmentRequest $request)
    {
        MassScheduleAssignment::create($this->normalizePayload($request->validated()));

        return redirect()->route('liturgy.mass-schedules.index')
            ->with('success', db_trans('mass_schedule_assignment_created_successfully'));
    }

    public function update(UpdateMassScheduleAssignmentRequest $request, MassScheduleAssignment $mass_schedule_assignment)
    {
        $mass_schedule_assignment->update($this->normalizePayload($request->validated()));

        return redirect()->route('liturgy.mass-schedules.index')
            ->with('success', db_trans('mass_schedule_assignment_updated_successfully'));
    }

    public function destroy(MassScheduleAssignment $mass_schedule_assignment)
    {
        $mass_schedule_assignment->delete();

        return redirect()->route('liturgy.mass-schedules.index')
            ->with('success', db_trans('mass_schedule_assignment_deleted_successfully'));
    }

    protected function normalizePayload(array $validated): array
    {
        $map = [
            'member' => \App\Models\Member::class,
            'jumuiya' => \App\Models\Jumuiya::class,
            'kanda' => \App\Models\Kanda::class,
            'apostolic_group' => \App\Models\ApostolicGroup::class,
            'leadership_assignment' => LeadershipAssignment::class,
        ];

        $validated['assignable_type'] = $map[$validated['assignable_type']];

        return $validated;
    }
}
