<?php

namespace App\Services\ApostolicGroup;

use App\Models\ApostolicGroup;
use Illuminate\Support\Collection;
use App\Models\ApostolicGroupMember;
use App\Models\Member;
use App\Models\User;
use App\Models\Kanda;
use App\Models\Jumuiya;
use App\Models\Familia;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApostolicGroupService
{
	
	public function getExportRows(User $user): Collection
{
    return ApostolicGroup::query()
        ->with(['leader'])
        ->withCount([
            'groupMembers as total_members_count',
            'groupMembers as active_members_count' => fn ($q) => $q->where('status', 'active'),
        ])
        ->orderBy('name')
        ->get()
        ->map(function (ApostolicGroup $group, int $index) {
            $ruleLabel = match ($group->membership_rule_type) {
                'gender' => db_trans('gender_rule'),
                'family_role' => db_trans('family_role_rule'),
                default => db_trans('manual_rule'),
            };

            $meetingDayLabel = filled($group->meeting_day)
                ? db_trans(Str::lower($group->meeting_day))
                : null;

            $meetingTimeLabel = $group->meeting_time
                ? \Carbon\Carbon::parse($group->meeting_time)->format('H:i')
                : null;

            $meetingLabel = trim(collect([
                $meetingDayLabel,
                $meetingTimeLabel,
            ])->filter()->implode(' · '));

            return (object) [
                'sn' => $index + 1,
                'name' => $group->name,
                'leader_name' => $group->leader?->full_name ?? db_trans('no_leader_assigned'),
                'membership_rule_label' => $ruleLabel,
                'meeting_label' => $meetingLabel !== '' ? $meetingLabel : db_trans('not_set'),
                'members_count' => (int) $group->total_members_count,
                'status_label' => $group->is_active ? db_trans('active') : db_trans('inactive'),
            ];
        })
        ->values();
}


public function getMembersExportRows(ApostolicGroup $group, User $user): Collection
{
    $group->load(['leader', 'assistantLeader', 'patron']);

    return ApostolicGroupMember::query()
        ->with(['member.familia.jumuiya'])
        ->where('apostolic_group_id', $group->id)
        ->orderBy('joined_at')
        ->get()
        ->map(function (ApostolicGroupMember $membership, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member_name' => $membership->member?->full_name ?? '—',
                'familia_name' => $membership->member?->familia?->name ?? '—',
                'jumuiya_name' => $membership->member?->familia?->jumuiya?->name ?? '—',
                'role_label' => db_trans($membership->role),
                'status_label' => $membership->status === 'active'
                    ? db_trans('active')
                    : db_trans('inactive'),
                'joined_at_label' => optional($membership->joined_at)->format('d M Y') ?: '—',
            ];
        })
        ->values();
}



