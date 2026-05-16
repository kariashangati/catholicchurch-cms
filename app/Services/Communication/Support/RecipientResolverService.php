<?php

namespace App\Services\Communication\Support;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\CommunicationPreference;
use App\Models\Member;
use App\Models\Tithe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class RecipientResolverService
{
    public function __construct(
        protected PhoneNumberService $phoneNumberService,
    ) {
    }

    public function resolve(array $payload, bool $respectPreferences = true): Collection
    {
        $audienceType = $payload['audience_type'] ?? null;

        if (! $audienceType) {
            throw new InvalidArgumentException('The audience_type field is required.');
        }

        $recipients = match ($audienceType) {
            'member' => $this->resolveMembersByIds([$payload['member_id'] ?? null]),
            'members' => $this->resolveMembersByIds($payload['member_ids'] ?? []),
            'familia' => $this->resolveMembersFromFamilias([$payload['familia_id'] ?? null]),
            'familias' => $this->resolveMembersFromFamilias($payload['familia_ids'] ?? []),
            'jumuiya' => $this->resolveMembersFromJumuiyas([$payload['jumuiya_id'] ?? null]),
            'jumuiyas' => $this->resolveMembersFromJumuiyas($payload['jumuiya_ids'] ?? []),
            'kanda' => $this->resolveMembersFromKandas([$payload['kanda_id'] ?? null]),
            'kandas' => $this->resolveMembersFromKandas($payload['kanda_ids'] ?? []),
            'apostolic_group' => $this->resolveMembersFromApostolicGroups([$payload['apostolic_group_id'] ?? null]),
            'apostolic_groups' => $this->resolveMembersFromApostolicGroups($payload['apostolic_group_ids'] ?? []),
            'leadership_assignment' => $this->resolveMembersFromLeadershipAssignments([$payload['leadership_assignment_id'] ?? null]),
            'leadership_assignments' => $this->resolveMembersFromLeadershipAssignments($payload['leadership_assignment_ids'] ?? []),
            'all_members' => $this->memberBaseQuery()->get(),
            'all_active_members' => $this->memberBaseQuery()->where('members.is_active', true)->get(),
            'filtered_members' => $this->resolveFilteredMembers($payload['filters'] ?? []),
            'non_tithe_givers' => $this->resolveNonTitheGivers($payload),
            'non_contribution_givers' => $this->resolveNonContributionGivers($payload),
            default => throw new InvalidArgumentException("Unsupported audience_type [{$audienceType}]."),
        };

        return $this->transformMembersToRecipients($recipients, $respectPreferences)
            ->unique('phone_normalized')
            ->values();
    }

    protected function memberBaseQuery(): Builder
    {
        return Member::query()
            ->with(['familia.jumuiya.kanda'])
            ->select('members.*');
    }

    protected function resolveMembersByIds(array $memberIds): Collection
    {
        $ids = collect($memberIds)->filter()->values();
        return $ids->isEmpty() ? collect() : $this->memberBaseQuery()->whereIn('members.id', $ids)->get();
    }

    protected function resolveMembersFromFamilias(array $familiaIds): Collection
    {
        $ids = collect($familiaIds)->filter()->values();
        return $ids->isEmpty() ? collect() : $this->memberBaseQuery()->whereIn('familia_id', $ids)->get();
    }

    protected function resolveMembersFromJumuiyas(array $jumuiyaIds): Collection
    {
        $ids = collect($jumuiyaIds)->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->memberBaseQuery()
            ->whereHas('familia', fn (Builder $query) => $query->whereIn('jumuiya_id', $ids))
            ->get();
    }

    protected function resolveMembersFromKandas(array $kandaIds): Collection
    {
        $ids = collect($kandaIds)->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->memberBaseQuery()
            ->whereHas('familia.jumuiya', fn (Builder $query) => $query->whereIn('kanda_id', $ids))
            ->get();
    }

    protected function resolveMembersFromApostolicGroups(array $groupIds): Collection
    {
        $ids = collect($groupIds)->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->memberBaseQuery()
            ->whereHas('apostolicGroups', fn (Builder $query) => $query->whereIn('apostolic_groups.id', $ids))
            ->get();
    }

    protected function resolveMembersFromLeadershipAssignments(array $assignmentIds): Collection
    {
        $ids = collect($assignmentIds)->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->memberBaseQuery()
            ->whereHas('leadershipAssignments', fn (Builder $query) => $query->whereIn('leadership_assignments.id', $ids))
            ->get();
    }

    protected function resolveFilteredMembers(array $filters): Collection
    {
        return $this->applyMemberScopeFilters($this->memberBaseQuery(), $filters)
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== '' && $filters['is_active'] !== null, fn (Builder $q) => $q->where('members.is_active', (bool) $filters['is_active']))
            ->when(! empty($filters['gender']), fn (Builder $q) => $q->where('gender', $filters['gender']))
            ->when(! empty($filters['age_group_id']), fn (Builder $q) => $q->where('age_group_id', $filters['age_group_id']))
            ->when(! empty($filters['apostolic_group_id']), function (Builder $query) use ($filters) {
                $query->whereHas('apostolicGroups', fn (Builder $sub) => $sub->where('apostolic_groups.id', $filters['apostolic_group_id']));
            })
            ->when(! empty($filters['leadership_position_id']), function (Builder $query) use ($filters) {
                $query->whereHas('leadershipAssignments', fn (Builder $sub) => $sub->where('leadership_position_id', $filters['leadership_position_id'])->where('status', 'active'));
            })
            ->get();
    }

    protected function resolveNonTitheGivers(array $payload): Collection
    {
        [$start, $end] = $this->periodRange($payload);
        $filters = $payload['filters'] ?? $payload;

        return $this->applyMemberScopeFilters($this->memberBaseQuery()->where('members.is_active', true), $filters)
            ->whereDoesntHave('titheRecords', function (Builder $query) use ($start, $end) {
                $query->whereBetween('contribution_date', [$start->toDateString(), $end->toDateString()])
                    ->whereIn('status', ['approved', 'pending']);
            })
            ->get();
    }

    protected function resolveNonContributionGivers(array $payload): Collection
    {
        [$start, $end] = $this->periodRange($payload);
        $filters = $payload['filters'] ?? $payload;
        $typeId = $filters['contribution_type_id'] ?? $payload['contribution_type_id'] ?? null;

        return $this->applyMemberScopeFilters($this->memberBaseQuery()->where('members.is_active', true), $filters)
            ->whereDoesntHave('cashContributions', function (Builder $query) use ($start, $end, $typeId) {
                $query->whereBetween('contribution_date', [$start->toDateString(), $end->toDateString()])
                    ->whereIn('status', ['approved', 'pending'])
                    ->when($typeId, fn (Builder $q) => $q->where('contribution_type_id', $typeId));
            })
            ->whereDoesntHave('bankContributions', function (Builder $query) use ($start, $end, $typeId) {
                $query->whereBetween('contribution_date', [$start->toDateString(), $end->toDateString()])
                    ->whereIn('status', ['verified', 'approved', 'pending'])
                    ->when($typeId, fn (Builder $q) => $q->where('contribution_type_id', $typeId));
            })
            ->get();
    }

    protected function applyMemberScopeFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['familia_id']), fn (Builder $q) => $q->where('familia_id', $filters['familia_id']))
            ->when(! empty($filters['jumuiya_id']), fn (Builder $q) => $q->whereHas('familia', fn (Builder $sub) => $sub->where('jumuiya_id', $filters['jumuiya_id'])))
            ->when(! empty($filters['kanda_id']), fn (Builder $q) => $q->whereHas('familia.jumuiya', fn (Builder $sub) => $sub->where('kanda_id', $filters['kanda_id'])));
    }

    protected function periodRange(array $payload): array
    {
        $year = (int) ($payload['year'] ?? data_get($payload, 'filters.year') ?? now()->year);
        $month = $payload['month'] ?? data_get($payload, 'filters.month') ?? null;

        if ($month) {
            $start = Carbon::create($year, (int) $month, 1)->startOfMonth();
            return [$start, $start->copy()->endOfMonth()];
        }

        return [Carbon::create($year, 1, 1)->startOfYear(), Carbon::create($year, 12, 31)->endOfYear()];
    }

    protected function transformMembersToRecipients(Collection $members, bool $respectPreferences): Collection
    {
        return $members
            ->map(function (Member $member) use ($respectPreferences) {
                $preferences = $this->preferenceFor($member);
                $rawPhone = $preferences?->preferred_phone ?: $member->phone;
                $normalizedPhone = $rawPhone ? $this->phoneNumberService->normalize($rawPhone) : null;
                $fullName = $member->full_name ?: trim(collect([$member->first_name, $member->middle_name, $member->last_name])->filter()->implode(' '));
                $jumuiya = $member->familia?->jumuiya;

                return [
                    'member_id' => $member->id,
                    'familia_id' => $member->familia_id,
                    'jumuiya_id' => $jumuiya?->id,
                    'kanda_id' => $jumuiya?->kanda_id,
                    'recipient_name' => $fullName,
                    'name' => $fullName,
                    'phone' => $rawPhone,
                    'phone_normalized' => $normalizedPhone,
                    'recipient_type' => 'member',
                    'locale' => $preferences?->preferred_locale ?: app()->getLocale(),
                    'can_receive' => $this->canReceiveSms($preferences, $respectPreferences),
                    'preference_reason' => $this->preferenceReason($preferences, $rawPhone, $normalizedPhone),
                ];
            })
            ->filter(fn (array $recipient) => $recipient['phone_normalized'] && $recipient['can_receive'])
            ->values();
    }

    protected function preferenceFor(Member $member): ?CommunicationPreference
    {
        if (method_exists($member, 'communicationPreference')) {
            return $member->communicationPreference;
        }

        return CommunicationPreference::query()->where('member_id', $member->id)->first();
    }

    protected function canReceiveSms(?CommunicationPreference $preferences, bool $respectPreferences): bool
    {
        if (! $respectPreferences || ! $preferences) {
            return true;
        }

        return (bool) ($preferences->allow_sms && $preferences->allow_manual_sms && is_null($preferences->opted_out_at));
    }

    protected function preferenceReason(?CommunicationPreference $preferences, ?string $rawPhone, ?string $normalizedPhone): ?string
    {
        if (! $rawPhone) return 'missing_phone';
        if (! $normalizedPhone) return 'invalid_phone';
        if ($preferences && ! $preferences->allow_sms) return 'sms_disabled';
        if ($preferences && $preferences->opted_out_at) return 'opted_out';
        return null;
    }
}
