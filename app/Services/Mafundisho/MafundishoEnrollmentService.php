<?php

namespace App\Services\Mafundisho;

use App\Models\MafundishoEnrollment;
use App\Models\Member;
use App\Models\TeachingType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MafundishoEnrollmentService
{
    public function getSummaryData(User $user): array
    {
        $baseEnrollments = $this->scopedEnrollmentQuery($user);
        $baseMembers = $this->scopedEligibleMembersBaseQuery($user);
        $teachingTypes = $this->activeTeachingTypes();

        $summaryByType = $teachingTypes
            ->map(function (TeachingType $teachingType) use ($baseEnrollments, $user): array {
                $typedQuery = (clone $baseEnrollments)
                    ->where('mafundisho_enrollments.teaching_type_id', $teachingType->id);

                $eligibleCount = $this->eligibleMembersForTeachingType($user, $teachingType)
                    ->count('members.id');

                $completed = (clone $typedQuery)
                    ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_COMPLETED)
                    ->count('mafundisho_enrollments.id');

                $total = (clone $typedQuery)->count('mafundisho_enrollments.id');

                return [
                    'type' => $teachingType->slug,
                    'teaching_type_id' => $teachingType->id,
                    'label' => $teachingType->name,
                    'total' => $total,
                    'active' => (clone $typedQuery)
                        ->whereIn('mafundisho_enrollments.status', [
                            MafundishoEnrollment::STATUS_CONTINUING,
                            MafundishoEnrollment::STATUS_ACTIVE,
                        ])
                        ->count('mafundisho_enrollments.id'),
                    'completed' => $completed,
                    'failed' => (clone $typedQuery)
                        ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_FAILED)
                        ->count('mafundisho_enrollments.id'),
                    'repeated' => (clone $typedQuery)
                        ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_REPEATED)
                        ->count('mafundisho_enrollments.id'),
                    'withdrawn' => (clone $typedQuery)
                        ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_WITHDRAWN)
                        ->count('mafundisho_enrollments.id'),
                    'eligible' => $eligibleCount,
                    'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
                ];
            })
            ->values();

        $chartRows = (clone $baseEnrollments)
            ->toBase()
            ->selectRaw('mafundisho_enrollments.year, COUNT(*) as total')
            ->groupBy('mafundisho_enrollments.year')
            ->orderBy('mafundisho_enrollments.year')
            ->get();

        $statusChartRows = (clone $baseEnrollments)
            ->toBase()
            ->selectRaw('mafundisho_enrollments.status, COUNT(*) as total')
            ->groupBy('mafundisho_enrollments.status')
            ->pluck('total', 'status');

        $recentEnrollments = (clone $baseEnrollments)
            ->select('mafundisho_enrollments.*')
            ->with([
                'teachingType:id,name,slug,sacrament_key,requires_partner_info',
                'member:id,first_name,middle_name,last_name,member_code,phone,gender,familia_id',
                'member.familia:id,name,jumuiya_id',
                'member.familia.jumuiya:id,name,kanda_id',
            ])
            ->latest('mafundisho_enrollments.created_at')
            ->take(10)
            ->get();

        $currentYear = (int) now()->year;
        $thisYearEnrollments = (clone $baseEnrollments)
            ->where('mafundisho_enrollments.year', $currentYear)
            ->count('mafundisho_enrollments.id');

        return [
            'stats' => [
                'total_students' => (clone $baseEnrollments)->count('mafundisho_enrollments.id'),
                'active_students' => (clone $baseEnrollments)
                    ->whereIn('mafundisho_enrollments.status', [
                        MafundishoEnrollment::STATUS_CONTINUING,
                        MafundishoEnrollment::STATUS_ACTIVE,
                    ])
                    ->count('mafundisho_enrollments.id'),
                'completed_students' => (clone $baseEnrollments)
                    ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_COMPLETED)
                    ->count('mafundisho_enrollments.id'),
                'failed_students' => (clone $baseEnrollments)
                    ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_FAILED)
                    ->count('mafundisho_enrollments.id'),
                'repeated_students' => (clone $baseEnrollments)
                    ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_REPEATED)
                    ->count('mafundisho_enrollments.id'),
                'withdrawn_students' => (clone $baseEnrollments)
                    ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_WITHDRAWN)
                    ->count('mafundisho_enrollments.id'),
                'eligible_members' => (clone $baseMembers)->count('members.id'),
                'this_year_enrollments' => $thisYearEnrollments,
            ],
            'teachingTypes' => $teachingTypes,
            'summaryByType' => $summaryByType,
            'recentEnrollments' => $recentEnrollments,
            'chart' => [
                'labels' => $chartRows->pluck('year')->map(fn ($year) => (string) $year)->values()->all(),
                'data' => $chartRows->pluck('total')->map(fn ($total) => (int) $total)->values()->all(),
            ],
            'statusChart' => [
                'labels' => $this->statusLabels()->values()->all(),
                'data' => [
                    (int) (($statusChartRows[MafundishoEnrollment::STATUS_CONTINUING] ?? 0) + ($statusChartRows[MafundishoEnrollment::STATUS_ACTIVE] ?? 0)),
                    (int) ($statusChartRows[MafundishoEnrollment::STATUS_COMPLETED] ?? 0),
                    (int) ($statusChartRows[MafundishoEnrollment::STATUS_FAILED] ?? 0),
                    (int) ($statusChartRows[MafundishoEnrollment::STATUS_REPEATED] ?? 0),
                    (int) ($statusChartRows[MafundishoEnrollment::STATUS_WITHDRAWN] ?? 0),
                ],
            ],
            'statusLabels' => $this->statusLabels(),
        ];
    }

    public function getTypeIndexData(User $user, string $type, ?int $year = null, ?string $status = null, ?int $month = null): array
    {
        $teachingType = $this->findTeachingTypeBySlug($type);
        $this->ensureValidStatus($status);

        $availableYears = $this->availableYears($user, $teachingType->slug);

        if ($year === null) {
            $year = $availableYears->first() ?: (int) now()->year;
        }

        $baseTypeQuery = $this->scopedEnrollmentQuery($user)
            ->where('mafundisho_enrollments.teaching_type_id', $teachingType->id)
            ->where('mafundisho_enrollments.year', $year);

        if ($month && $month >= 1 && $month <= 12) {
            $baseTypeQuery->whereMonth(
                DB::raw('COALESCE(mafundisho_enrollments.started_at, mafundisho_enrollments.created_at)'),
                $month
            );
        }

        $enrollments = (clone $baseTypeQuery)
            ->when($status, function (Builder $query) use ($status): void {
                if ($status === MafundishoEnrollment::STATUS_CONTINUING) {
                    $query->whereIn('mafundisho_enrollments.status', [
                        MafundishoEnrollment::STATUS_CONTINUING,
                        MafundishoEnrollment::STATUS_ACTIVE,
                    ]);
                } else {
                    $query->where('mafundisho_enrollments.status', $status);
                }
            })
            ->select('mafundisho_enrollments.*')
            ->with([
                'teachingType:id,name,slug,sacrament_key,requires_partner_info',
                'member:id,first_name,middle_name,last_name,member_code,phone,gender,familia_id',
                'member.familia:id,name,jumuiya_id',
                'member.familia.jumuiya:id,name,kanda_id',
                'member.familia.jumuiya.kanda:id,name',
            ])
            ->latest('mafundisho_enrollments.created_at')
            ->get();

        $members = $this->eligibleMembersForTeachingType($user, $teachingType)
            ->select([
                'members.id',
                'members.first_name',
                'members.middle_name',
                'members.last_name',
                'members.member_code',
                'members.phone',
                'members.gender',
                'members.familia_id',
            ])
            ->with([
                'familia:id,name,jumuiya_id',
                'familia.jumuiya:id,name,kanda_id',
            ])
            ->orderBy('members.first_name')
            ->orderBy('members.last_name')
            ->get();

        $statusCounts = [
            MafundishoEnrollment::STATUS_CONTINUING => (clone $baseTypeQuery)
                ->whereIn('mafundisho_enrollments.status', [
                    MafundishoEnrollment::STATUS_CONTINUING,
                    MafundishoEnrollment::STATUS_ACTIVE,
                ])
                ->count('mafundisho_enrollments.id'),
            MafundishoEnrollment::STATUS_COMPLETED => (clone $baseTypeQuery)
                ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_COMPLETED)
                ->count('mafundisho_enrollments.id'),
            MafundishoEnrollment::STATUS_FAILED => (clone $baseTypeQuery)
                ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_FAILED)
                ->count('mafundisho_enrollments.id'),
            MafundishoEnrollment::STATUS_REPEATED => (clone $baseTypeQuery)
                ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_REPEATED)
                ->count('mafundisho_enrollments.id'),
            MafundishoEnrollment::STATUS_WITHDRAWN => (clone $baseTypeQuery)
                ->where('mafundisho_enrollments.status', MafundishoEnrollment::STATUS_WITHDRAWN)
                ->count('mafundisho_enrollments.id'),
        ];

        $stats = [
            'total' => (clone $baseTypeQuery)->count('mafundisho_enrollments.id'),
            'active' => $statusCounts[MafundishoEnrollment::STATUS_CONTINUING] ?? 0,
            'continuing' => $statusCounts[MafundishoEnrollment::STATUS_CONTINUING] ?? 0,
            'completed' => $statusCounts[MafundishoEnrollment::STATUS_COMPLETED] ?? 0,
            'failed' => $statusCounts[MafundishoEnrollment::STATUS_FAILED] ?? 0,
            'repeated' => $statusCounts[MafundishoEnrollment::STATUS_REPEATED] ?? 0,
            'withdrawn' => $statusCounts[MafundishoEnrollment::STATUS_WITHDRAWN] ?? 0,
            'eligible' => $members->count(),
            'eligible_not_enrolled' => max(
                $members->count() - ((clone $baseTypeQuery)->distinct('mafundisho_enrollments.member_id')->count('mafundisho_enrollments.member_id')),
                0
            ),
        ];

        $monthlyRows = (clone $baseTypeQuery)
            ->toBase()
            ->selectRaw('MONTH(COALESCE(mafundisho_enrollments.started_at, mafundisho_enrollments.created_at)) as month_no, COUNT(*) as total')
            ->groupBy('month_no')
            ->orderBy('month_no')
            ->get();

        return [
            'type' => $teachingType->slug,
            'teachingType' => $teachingType,
            'teachingTypes' => $this->activeTeachingTypes(),
            'typeLabel' => $teachingType->name,
            'year' => $year,
            'month' => $month,
            'status' => $status,
            'enrollments' => $enrollments,
            'members' => $members,
            'availableYears' => $availableYears,
            'statusCounts' => $statusCounts,
            'stats' => $stats,
            'statusLabels' => $this->statusLabels(),
            'statusChart' => [
                'labels' => $this->statusLabels()->values()->all(),
                'data' => [
                    (int) ($statusCounts[MafundishoEnrollment::STATUS_CONTINUING] ?? 0),
                    (int) ($statusCounts[MafundishoEnrollment::STATUS_COMPLETED] ?? 0),
                    (int) ($statusCounts[MafundishoEnrollment::STATUS_FAILED] ?? 0),
                    (int) ($statusCounts[MafundishoEnrollment::STATUS_REPEATED] ?? 0),
                    (int) ($statusCounts[MafundishoEnrollment::STATUS_WITHDRAWN] ?? 0),
                ],
            ],
            'monthlyChart' => [
                'labels' => $monthlyRows->pluck('month_no')->map(function ($month) {
                    return $month ? now()->startOfYear()->month((int) $month)->format('M') : db_trans('unknown');
                })->values()->all(),
                'data' => $monthlyRows->pluck('total')->map(fn ($total) => (int) $total)->values()->all(),
            ],
        ];
    }

    public function getShowData(User $user, MafundishoEnrollment $enrollment): array
    {
        $this->authorizeEnrollmentAccess($user, $enrollment);

        $enrollment->load([
            'teachingType:id,name,slug,sacrament_key,requires_partner_info',
            'member:id,first_name,middle_name,last_name,member_code,gender,phone,email,date_of_birth,familia_id',
            'member.familia:id,name,jumuiya_id',
            'member.familia.jumuiya:id,name,kanda_id',
            'member.familia.jumuiya.kanda:id,name',
        ]);

        return [
            'enrollment' => $enrollment,
            'typeLabel' => $enrollment->teaching_type_label,
        ];
    }

    public function store(array $validated, User $user): void
    {
        $teachingType = $this->resolveTeachingTypeFromValidated($validated);
        $year = (int) ($validated['year'] ?? now()->year);
        $status = $this->normalizeStatus($validated['status'] ?? MafundishoEnrollment::STATUS_CONTINUING);

        $memberIds = collect($validated['member_ids'] ?? $validated['members'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($memberIds->isEmpty()) {
            throw ValidationException::withMessages([
                'member_ids' => db_trans('validation_member_required'),
            ]);
        }

        $eligibleMembers = $this->eligibleMembersForTeachingType($user, $teachingType)
            ->whereIn('members.id', $memberIds)
            ->get()
            ->keyBy('id');

        if ($eligibleMembers->count() !== $memberIds->count()) {
            throw ValidationException::withMessages([
                'member_ids' => db_trans('validation_member_invalid'),
            ]);
        }

        DB::transaction(function () use ($validated, $memberIds, $eligibleMembers, $teachingType, $year, $status, $user): void {
            foreach ($memberIds as $memberId) {
                $member = $eligibleMembers->get($memberId);

        $enrollment = MafundishoEnrollment::query()
    ->where('member_id', $member->id)
    ->where('year', $year)
    ->where(function ($query) use ($teachingType) {
        $query->where('teaching_type_id', $teachingType->id)
            ->orWhere('type', $teachingType->slug);
    })
    ->first();

if ($enrollment) {
    $enrollment->update([
        'teaching_type_id' => $teachingType->id,
        'type' => $teachingType->slug,
        'status' => $status,
        'started_at' => $validated['started_at'] ?? null,
        'ended_at' => $validated['ended_at'] ?? null,
        'notes' => $validated['notes'] ?? null,
        'partner_name' => $validated['partner_name'] ?? null,
        'partner_jumuiya' => $validated['partner_jumuiya'] ?? null,
        'partner_phone' => $validated['partner_phone'] ?? null,
        'updated_by' => $user->id,
    ]);
} else {
    $enrollment = MafundishoEnrollment::create([
        'member_id' => $member->id,
        'teaching_type_id' => $teachingType->id,
        'year' => $year,
        'type' => $teachingType->slug,
        'status' => $status,
        'started_at' => $validated['started_at'] ?? null,
        'ended_at' => $validated['ended_at'] ?? null,
        'notes' => $validated['notes'] ?? null,
        'partner_name' => $validated['partner_name'] ?? null,
        'partner_jumuiya' => $validated['partner_jumuiya'] ?? null,
        'partner_phone' => $validated['partner_phone'] ?? null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
}

                $this->syncMemberSacramentStatus($member, $teachingType, $enrollment->status);
            }
        });
    }

    public function update(MafundishoEnrollment $enrollment, array $validated, User $user): void
    {
        $this->authorizeEnrollmentAccess($user, $enrollment);

        $teachingType = $this->resolveTeachingTypeFromValidated($validated);
        $status = $this->normalizeStatus($validated['status'] ?? MafundishoEnrollment::STATUS_CONTINUING);

        DB::transaction(function () use ($enrollment, $validated, $teachingType, $status, $user): void {
            $enrollment->update([
                'member_id' => (int) $validated['member_id'],
                'teaching_type_id' => $teachingType->id,
                'year' => (int) ($validated['year'] ?? now()->year),
                'type' => $teachingType->slug,
                'status' => $status,
                'started_at' => $validated['started_at'] ?? null,
                'ended_at' => $validated['ended_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'partner_name' => $validated['partner_name'] ?? null,
                'partner_jumuiya' => $validated['partner_jumuiya'] ?? null,
                'partner_phone' => $validated['partner_phone'] ?? null,
                'updated_by' => $user->id,
            ]);

            $member = Member::findOrFail($enrollment->member_id);
            $this->syncMemberSacramentStatus($member, $teachingType, $status);
        });
    }

    public function delete(MafundishoEnrollment $enrollment, User $user): void
    {
        $this->authorizeEnrollmentAccess($user, $enrollment);

        DB::transaction(function () use ($enrollment): void {
            $member = Member::findOrFail($enrollment->member_id);
            $teachingType = $enrollment->teachingType ?: $this->findTeachingTypeBySlug($enrollment->type);

            $enrollment->delete();

            $latestRelevantEnrollment = MafundishoEnrollment::query()
                ->where('member_id', $member->id)
                ->where('teaching_type_id', $teachingType->id)
                ->latest('year')
                ->latest('id')
                ->first();

            if ($latestRelevantEnrollment) {
                $this->syncMemberSacramentStatus($member, $teachingType, $latestRelevantEnrollment->status);
            } else {
                $this->resetMemberSacramentStatus($member, $teachingType);
            }
        });
    }

    public function availableYears(User $user, ?string $type = null): Collection
    {
        $query = $this->scopedEnrollmentQuery($user);

        if ($type) {
            $teachingType = $this->findTeachingTypeBySlug($type);
            $query->where('mafundisho_enrollments.teaching_type_id', $teachingType->id);
        }

        return $query
            ->toBase()
            ->select('mafundisho_enrollments.year')
            ->distinct()
            ->orderByDesc('mafundisho_enrollments.year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();
    }

    protected function scopedEnrollmentQuery(User $user): Builder
    {
        $query = MafundishoEnrollment::query()
            ->leftJoin('members', 'members.id', '=', 'mafundisho_enrollments.member_id')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id');

        if ($user->can('mafundisho.view-all')) {
            return $query;
        }

        if (!empty($user->jumuiya_id)) {
            return $query->where('jumuiyas.id', $user->jumuiya_id);
        }

        if (!empty($user->kanda_id)) {
            return $query->where('jumuiyas.kanda_id', $user->kanda_id);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedEligibleMembersBaseQuery(User $user): Builder
    {
        $query = Member::query()
            ->select('members.*')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id');

        if ($user->can('mafundisho.view-all')) {
            return $query;
        }

        if (!empty($user->jumuiya_id)) {
            return $query->where('jumuiyas.id', $user->jumuiya_id);
        }

        if (!empty($user->kanda_id)) {
            return $query->where('jumuiyas.kanda_id', $user->kanda_id);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function eligibleMembersForType(User $user, string $type): Builder
    {
        return $this->eligibleMembersForTeachingType($user, $this->findTeachingTypeBySlug($type));
    }

    protected function eligibleMembersForTeachingType(User $user, TeachingType $teachingType): Builder
    {
        $query = $this->scopedEligibleMembersBaseQuery($user);

        return match ($teachingType->sacrament_key) {
            TeachingType::SACRAMENT_COMMUNION => $query->where(function (Builder $builder): void {
                $builder->whereNull('members.has_communion')
                    ->orWhere('members.has_communion', false)
                    ->orWhere('members.has_communion', 0);
            }),
            TeachingType::SACRAMENT_CONFIRMATION => $query->where(function (Builder $builder): void {
                $builder->whereNull('members.has_confirmation')
                    ->orWhere('members.has_confirmation', false)
                    ->orWhere('members.has_confirmation', 0);
            }),
            TeachingType::SACRAMENT_MARRIAGE => $query->where(function (Builder $builder): void {
                $builder->whereNull('members.is_married')
                    ->orWhere('members.is_married', false)
                    ->orWhere('members.is_married', 0);
            }),
            default => $query,
        };
    }

    protected function syncMemberSacramentStatus(Member $member, TeachingType $teachingType, string $status): void
    {
        $column = $this->sacramentColumnForTeachingType($teachingType);

        if (!$column) {
            return;
        }

        $value = $status === MafundishoEnrollment::STATUS_COMPLETED;

        $member->forceFill([$column => $value])->save();
    }

    protected function resetMemberSacramentStatus(Member $member, TeachingType $teachingType): void
    {
        $column = $this->sacramentColumnForTeachingType($teachingType);

        if (!$column) {
            return;
        }

        $member->forceFill([$column => false])->save();
    }

    protected function sacramentColumnForTeachingType(TeachingType $teachingType): ?string
    {
        return match ($teachingType->sacrament_key) {
            TeachingType::SACRAMENT_COMMUNION => 'has_communion',
            TeachingType::SACRAMENT_CONFIRMATION => 'has_confirmation',
            TeachingType::SACRAMENT_MARRIAGE => 'is_married',
            default => null,
        };
    }

    protected function authorizeEnrollmentAccess(User $user, MafundishoEnrollment $enrollment): void
    {
        $exists = $this->scopedEnrollmentQuery($user)
            ->where('mafundisho_enrollments.id', $enrollment->id)
            ->exists();

        if (!$exists) {
            abort(403);
        }
    }

    protected function resolveTeachingTypeFromValidated(array $validated): TeachingType
    {
        if (!empty($validated['teaching_type_id'])) {
            return TeachingType::query()
                ->whereKey((int) $validated['teaching_type_id'])
                ->where('is_active', true)
                ->firstOrFail();
        }

        if (!empty($validated['type'])) {
            return $this->findTeachingTypeBySlug($validated['type']);
        }

        throw ValidationException::withMessages([
            'teaching_type_id' => db_trans('validation_invalid_selection'),
        ]);
    }

    protected function findTeachingTypeBySlug(string $slug): TeachingType
    {
        $teachingType = TeachingType::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$teachingType) {
            throw ValidationException::withMessages([
                'type' => db_trans('validation_invalid_selection'),
            ]);
        }

        return $teachingType;
    }

    protected function ensureValidStatus(?string $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        if (!in_array($this->normalizeStatus($status), MafundishoEnrollment::availableStatuses(), true)) {
            throw ValidationException::withMessages([
                'status' => db_trans('validation_invalid_selection'),
            ]);
        }
    }

    protected function normalizeStatus(?string $status): string
    {
        return match ($status) {
            null, '', MafundishoEnrollment::STATUS_ACTIVE => MafundishoEnrollment::STATUS_CONTINUING,
            default => $status,
        };
    }

    protected function activeTeachingTypes(): Collection
    {
        return TeachingType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected function statusLabels(): Collection
    {
        return collect([
            MafundishoEnrollment::STATUS_CONTINUING => db_trans('continuing_students'),
            MafundishoEnrollment::STATUS_ACTIVE => db_trans('continuing_students'),
            MafundishoEnrollment::STATUS_COMPLETED => db_trans('graduated_students'),
            MafundishoEnrollment::STATUS_FAILED => db_trans('failed_students'),
            MafundishoEnrollment::STATUS_REPEATED => db_trans('repeating_students'),
            MafundishoEnrollment::STATUS_WITHDRAWN => db_trans('withdrawn_students'),
        ]);
    }

    protected function labelForType(string $type): string
    {
        return optional($this->findTeachingTypeBySlug($type))->name ?: ucfirst($type);
    }

    public function getTypeExportRows(
    User $user,
    string $type,
    int $year,
    ?string $status = null,
    ?int $month = null
): Collection {
    $data = $this->getTypeIndexData($user, $type, $year, $status, $month);

    return $data['enrollments']
        ->values()
        ->map(function (MafundishoEnrollment $enrollment, int $index) use ($data) {
            $normalizedStatus = $enrollment->status === MafundishoEnrollment::STATUS_ACTIVE
                ? MafundishoEnrollment::STATUS_CONTINUING
                : $enrollment->status;

            return (object) [
                'sn' => $index + 1,
                'member' => $enrollment->member?->full_name ?? '—',
                'member_code' => $enrollment->member?->member_code ?: '—',
                'familia' => $enrollment->member?->familia?->name ?: '—',
                'jumuiya' => $enrollment->member?->familia?->jumuiya?->name ?: '—',
                'teaching_type' => $enrollment->teachingType?->name ?? $data['typeLabel'],
                'year' => $enrollment->year,
                'status' => $data['statusLabels'][$normalizedStatus] ?? $enrollment->status,
                'started_on' => optional($enrollment->started_at)->format('d M Y') ?: '—',
                'ended_on' => optional($enrollment->ended_at)->format('d M Y') ?: '—',
            ];
        });
}

public function getTypeExportPdfData(
    User $user,
    string $type,
    int $year,
    ?string $status = null,
    ?int $month = null
): array {
    $data = $this->getTypeIndexData($user, $type, $year, $status, $month);
    $rows = $this->getTypeExportRows($user, $type, $year, $status, $month);

    $monthLabel = $month
        ? now()->startOfYear()->month((int) $month)->translatedFormat('F')
        : db_trans('all_months');

    return [
        'pageTitle' => db_trans('teaching_students'),
        'reportTitle' => $data['typeLabel'] . ' - ' . $year,
        'rows' => $rows,
        'metaItems' => [
            ['label' => db_trans('teaching_type'), 'value' => $data['typeLabel']],
            ['label' => db_trans('year'), 'value' => $year],
            ['label' => db_trans('month'), 'value' => $monthLabel],
            ['label' => db_trans('students'), 'value' => number_format($rows->count())],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
        ],
    ];
}
}