public function getMembersExportPdfData(ApostolicGroup $group, User $user): array
{
    $rows = $this->getMembersExportRows($group, $user);

    return [
        'pageTitle' => db_trans('group_members'),
        'reportTitle' => db_trans('apostolic_group_members_report_title') . ' ' . $group->name,
        'metaItems' => [
            [
                'label' => db_trans('group'),
                'value' => $group->name,
            ],
            [
                'label' => db_trans('total_members'),
                'value' => number_format($rows->count()),
            ],
            [
                'label' => db_trans('active_members'),
                'value' => number_format($rows->where('status_label', db_trans('active'))->count()),
            ],
            [
                'label' => db_trans('inactive_members'),
                'value' => number_format($rows->where('status_label', db_trans('inactive'))->count()),
            ],
        ],
        'rows' => $rows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}


public function getExportPdfData(User $user): array
{
    $rows = $this->getExportRows($user);

    return [
        'pageTitle' => db_trans('apostolic_groups'),
        'reportTitle' => db_trans('apostolic_groups_list_report'),
        'metaItems' => [
            [
                'label' => db_trans('apostolic_groups'),
                'value' => number_format($rows->count()),
            ],
            [
                'label' => db_trans('active_apostolic_groups'),
                'value' => number_format($rows->where('status_label', db_trans('active'))->count()),
            ],
            [
                'label' => db_trans('inactive_apostolic_groups'),
                'value' => number_format($rows->where('status_label', db_trans('inactive'))->count()),
            ],
            [
                'label' => db_trans('members'),
                'value' => number_format($rows->sum('members_count')),
            ],
        ],
        'rows' => $rows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}




	
    public function getIndexData(User $user, array $filters = []): array
    {
        $groups = ApostolicGroup::query()
            ->with(['leader', 'assistantLeader', 'patron'])
            ->withCount([
                'groupMembers as total_members_count',
                'groupMembers as active_members_count' => fn ($q) => $q->where('status', 'active'),
            ])
            ->latest()
            ->get();

        $normalizedFilters = $this->normalizeIndexFilters($filters);
        $growthChart = $this->buildGrowthChart(
            $normalizedFilters['year'],
            $normalizedFilters['month']
        );

        $recentGroups = $groups->take(5);
       $members = Member::query()
    ->with('familia.jumuiya.kanda')
    ->where('is_active', true)
    ->orderBy('first_name')
    ->orderBy('middle_name')
    ->orderBy('last_name')
    ->get();

        $membershipRoleCounts = ApostolicGroupMember::query()
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        $ruleTypeCounts = $groups->groupBy(fn ($group) => $group->membership_rule_type ?: 'manual')
            ->map->count();

   $meetingDayCounts = $groups->filter(fn ($group) => filled($group->meeting_day))
    ->groupBy(function ($group) {
        return match ($group->meeting_day) {
            'Monday' => db_trans('monday'),
            'Tuesday' => db_trans('tuesday'),
            'Wednesday' => db_trans('wednesday'),
            'Thursday' => db_trans('thursday'),
            'Friday' => db_trans('friday'),
            'Saturday' => db_trans('saturday'),
            'Sunday' => db_trans('sunday'),
            default => $group->meeting_day ?: db_trans('not_set'),
        };
    })
    ->map->count();

        $groupsWithoutLeader = $groups->filter(fn ($group) => blank($group->leader_member_id))->count();
        $groupsWithMeetings = $groups->filter(fn ($group) => filled($group->meeting_day) || filled($group->meeting_time) || filled($group->meeting_location))->count();

        return [
            'pageTitle' => db_trans('apostolic_groups'),
            'groups' => $groups,
            'recentGroups' => $recentGroups,
            'availableMembers' => $members,
            'availableKandas' => Kanda::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'availableJumuiyas' => Jumuiya::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
          'availableFamilias' => Familia::query()
    ->with('jumuiya')
    ->where('is_active', true)
    ->orderBy('name')
    ->get(),
            'stats' => [
                'total_groups' => $groups->count(),
                'active_groups' => $groups->where('is_active', true)->count(),
                'inactive_groups' => $groups->where('is_active', false)->count(),
                'active_memberships' => $groups->sum('active_members_count'),
                'groups_without_leaders' => $groupsWithoutLeader,
                'groups_with_meetings' => $groupsWithMeetings,
                'manual_rule_groups' => $groups->where('membership_rule_type', 'manual')->count(),
                'auto_rule_groups' => $groups->whereIn('membership_rule_type', ['gender', 'family_role'])->count(),
            ],
            'filters' => $normalizedFilters,
            'filterOptions' => [
                'years' => range((int) now()->year - 5, (int) now()->year + 1),
                'months' => collect(range(1, 12))->map(fn ($month) => [
                    'value' => $month,
                    'label' => Carbon::create(null, $month, 1)->translatedFormat('F'),
                ])->values(),
            ],
            'chart' => $growthChart,
            'roleChart' => [
                'labels' => $this->formatRoleLabels(array_keys($membershipRoleCounts->toArray())),
                'data' => array_values($membershipRoleCounts->toArray()),
            ],
            'ruleChart' => [
                'labels' => $this->formatRuleTypeLabels(array_keys($ruleTypeCounts->toArray())),
                'data' => array_values($ruleTypeCounts->toArray()),
            ],
            'meetingChart' => [
                'labels' => array_keys($meetingDayCounts->toArray()),
                'data' => array_values($meetingDayCounts->toArray()),
            ],
        ];
    }

    public function getShowData(ApostolicGroup $group): array
    {
        $group->load(['leader', 'assistantLeader', 'patron']);

        $memberships = ApostolicGroupMember::query()
            ->with(['member.familia.jumuiya'])
            ->where('apostolic_group_id', $group->id)
            ->latest()
            ->get();

        $members = $memberships->pluck('member')->filter();

        $familiaCount = $members->pluck('familia_id')->filter()->unique()->count();
        $jumuiyaCount = $members->map(fn ($member) => $member?->familia?->jumuiya_id)->filter()->unique()->count();
        $maleMembers = $members->filter(fn ($member) => strtolower((string) $member->gender) === 'male')->count();
        $femaleMembers = $members->filter(fn ($member) => strtolower((string) $member->gender) === 'female')->count();
        $activeMembers = $memberships->where('status', 'active')->count();
        $inactiveMembers = $memberships->where('status', 'inactive')->count();
        $leadershipFilled = collect([$group->leader_member_id, $group->assistant_leader_member_id, $group->patron_member_id])->filter()->count();
        $profileReadiness = (int) round((collect([
            filled($group->leader_member_id),
            filled($group->meeting_day) || filled($group->meeting_time) || filled($group->meeting_location),
            filled($group->membership_rule_type),
            filled($group->founded_on),
            $activeMembers > 0,
        ])->filter()->count() / 5) * 100);

        $roleBreakdown = $memberships->groupBy('role')->map->count();

        return [
            'pageTitle' => db_trans('apostolic_group_details'),
            'group' => $group,
            'memberships' => $memberships,
            'availableMembers' => Member::query()->with('familia.jumuiya')->where('is_active', true)->orderBy('first_name')->get(),
            'stats' => [
                'total_members' => $memberships->count(),
                'active_members' => $activeMembers,
                'inactive_members' => $inactiveMembers,
                'male_members' => $maleMembers,
                'female_members' => $femaleMembers,
                'familias_represented' => $familiaCount,
                'jumuiyas_represented' => $jumuiyaCount,
                'leadership_filled' => $leadershipFilled,
                'profile_readiness' => $profileReadiness,
            ],
            'memberChart' => [
                'labels' => [db_trans('male_members'), db_trans('female_members')],
                'data' => [$maleMembers, $femaleMembers],
            ],
            'statusChart' => [
                'labels' => [db_trans('active_members'), db_trans('inactive_members')],
                'data' => [$activeMembers, $inactiveMembers],
            ],
            'roleChart' => [
                'labels' => $this->formatRoleLabels(array_keys($roleBreakdown->toArray())),
                'data' => array_values($roleBreakdown->toArray()),
            ],
        ];
    }

    public function store(array $data): ApostolicGroup
    {
        $imagePath = $this->storeImage($data['image'] ?? null);

        return ApostolicGroup::create([
            'name' => Str::upper(trim($data['name'])),
            'code' => Str::upper(trim($data['code'])),
            'slug' => Str::slug($data['name']),
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'leader_member_id' => $data['leader_member_id'] ?? null,
            'assistant_leader_member_id' => $data['assistant_leader_member_id'] ?? null,
            'patron_member_id' => $data['patron_member_id'] ?? null,
            'membership_rule_type' => $data['membership_rule_type'] ?? 'manual',
            'membership_rule_value' => $data['membership_rule_value'] ?? null,
            'image' => $imagePath,
            'founded_on' => $data['founded_on'] ?? null,
            'meeting_day' => $data['meeting_day'] ?? null,
            'meeting_time' => $data['meeting_time'] ?? null,
            'meeting_location' => $data['meeting_location'] ?? null,
        ]);
    }

    public function update(ApostolicGroup $group, array $data): ApostolicGroup
    {
        $imagePath = $group->image;

        if (!empty($data['image'])) {
            if ($group->image && Storage::disk('public')->exists($group->image)) {
                Storage::disk('public')->delete($group->image);
            }
            $imagePath = $this->storeImage($data['image']);
        }

        $group->update([
            'name' => Str::upper(trim($data['name'])),
            'code' => Str::upper(trim($data['code'])),
            'slug' => Str::slug($data['name']),
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'leader_member_id' => $data['leader_member_id'] ?? null,
            'assistant_leader_member_id' => $data['assistant_leader_member_id'] ?? null,
            'patron_member_id' => $data['patron_member_id'] ?? null,
            'membership_rule_type' => $data['membership_rule_type'] ?? 'manual',
            'membership_rule_value' => $data['membership_rule_value'] ?? null,
            'image' => $imagePath,
            'founded_on' => $data['founded_on'] ?? null,
            'meeting_day' => $data['meeting_day'] ?? null,
            'meeting_time' => $data['meeting_time'] ?? null,
            'meeting_location' => $data['meeting_location'] ?? null,
        ]);

        return $group->refresh();
    }

    public function delete(ApostolicGroup $group): bool
    {
        if ($group->image && Storage::disk('public')->exists($group->image)) {
            Storage::disk('public')->delete($group->image);
        }

        return (bool) $group->delete();
    }

    public function addMembers(ApostolicGroup $group, array $memberIds, array $payload, User $user): void
    {
        DB::transaction(function () use ($group, $memberIds, $payload, $user) {
            foreach ($memberIds as $memberId) {
                ApostolicGroupMember::updateOrCreate(
                    [
                        'apostolic_group_id' => $group->id,
                        'member_id' => $memberId,
                    ],
                    [
                        'role' => $payload['role'] ?? 'member',
                        'status' => $payload['status'] ?? 'active',
                        'joined_at' => $payload['joined_at'] ?? now()->toDateString(),
                        'left_at' => ($payload['status'] ?? 'active') === 'inactive' ? ($payload['left_at'] ?? now()->toDateString()) : null,
                        'notes' => $payload['notes'] ?? null,
                        'added_by' => $user->id,
                    ]
                );
            }
        });
    }

    public function updateMembership(ApostolicGroupMember $membership, array $data): ApostolicGroupMember
    {
        $membership->update([
            'role' => $data['role'],
            'status' => $data['status'],
            'joined_at' => $data['joined_at'] ?? $membership->joined_at,
            'left_at' => $data['status'] === 'inactive' ? ($data['left_at'] ?? now()->toDateString()) : null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $membership->refresh();
    }

    public function removeMembership(ApostolicGroupMember $membership): bool
    {
        return (bool) $membership->delete();
    }

    public function syncRuleMembers(ApostolicGroup $group, User $user): int
    {
        $query = Member::query()->where('is_active', true);

        if ($group->membership_rule_type === 'gender' && filled($group->membership_rule_value)) {
            $query->whereRaw('LOWER(gender) = ?', [strtolower($group->membership_rule_value)]);
        } elseif ($group->membership_rule_type === 'family_role' && filled($group->membership_rule_value)) {
            $query->whereRaw('LOWER(family_role) = ?', [strtolower($group->membership_rule_value)]);
        } else {
            return 0;
        }

        $memberIds = $query->pluck('id');

        $count = 0;
        DB::transaction(function () use ($group, $memberIds, $user, &$count) {
            foreach ($memberIds as $memberId) {
                $membership = ApostolicGroupMember::firstOrCreate(
                    [
                        'apostolic_group_id' => $group->id,
                        'member_id' => $memberId,
                    ],
                    [
                        'role' => 'member',
                        'status' => 'active',
                        'joined_at' => now()->toDateString(),
                        'added_by' => $user->id,
                    ]
                );

                if ($membership->wasRecentlyCreated) {
                    $count++;
                }
            }
        });

        return $count;
    }

    protected function storeImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store('apostolic-groups', 'public');
    }

    protected function formatRoleLabels(array $roles): array
    {
        return collect($roles)->map(function ($role) {
            return db_trans($role) !== $role ? db_trans($role) : Str::headline((string) $role);
        })->values()->all();
    }

    protected function formatRuleTypeLabels(array $types): array
    {
        return collect($types)->map(function ($type) {
            return match ($type) {
                'manual' => db_trans('manual_rule'),
                'gender' => db_trans('gender_rule'),
                'family_role' => db_trans('family_role_rule'),
                default => Str::headline((string) $type),
            };
        })->values()->all();
    }

    protected function normalizeIndexFilters(array $filters): array
    {
        $year = isset($filters['year']) && $filters['year']
            ? (int) $filters['year']
            : (int) now()->year;

        $month = isset($filters['month']) && $filters['month'] !== ''
            ? (int) $filters['month']
            : null;

        if ($month && ($month < 1 || $month > 12)) {
            $month = null;
        }

        return [
            'year' => $year,
            'month' => $month,
        ];
    }

    protected function buildGrowthChart(int $year, ?int $month = null): array
    {
        $labels = [];
        $data = [];

        if ($month) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $labels[] = $date->format('d M');

                $data[] = ApostolicGroup::query()
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'period_label' => $start->translatedFormat('F Y'),
            ];
        }

        for ($currentMonth = 1; $currentMonth <= 12; $currentMonth++) {
            $date = Carbon::create($year, $currentMonth, 1);

            $labels[] = $date->translatedFormat('M');

            $data[] = ApostolicGroup::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $currentMonth)
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'period_label' => (string) $year,
        ];
    }
}