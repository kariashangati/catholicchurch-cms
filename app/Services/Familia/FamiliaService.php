<?php

namespace App\Services\Familia;

use App\Models\ApostolicGroupMember;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\Kanda;

class FamiliaService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getIndexData(User $user, array $filters = []): array
    {
        $familias = $this->scopedFamiliasQuery($user)
            ->with('jumuiya.kanda')
            ->withCount('members')
            ->latest()
            ->get();

        $availableJumuiyas = $this->availableJumuiyasForUser($user);
        $recentFamilias = $familias->take(5)->values();
        $familiaNames = $familias->take(8)->pluck('name')->values();
        $memberCounts = $familias->take(8)->pluck('members_count')->map(fn ($value) => (int) $value)->values();
        $activeCount = $familias->where('is_active', true)->count();
        $inactiveCount = $familias->where('is_active', false)->count();
        $withPhone = $familias->filter(fn ($familia) => filled($familia->phone))->count();
        $withoutPhone = $familias->count() - $withPhone;
        $withoutAddress = $familias->filter(fn ($familia) => blank($familia->address))->count();
        $withoutEnvelope = $familias->filter(fn ($familia) => blank($familia->envelope_no))->count();

        $jumuiyaDistribution = $familias
            ->groupBy(fn ($familia) => $familia->jumuiya?->name ?: db_trans('unassigned'))
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->take(6);

        return [
            'pageTitle' => db_trans('familias'),
            'familias' => $familias,
            'kandas' => $this->availableKandasForUser($user),
            'jumuiyas' => $availableJumuiyas,
            'recentFamilias' => $recentFamilias,
            'stats' => [
                'total_familias' => $familias->count(),
                'active_familias' => $activeCount,
                'inactive_familias' => $inactiveCount,
                'total_members' => $familias->sum('members_count'),
                'familias_with_phone' => $withPhone,
                'average_members_per_familia' => $familias->count() > 0
                    ? round($familias->sum('members_count') / $familias->count(), 1)
                    : 0,
                'familias_without_phone' => $withoutPhone,
                'familias_without_address' => $withoutAddress,
                'familias_without_envelope' => $withoutEnvelope,
            ],
            'chartData' => [
                'size_labels' => $familiaNames,
                'size_members' => $memberCounts,
                'status_labels' => [db_trans('active_familias'), db_trans('inactive_familias')],
                'status_data' => [$activeCount, $inactiveCount],
                'phone_labels' => [db_trans('familias_with_phone'), db_trans('familias_without_phone')],
                'phone_data' => [$withPhone, $withoutPhone],
                'jumuiya_labels' => $jumuiyaDistribution->keys()->values(),
                'jumuiya_data' => $jumuiyaDistribution->values(),
            ],
        ];
    }

    public function getShowData(Familia $familia, User $user): array
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);

        $familia->load('jumuiya.kanda');

        $members = Member::query()
            ->with('familia.jumuiya.kanda')
            ->where('familia_id', $familia->id)
            ->latest()
            ->get();

        $normalize = fn ($value) => Str::lower(trim((string) $value));
        $maleCount = $members->filter(fn ($member) => in_array($normalize($member->gender), ['male', 'mwanaume'], true))->count();
        $femaleCount = $members->filter(fn ($member) => in_array($normalize($member->gender), ['female', 'mwanamke'], true))->count();
        $fathersCount = $members->filter(fn ($member) => in_array($normalize($member->family_role), ['father', 'baba'], true))->count();
        $mothersCount = $members->filter(fn ($member) => in_array($normalize($member->family_role), ['mother', 'mama'], true))->count();
        $childrenCount = $members->filter(fn ($member) => in_array($normalize($member->family_role), ['child', 'mtoto'], true))->count();

        $currentYear = now()->year;
        $previousYear = now()->copy()->subYear()->year;
        $memberIds = $members->pluck('id');
        $mavunoTypeId = ContributionType::query()->where('slug', 'mavuno')->value('id');

        $totalsCurrent = [
            'zaka' => Tithe::query()->whereIn('member_id', $memberIds)->whereYear('contribution_date', $currentYear)->sum('amount'),
            'mavuno' => $mavunoTypeId
                ? CashContribution::query()->whereIn('member_id', $memberIds)->where('contribution_type_id', $mavunoTypeId)->whereYear('contribution_date', $currentYear)->sum('amount')
                : 0,
            'other' => CashContribution::query()->whereIn('member_id', $memberIds)
                ->when($mavunoTypeId, fn ($query) => $query->where('contribution_type_id', '!=', $mavunoTypeId))
                ->whereYear('contribution_date', $currentYear)
                ->sum('amount'),
        ];

        $totalsPrevious = [
            'zaka' => Tithe::query()->whereIn('member_id', $memberIds)->whereYear('contribution_date', $previousYear)->sum('amount'),
            'mavuno' => $mavunoTypeId
                ? CashContribution::query()->whereIn('member_id', $memberIds)->where('contribution_type_id', $mavunoTypeId)->whereYear('contribution_date', $previousYear)->sum('amount')
                : 0,
            'other' => CashContribution::query()->whereIn('member_id', $memberIds)
                ->when($mavunoTypeId, fn ($query) => $query->where('contribution_type_id', '!=', $mavunoTypeId))
                ->whereYear('contribution_date', $previousYear)
                ->sum('amount'),
        ];

        $profileCompletedChecks = [
            filled($familia->phone),
            filled($familia->address),
            filled($familia->envelope_no),
            (bool) $familia->jumuiya_id,
            $members->count() > 0,
        ];

        $profileReadiness = (int) round(
            (collect($profileCompletedChecks)->filter()->count() / max(count($profileCompletedChecks), 1)) * 100
        );

        return [
            'pageTitle' => db_trans('family_details'),
            'familia' => $familia,
            'members' => $members,
            'kandas' => $this->availableKandasForUser($user),
            'jumuiyas' => $this->availableJumuiyasForUser($user),
            'availableFamiliaRoles' => ['baba', 'mama', 'mtoto', 'nyingine'],
            'stats' => [
                'members_count' => $members->count(),
                'male_members_count' => $maleCount,
                'female_members_count' => $femaleCount,
                'fathers_count' => $fathersCount,
                'mothers_count' => $mothersCount,
                'children_count' => $childrenCount,
                'active_members_count' => $members->where('is_active', true)->count(),
                'baptized_count' => $members->where('is_baptized', true)->count(),
                'communion_count' => $members->where('has_communion', true)->count(),
                'confirmation_count' => $members->where('has_confirmation', true)->count(),
                'married_count' => $members->where('is_married', true)->count(),
                'profile_readiness' => $profileReadiness,
            ],
            'chartData' => [
                'genderLabels' => [db_trans('male_members'), db_trans('female_members')],
                'genderData' => [$maleCount, $femaleCount],
                'roleLabels' => [db_trans('father'), db_trans('mother'), db_trans('child')],
                'roleData' => [$fathersCount, $mothersCount, $childrenCount],
                'sacramentLabels' => [db_trans('baptized'), db_trans('communion'), db_trans('confirmation'), db_trans('married')],
                'sacramentData' => [
                    $members->where('is_baptized', true)->count(),
                    $members->where('has_communion', true)->count(),
                    $members->where('has_confirmation', true)->count(),
                    $members->where('is_married', true)->count(),
                ],
            ],
            'finance' => [
                'current_year' => $currentYear,
                'previous_year' => $previousYear,
                'current' => [
                    'zaka' => (float) $totalsCurrent['zaka'],
                    'mavuno' => (float) $totalsCurrent['mavuno'],
                    'other' => (float) $totalsCurrent['other'],
                    'total' => (float) array_sum($totalsCurrent),
                ],
                'previous' => [
                    'zaka' => (float) $totalsPrevious['zaka'],
                    'mavuno' => (float) $totalsPrevious['mavuno'],
                    'other' => (float) $totalsPrevious['other'],
                    'total' => (float) array_sum($totalsPrevious),
                ],
            ],
        ];
    }

    public function store(array $data, User $user): Familia
    {
        $jumuiya = Jumuiya::findOrFail($data['jumuiya_id']);
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        return Familia::create([
            'jumuiya_id' => $data['jumuiya_id'],
            'name' => Str::upper(trim($data['name'])),
            'phone' => $data['phone'] ?? null,
            'envelope_no' => $data['envelope_no'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update(Familia $familia, array $data, User $user): Familia
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);

        $jumuiya = Jumuiya::findOrFail($data['jumuiya_id']);
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $familia->update([
            'jumuiya_id' => $data['jumuiya_id'],
            'name' => Str::upper(trim($data['name'])),
            'phone' => $data['phone'] ?? null,
            'envelope_no' => $data['envelope_no'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $familia->refresh();
    }

    public function delete(Familia $familia, User $user): bool
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);

        if ($familia->members()->exists()) {
            abort(422, db_trans('cannot_delete_family_with_members'));
        }

        return (bool) $familia->delete();
    }

    public function addMember(Familia $familia, array $data, User $user): Member
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);
        $data['member_code'] = $data['member_code'] ?: $this->generateMemberCode($familia);

        $gender = $this->normalizeMemberGenderForStorage($data['gender'] ?? null);
        $familyRole = $this->normalizeFamilyRoleForStorage($data['family_role'] ?? null);

        return Member::create([
            'familia_id' => $familia->id,
            'first_name' => Str::upper(trim($data['first_name'])),
            'middle_name' => filled($data['middle_name'] ?? null) ? Str::upper(trim($data['middle_name'])) : null,
            'last_name' => Str::upper(trim($data['last_name'])),
            'phone' => $data['phone'] ?? null,
            'gender' => $gender,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'is_baptized' => (bool) ($data['is_baptized'] ?? false),
            'has_communion' => (bool) ($data['has_communion'] ?? false),
            'has_confirmation' => (bool) ($data['has_confirmation'] ?? false),
            'receives_eucharist' => (bool) ($data['receives_eucharist'] ?? false),
            'is_married' => (bool) ($data['is_married'] ?? false),
            'marriage_type' => $data['marriage_type'] ?? null,
            'baptism_certificate_number' => $data['baptism_certificate_number'] ?? null,
            'marriage_certificate_number' => $data['marriage_certificate_number'] ?? null,
            'baptism_parish' => $data['baptism_parish'] ?? null,
            'baptism_diocese' => $data['baptism_diocese'] ?? null,
            'family_role' => $familyRole,
            'notes' => $data['notes'] ?? null,
            'member_code' => $data['member_code'],
            'bahasha' => $data['bahasha'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function updateMember(Familia $familia, Member $member, array $data, User $user): Member
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);
        $this->scopeAccessGate->authorizeMember($user, $member);
        abort_unless((int) $member->familia_id === (int) $familia->id, 404);

        $gender = $this->normalizeMemberGenderForStorage($data['gender'] ?? null);
        $familyRole = $this->normalizeFamilyRoleForStorage($data['family_role'] ?? null);

        $member->update([
            'first_name' => Str::upper(trim($data['first_name'])),
            'middle_name' => filled($data['middle_name'] ?? null) ? Str::upper(trim($data['middle_name'])) : null,
            'last_name' => Str::upper(trim($data['last_name'])),
            'phone' => $data['phone'] ?? null,
            'gender' => $gender,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'is_baptized' => (bool) ($data['is_baptized'] ?? false),
            'has_communion' => (bool) ($data['has_communion'] ?? false),
            'has_confirmation' => (bool) ($data['has_confirmation'] ?? false),
            'receives_eucharist' => (bool) ($data['receives_eucharist'] ?? false),
            'is_married' => (bool) ($data['is_married'] ?? false),
            'marriage_type' => $data['marriage_type'] ?? null,
            'baptism_certificate_number' => $data['baptism_certificate_number'] ?? null,
            'marriage_certificate_number' => $data['marriage_certificate_number'] ?? null,
            'baptism_parish' => $data['baptism_parish'] ?? null,
            'baptism_diocese' => $data['baptism_diocese'] ?? null,
            'family_role' => $familyRole,
            'notes' => $data['notes'] ?? null,
            'member_code' => $data['member_code'] ?? $member->member_code,
            'bahasha' => $data['bahasha'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $member->refresh();
    }

    public function deleteMember(Familia $familia, Member $member, User $user): bool
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);
        $this->scopeAccessGate->authorizeMember($user, $member);
        abort_unless((int) $member->familia_id === (int) $familia->id, 404);

        $hasTithes = Tithe::query()->where('member_id', $member->id)->exists();
        $hasCashContributions = CashContribution::query()->where('member_id', $member->id)->exists();
        $hasGroupMemberships = class_exists(ApostolicGroupMember::class)
            ? ApostolicGroupMember::query()->where('member_id', $member->id)->exists()
            : false;

        if ($hasTithes || $hasCashContributions || $hasGroupMemberships) {
            abort(422, db_trans('cannot_delete_member_with_related_records'));
        }

        return (bool) $member->delete();
    }

    protected function scopedFamiliasQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Familia::query()
            ->select('familias.*')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id');

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('jumuiyas.kanda_id', $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->where('familias.jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function availableKandasForUser(User $user)
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return collect();
        }

        if ($scope->isGlobal()) {
            return Kanda::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        if ($scope->isKanda()) {
            return Kanda::query()
                ->whereKey($scope->kandaId)
                ->where('is_active', true)
                ->get();
        }

        if ($scope->isJumuiya() && $scope->kandaId) {
            return Kanda::query()
                ->whereKey($scope->kandaId)
                ->where('is_active', true)
                ->get();
        }

        return collect();
    }

    protected function availableJumuiyasForUser(User $user)
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Jumuiya::query()->with('kanda')->orderBy('name');

        if ($scope->isInvalid()) {
            return collect();
        }

        if ($scope->isGlobal()) {
            return $query->get();
        }

        if ($scope->isKanda()) {
            return $query->where('kanda_id', $scope->kandaId)->get();
        }

        if ($scope->isJumuiya()) {
            return $query->whereKey($scope->jumuiyaId)->get();
        }

        return collect();
    }

    public function getExportRows(User $user): Collection
    {
        return $this->scopedFamiliasQuery($user)
            ->with('jumuiya.kanda')
            ->withCount('members')
            ->orderBy('familias.name')
            ->get()
            ->map(function (Familia $familia) {
                return (object) [
                    'name' => $familia->name,
                    'jumuiya_name' => $familia->jumuiya?->name ?? '—',
                    'phone' => $familia->phone ?: '—',
                    'envelope_no' => $familia->envelope_no ?: '—',
                    'members_count' => (int) $familia->members_count,
                    'status_label' => $familia->is_active ? db_trans('active') : db_trans('inactive'),
                ];
            });
    }

    public function getMembersExportRows(Familia $familia, User $user): Collection
    {
        $this->scopeAccessGate->authorizeFamilia($user, $familia);

        return Member::query()
            ->where('familia_id', $familia->id)
            ->orderBy('first_name')
            ->get()
            ->values()
            ->map(function (Member $member, int $index) {
                $sacraments = collect([
                    $member->is_baptized ? db_trans('baptized_short') : null,
                    $member->has_communion ? db_trans('communion_short') : null,
                    $member->has_confirmation ? db_trans('confirmation_short') : null,
                    $member->is_married ? db_trans('married_short') : null,
                ])->filter()->implode(', ');

                return (object)[
                    'sn' => $index + 1,
                    'member' => $member->full_name,
                    'gender' => db_trans(strtolower($member->gender)),
                    'family_role' => db_trans(strtolower($member->family_role)),
                    'phone' => $member->phone ?: '—',
                    'sacraments' => $sacraments ?: '—',
                    'status' => $member->is_active ? db_trans('active') : db_trans('inactive'),
                ];
            });
    }

    public function getMembersExportPdfData(Familia $familia, User $user): array
    {
        $rows = $this->getMembersExportRows($familia, $user);

        return [
            'reportTitle' => db_trans('familia_ya') . ' ' . $familia->name,
            'rows' => $rows,
            'metaItems' => [
                ['label' => db_trans('familia'), 'value' => $familia->name],
                ['label' => db_trans('waumini'), 'value' => number_format($rows->count())],
                ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
            ],
        ];
    }

    public function getExportPdfData(User $user): array
    {
        $rows = $this->getExportRows($user);

        $jumuiyaDistribution = $rows
            ->groupBy('jumuiya_name')
            ->map(fn (Collection $items, string $name) => (object) [
                'name' => $name,
                'familias_count' => $items->count(),
            ])
            ->sortByDesc('familias_count')
            ->values();

        return [
            'pageTitle' => db_trans('family_directory'),
            'reportTitle' => db_trans('family_directory'),
            'rows' => $rows,
            'jumuiyaDistribution' => $jumuiyaDistribution,
            'metaItems' => [
                ['label' => db_trans('familias'), 'value' => number_format($rows->count())],
                ['label' => db_trans('members'), 'value' => number_format($rows->sum('members_count'))],
                ['label' => db_trans('active'), 'value' => number_format($rows->where('status_label', db_trans('active'))->count())],
                ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
            ],
        ];
    }

    protected function normalizeMemberGenderForStorage(?string $value): ?string
    {
        $value = Str::lower(trim((string) $value));

        return match ($value) {
            'male', 'mwanaume' => 'mwanaume',
            'female', 'mwanamke' => 'mwanamke',
            default => filled($value) ? $value : null,
        };
    }

    protected function normalizeFamilyRoleForStorage(?string $value): ?string
    {
        $value = Str::lower(trim((string) $value));

        return match ($value) {
            'father', 'baba' => 'baba',
            'mother', 'mama' => 'mama',
            'child', 'mtoto' => 'mtoto',
            'other', 'nyingine' => 'nyingine',
            default => filled($value) ? $value : null,
        };
    }

    protected function generateMemberCode(Familia $familia): string
    {
        $familia->loadMissing('jumuiya.kanda');
        $prefix = optional($familia->jumuiya?->kanda)->code ?: 'MBR';
        $latestId = (Member::query()->max('id') ?? 0) + 1;

        return strtoupper($prefix) . str_pad((string) $latestId, 5, '0', STR_PAD_LEFT);
    }
}