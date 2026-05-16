<?php

namespace App\Services\Leadership;

use App\Models\LeadershipAssignment;
use App\Models\LeadershipPosition;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadershipService
{
    public function dashboardData(): array
    {
        $activeStatus = LeadershipAssignment::STATUS_ACTIVE;

        $summary = [
            'active_leaders' => LeadershipAssignment::where('status', $activeStatus)->count(),
            'parish_leaders' => LeadershipAssignment::where('status', $activeStatus)->where('scope_type', 'parish')->count(),
            'kanda_leaders' => LeadershipAssignment::where('status', $activeStatus)->where('scope_type', 'kanda')->count(),
            'jumuiya_leaders' => LeadershipAssignment::where('status', $activeStatus)->where('scope_type', 'jumuiya')->count(),
            'positions' => LeadershipPosition::where('is_active', true)->count(),
        ];

        $leadersByPosition = LeadershipAssignment::query()
            ->select('leadership_position_id', DB::raw('COUNT(*) as total'))
            ->where('status', $activeStatus)
            ->groupBy('leadership_position_id')
            ->with('position:id,name')
            ->get();

        $recentAssignments = LeadershipAssignment::query()
            ->with(['member', 'position', 'jumuiya', 'kanda'])
            ->latest()
            ->take(50)
            ->get();

        return compact('summary', 'leadersByPosition', 'recentAssignments');
    }

    public function createPosition(array $data): LeadershipPosition
    {
        return LeadershipPosition::create($this->normalizePositionData($data));
    }

    public function updatePosition(LeadershipPosition $position, array $data): LeadershipPosition
    {
        $position->update($this->normalizePositionData($data));
        return $position->refresh();
    }

    public function createAssignment(array $data, int $appointedBy): LeadershipAssignment
    {
        return DB::transaction(function () use ($data, $appointedBy) {
            $payload = $this->normalizeAssignmentData($data);
            $payload['appointed_by'] = $appointedBy;
            $assignment = LeadershipAssignment::create($payload);
            $this->syncMappedRole($assignment);
            return $assignment;
        });
    }

    public function updateAssignment(LeadershipAssignment $assignment, array $data): LeadershipAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $oldUser = $assignment->user;
            $oldRole = $assignment->position?->auto_role_name;

            $assignment->update($this->normalizeAssignmentData($data));
            $assignment->refresh()->load(['user', 'position']);

            if ($oldUser && $oldRole && $oldUser->id !== $assignment->user_id && $oldUser->hasRole($oldRole)) {
                $oldUser->removeRole($oldRole);
            }

            $this->syncMappedRole($assignment);
            return $assignment;
        });
    }

    public function deleteAssignment(LeadershipAssignment $assignment): void
    {
        DB::transaction(function () use ($assignment) {
            $user = $assignment->user;
            $role = $assignment->position?->auto_role_name;
            if ($user && $role && $user->hasRole($role)) {
                $user->removeRole($role);
            }
            $assignment->delete();
        });
    }

    public function syncMappedRole(LeadershipAssignment $assignment): void
    {
        $assignment->loadMissing(['position', 'user']);
        if (! $assignment->user || ! $assignment->position?->auto_role_name) {
            return;
        }

        if (LeadershipAssignment::normalizeStatus($assignment->status) !== LeadershipAssignment::STATUS_ACTIVE) {
            $assignment->user->removeRole($assignment->position->auto_role_name);
            return;
        }

        $assignment->user->assignRole($assignment->position->auto_role_name);
    }

    public function dashboardExportData(): array
    {
        $data = $this->dashboardData();

        return [
            'pageTitle' => db_trans('leadership_dashboard'),
            'reportTitle' => db_trans('leadership_dashboard'),
            'reportType' => 'dashboard',
            'summary' => $data['summary'],
            'leadersByPosition' => $data['leadersByPosition'],
            'recentAssignments' => $data['recentAssignments'],
            'assignments' => collect(),
            'positions' => collect(),
            'metaItems' => [
                ['label' => db_trans('active_leaders'), 'value' => number_format((int) data_get($data, 'summary.active_leaders', 0))],
                ['label' => db_trans('parish_leaders'), 'value' => number_format((int) data_get($data, 'summary.parish_leaders', 0))],
                ['label' => db_trans('kanda_leaders'), 'value' => number_format((int) data_get($data, 'summary.kanda_leaders', 0))],
                ['label' => db_trans('leadership_positions'), 'value' => number_format((int) data_get($data, 'summary.positions', 0))],
            ],
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function assignmentsExportData(): array
    {
        $assignments = LeadershipAssignment::query()
            ->with(['member', 'user', 'position', 'kanda', 'jumuiya', 'apostolicGroup'])
            ->latest()
            ->get();

        return [
            'pageTitle' => db_trans('leadership_assignments'),
            'reportTitle' => db_trans('leadership_assignments'),
            'reportType' => 'assignments',
            'summary' => [],
            'leadersByPosition' => collect(),
            'recentAssignments' => collect(),
            'assignments' => $assignments,
            'positions' => collect(),
            'metaItems' => [
                ['label' => db_trans('records'), 'value' => number_format($assignments->count())],
                ['label' => db_trans('active'), 'value' => number_format($assignments->where('status', LeadershipAssignment::STATUS_ACTIVE)->count())],
                ['label' => db_trans('inactive'), 'value' => number_format($assignments->where('status', '!=', LeadershipAssignment::STATUS_ACTIVE)->count())],
                ['label' => db_trans('report_date'), 'value' => now()->translatedFormat('d F Y')],
            ],
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function positionsExportData(): array
    {
        $positions = LeadershipPosition::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return [
            'pageTitle' => db_trans('leadership_positions'),
            'reportTitle' => db_trans('leadership_positions'),
            'reportType' => 'positions',
            'summary' => [],
            'leadersByPosition' => collect(),
            'recentAssignments' => collect(),
            'assignments' => collect(),
            'positions' => $positions,
            'metaItems' => [
                ['label' => db_trans('records'), 'value' => number_format($positions->count())],
                ['label' => db_trans('active'), 'value' => number_format($positions->where('is_active', true)->count())],
                ['label' => db_trans('inactive'), 'value' => number_format($positions->where('is_active', false)->count())],
                ['label' => db_trans('report_date'), 'value' => now()->translatedFormat('d F Y')],
            ],
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function dashboardExportSheets(array $data): array
    {
        return [
            db_trans('leadership_positions') => $this->leadersByPositionRows(collect($data['leadersByPosition'] ?? [])),
            db_trans('recent_leadership_activity') => $this->recentAssignmentsRows(collect($data['recentAssignments'] ?? [])),
        ];
    }

    public function assignmentsExportSheets(array $data): array
    {
        return [
            db_trans('leadership_assignments') => $this->assignmentsRows(collect($data['assignments'] ?? [])),
        ];
    }

    public function positionsExportSheets(array $data): array
    {
        return [
            db_trans('leadership_positions') => $this->positionsRows(collect($data['positions'] ?? [])),
        ];
    }

    protected function leadersByPositionRows(Collection $rows): array
    {
        $exportRows = [[db_trans('position'), db_trans('active_leaders')]];

        foreach ($rows as $row) {
            $exportRows[] = [
                $row->position?->name ?? '—',
                (int) ($row->total ?? 0),
            ];
        }

        return $exportRows;
    }

    protected function recentAssignmentsRows(Collection $assignments): array
    {
        $exportRows = [[db_trans('member'), db_trans('position'), db_trans('scope'), db_trans('status')]];

        foreach ($assignments as $assignment) {
            $exportRows[] = [
                $assignment->member?->full_name ?? '—',
                $assignment->position?->name ?? '—',
                $this->assignmentScopeLabel($assignment),
                $assignment->status_label ?? $assignment->status ?? '—',
            ];
        }

        return $exportRows;
    }

    protected function assignmentsRows(Collection $assignments): array
    {
        $exportRows = [[
            '#',
            db_trans('member'),
            db_trans('position'),
            db_trans('scope'),
            db_trans('started_at'),
            db_trans('ended_at'),
            db_trans('user'),
            db_trans('status'),
        ]];

        foreach ($assignments->values() as $index => $assignment) {
            $exportRows[] = [
                $index + 1,
                $assignment->member?->full_name ?? '—',
                $assignment->position?->name ?? '—',
                $this->assignmentScopeLabel($assignment),
                optional($assignment->started_at)->format('Y-m-d') ?: '—',
                optional($assignment->ended_at)->format('Y-m-d') ?: '—',
                $assignment->user?->name ?? '—',
                $assignment->status_label ?? $assignment->status ?? '—',
            ];
        }

        return $exportRows;
    }

    protected function positionsRows(Collection $positions): array
    {
        $exportRows = [[
            '#',
            db_trans('name'),
            db_trans('scope'),
            db_trans('committee_type'),
            db_trans('auto_role'),
            db_trans('display_order'),
            db_trans('status'),
        ]];

        foreach ($positions->values() as $index => $position) {
            $exportRows[] = [
                $index + 1,
                $position->name,
                db_trans($position->level_type),
                db_trans($position->committee_type),
                $position->auto_role_name ?: '—',
                (int) ($position->display_order ?? 0),
                $position->is_active ? db_trans('active') : db_trans('inactive'),
            ];
        }

        return $exportRows;
    }

    protected function assignmentScopeLabel(LeadershipAssignment $assignment): string
    {
        return $assignment->jumuiya?->name
            ?? $assignment->kanda?->name
            ?? $assignment->apostolicGroup?->name
            ?? ($assignment->scope_label ?: db_trans($assignment->scope_type));
    }

    protected function normalizePositionData(array $data): array
    {
        $name = trim((string) $data['name']);
        return [
            'name' => $name,
            'slug' => Arr::get($data, 'slug') ?: Str::slug($name),
            'level_type' => $data['level_type'],
            'committee_type' => $data['committee_type'],
            'auto_role_name' => Arr::get($data, 'auto_role_name') ?: null,
            'display_order' => (int) Arr::get($data, 'display_order', 0),
            'is_system' => (bool) Arr::get($data, 'is_system', false),
            'is_active' => (bool) Arr::get($data, 'is_active', true),
            'description' => Arr::get($data, 'description'),
        ];
    }

    protected function normalizeAssignmentData(array $data): array
    {
        return [
            'member_id' => $data['member_id'],
            'user_id' => Arr::get($data, 'user_id'),
            'leadership_position_id' => $data['leadership_position_id'],
            'kanda_id' => Arr::get($data, 'kanda_id'),
            'jumuiya_id' => Arr::get($data, 'jumuiya_id'),
            'apostolic_group_id' => Arr::get($data, 'apostolic_group_id'),
            'scope_type' => $data['scope_type'],
            'scope_label' => Arr::get($data, 'scope_label'),
            'started_at' => $data['started_at'],
            'ended_at' => Arr::get($data, 'ended_at'),
            'status' => LeadershipAssignment::normalizeStatus($data['status'] ?? LeadershipAssignment::STATUS_ACTIVE),
            'notes' => Arr::get($data, 'notes'),
        ];
    }
}
