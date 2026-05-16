<?php

namespace App\Services\Finance;

use App\Models\Jumuiya;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\TitheBatch;
use App\Models\TitheBatchDenomination;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\Communication\SmsTemplateMessageService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TitheService
{
    public function __construct(
        protected TitheSmsService $sms,
        protected TitheAccessService $access,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function store(array $data, User $user): Tithe
    {
        return DB::transaction(function () use ($data, $user) {
            $member = Member::with('familia.jumuiya')->findOrFail($data['member_id']);
            $this->scopeAccessGate->authorizeMember($user, $member);

            $this->guardExactDuplicate($member->id, $data['contribution_date'], $data['amount']);

            $monthConflict = $this->monthConflictQuery($member->id, $data['contribution_date'])->exists();

            if ($monthConflict && empty($data['override_reason'])) {
                throw ValidationException::withMessages([
                    'member_id' => db_trans('monthly_tithe_entry_exists_override_required'),
                ]);
            }

            $tithe = Tithe::create($this->payload($data, $user, $member));

            if (! empty($data['send_sms'])) {
                $this->sms->sendAcknowledgement($member, $this->messageFor($member, $tithe), $user);
            }

            return $tithe->load(['member.familia.jumuiya.kanda', 'jumuiya', 'recorder', 'approver']);
        });
    }

    public function update(Tithe $tithe, array $data, User $user): Tithe
    {
        return DB::transaction(function () use ($tithe, $data, $user) {
            $this->ensureUserCanAccessTithe($user, $tithe);

            $member = Member::with('familia.jumuiya')->findOrFail($data['member_id']);
            $this->scopeAccessGate->authorizeMember($user, $member);

            $this->guardExactDuplicate($member->id, $data['contribution_date'], $data['amount'], $tithe->id);

            $tithe->update($this->payload($data, $user, $member, $tithe));

            if (! empty($data['send_sms'])) {
                $this->sms->sendAcknowledgement($member, $this->messageFor($member, $tithe->fresh()), $user);
            }

            return $tithe->fresh(['member.familia.jumuiya.kanda', 'jumuiya', 'recorder', 'approver']);
        });
    }

    public function delete(Tithe $tithe, User $user): void
    {
        $this->ensureUserCanAccessTithe($user, $tithe);

        $tithe->delete();
    }

    public function bulkPreview(array $payload, User $user): array
    {
        $date = $this->normalizeContributionDate($payload['contribution_date'] ?? null);
        $jumuiyaId = (int) ($payload['jumuiya_id'] ?? 0);
        $kandaId = (int) ($payload['kanda_id'] ?? 0);

        $jumuiya = Jumuiya::with('kanda')->findOrFail($jumuiyaId);
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        if ((int) $jumuiya->kanda_id !== $kandaId) {
            throw ValidationException::withMessages([
                'jumuiya_id' => db_trans('selected_jumuiya_does_not_belong_to_kanda'),
            ]);
        }

        $members = Member::query()
            ->whereHas('familia', fn ($q) => $q->where('jumuiya_id', $jumuiyaId))
            ->with('familia')
            ->orderBy('first_name')
            ->get(['id', 'familia_id', 'first_name', 'middle_name', 'last_name', 'member_code', 'phone', 'bahasha']);

        $periodYear = (int) $date->format('Y');
        $periodMonth = (int) $date->format('n');

        $existing = Tithe::query()
            ->with(['recorder:id,name'])
            ->where('jumuiya_id', $jumuiyaId)
            ->whereYear('contribution_date', $periodYear)
            ->whereMonth('contribution_date', $periodMonth)
            ->get()
            ->groupBy('member_id');

        $rows = $members->map(function (Member $member) use ($existing) {
            $entries = $existing->get($member->id, collect());
            $latest = $entries->sortByDesc('contribution_date')->sortByDesc('id')->first();

            return [
                'member_id' => $member->id,
                'member_name' => trim($member->full_name ?? ($member->first_name . ' ' . $member->last_name)),
                'member_code' => $member->member_code,
                'phone' => $member->phone,
                'bahasha' => $member->bahasha,
                'familia' => $member->familia?->name,
                'already_recorded' => $entries->isNotEmpty(),
                'entries_count' => $entries->count(),
                'latest_amount' => $latest?->amount,
                'latest_date' => optional($latest?->contribution_date)->format('Y-m-d'),
                'latest_by' => $latest?->recorder?->name,
            ];
        })->values();

        return [
            'date' => $date->format('Y-m-d'),
            'rows' => $rows,
            'duplicates_count' => $rows->where('already_recorded', true)->count(),
        ];
    }

    public function bulkStore(array $payload, User $user): array
    {
        $date = $this->normalizeContributionDate($payload['contribution_date']);
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $override = (bool) ($payload['override_existing'] ?? false);
        $overrideReason = trim((string) ($payload['override_reason'] ?? ''));
        $rows = collect($payload['rows'])
            ->filter(fn ($row) => (float) ($row['amount'] ?? 0) > 0)
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'rows' => db_trans('please_enter_at_least_one_tithe_amount'),
            ]);
        }

        $jumuiya = Jumuiya::with('kanda')->findOrFail((int) $payload['jumuiya_id']);
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        if ((int) $jumuiya->kanda_id !== (int) $payload['kanda_id']) {
            throw ValidationException::withMessages([
                'jumuiya_id' => db_trans('selected_jumuiya_does_not_belong_to_kanda'),
            ]);
        }

        $expectedTotal = round((float) $payload['expected_total'], 2);
        $denominationTotal = round($this->denominationTotal(collect($payload['denominations'])), 2);

        if ($expectedTotal !== $denominationTotal) {
            throw ValidationException::withMessages([
                'denominations' => db_trans('denomination_total_must_match_tithe_total'),
            ]);
        }

        return DB::transaction(function () use ($payload, $user, $date, $year, $month, $override, $overrideReason, $rows, $expectedTotal) {
            $batch = TitheBatch::create([
                'kanda_id' => $payload['kanda_id'],
                'jumuiya_id' => $payload['jumuiya_id'],
                'contribution_date' => $date->toDateString(),
                'tithe_year' => $year,
                'tithe_month' => $month,
                'total_amount' => $expectedTotal,
                'rows_count' => $rows->count(),
                'recorded_by' => $user->id,
                'notes' => $overrideReason ?: null,
            ]);

            foreach ($payload['denominations'] as $denom) {
                $qty = (int) ($denom['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                TitheBatchDenomination::create([
                    'tithe_batch_id' => $batch->id,
                    'denomination_value' => $denom['value'],
                    'quantity' => $qty,
                    'total_amount' => round(((float) $denom['value']) * $qty, 2),
                ]);
            }

            $saved = 0;
            $skippedExact = 0;
            $skippedMonthly = 0;
            $overridden = 0;

            foreach ($rows as $row) {
                $member = Member::with('familia.jumuiya')->findOrFail($row['member_id']);
                $this->scopeAccessGate->authorizeMember($user, $member);

                if ((int) $member->familia?->jumuiya_id !== (int) $payload['jumuiya_id']) {
                    continue;
                }

                if ($this->exactDuplicateQuery($member->id, $date->toDateString(), $row['amount'])->exists()) {
                    $skippedExact++;
                    continue;
                }

                $monthlyConflicts = $this->monthConflictQuery($member->id, $date->toDateString())->count();

                if ($monthlyConflicts > 0 && ! $override) {
                    $skippedMonthly++;
                    continue;
                }

                Tithe::create($this->payload([
                    'member_id' => $member->id,
                    'amount' => $row['amount'],
                    'contribution_date' => $date->toDateString(),
                    'payment_method' => $row['payment_method'] ?? 'cash',
                    'reference_no' => $row['reference_no'] ?? null,
                    'receipt_no' => $row['receipt_no'] ?? null,
                    'status' => $row['status'] ?? Tithe::STATUS_APPROVED,
                    'notes' => $row['notes'] ?? null,
                    'override_reason' => $monthlyConflicts > 0 ? $overrideReason : null,
                    'send_sms' => false,
                    'tithe_batch_id' => $batch->id,
                    'tithe_year' => $year,
                    'tithe_month' => $month,
                ], $user, $member));

                if ($monthlyConflicts > 0) {
                    $overridden++;
                }

                $saved++;
            }

            return [
                'batch_id' => $batch->id,
                'saved_count' => $saved,
                'skipped_exact_duplicates' => $skippedExact,
                'skipped_monthly_duplicates' => $skippedMonthly,
                'overridden_count' => $overridden,
            ];
        });
    }

    protected function payload(array $data, User $user, Member $member, ?Tithe $existing = null): array
    {
        $status = $data['status'] ?? Tithe::STATUS_PENDING;
        $isApproved = $status === Tithe::STATUS_APPROVED;
        $date = Carbon::parse($data['contribution_date']);

        return [
            'member_id' => $member->id,
            'jumuiya_id' => $member->familia?->jumuiya_id,
            'amount' => $data['amount'],
            'contribution_date' => $date->toDateString(),
            'payment_method' => $data['payment_method'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'receipt_no' => $data['receipt_no'] ?: ($existing?->receipt_no ?: $this->generateReceiptNo()),
            'status' => $status,
            'recorded_by' => $existing?->recorded_by ?: $user->id,
            'approved_by' => $isApproved ? $user->id : null,
            'approved_at' => $isApproved ? now() : null,
            'notes' => $data['notes'] ?? null,
            'override_reason' => $data['override_reason'] ?? null,
            'override_approved_by' => ! empty($data['override_reason']) ? $user->id : null,
            'override_approved_at' => ! empty($data['override_reason']) ? now() : null,
            'tithe_batch_id' => $data['tithe_batch_id'] ?? $existing?->tithe_batch_id,
            'tithe_year' => (int) ($data['tithe_year'] ?? $date->format('Y')),
            'tithe_month' => (int) ($data['tithe_month'] ?? $date->format('n')),
        ];
    }

    protected function generateReceiptNo(): string
    {
        return 'TTH-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }

    protected function messageFor(Member $member, Tithe $tithe): string
    {
        $fallback = sprintf(
            'Mpendwa %s, tumepokea zaka yako ya TZS %s tarehe %s. Risiti: %s. Asante sana.',
            $member->full_name,
            number_format((float) $tithe->amount, 2),
            optional($tithe->contribution_date)->format('d/m/Y'),
            $tithe->receipt_no ?? '-'
        );

        return app(SmsTemplateMessageService::class)->render('finance_tithe_acknowledgement', [
            'member_name' => $member->full_name,
            'amount' => number_format((float) $tithe->amount, 2),
            'date' => optional($tithe->contribution_date)->format('d/m/Y'),
            'receipt_no' => $tithe->receipt_no ?? '-',
        ], $fallback);
    }

    protected function normalizeContributionDate(?string $date): Carbon
    {
        return $date ? Carbon::parse($date) : now()->subMonthNoOverflow();
    }

    protected function denominationTotal(Collection $denominations): float
    {
        return $denominations->sum(fn ($row) => ((float) ($row['value'] ?? 0)) * ((int) ($row['quantity'] ?? 0)));
    }

    protected function exactDuplicateQuery(int $memberId, string $date, float|string $amount, ?int $ignoreId = null)
    {
        $query = Tithe::query()
            ->where('member_id', $memberId)
            ->whereDate('contribution_date', $date)
            ->where('amount', $amount);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query;
    }

    protected function guardExactDuplicate(int $memberId, string $date, float|string $amount, ?int $ignoreId = null): void
    {
        if ($this->exactDuplicateQuery($memberId, $date, $amount, $ignoreId)->exists()) {
            throw ValidationException::withMessages([
                'amount' => db_trans('exact_tithe_duplicate_blocked'),
            ]);
        }
    }

    protected function monthConflictQuery(int $memberId, string $date)
    {
        $carbon = Carbon::parse($date);

        return Tithe::query()
            ->where('member_id', $memberId)
            ->whereYear('contribution_date', $carbon->year)
            ->whereMonth('contribution_date', $carbon->month);
    }

    protected function ensureUserCanAccessTithe(User $user, Tithe $tithe): void
    {
        $scope = $this->access->scopeForUser($user);

        if ($scope->isInvalid()) {
            throw ValidationException::withMessages([
                'tithe' => $scope->reason ?? db_trans('you_are_not_allowed_to_access_this_tithe_record'),
            ]);
        }

        if ($scope->isGlobal()) {
            return;
        }

        if ($scope->isKanda()) {
            $tithe->loadMissing('jumuiya');

            if ((int) $tithe->jumuiya?->kanda_id === (int) $scope->kandaId) {
                return;
            }
        }

        if ($scope->isJumuiya() && (int) $tithe->jumuiya_id === (int) $scope->jumuiyaId) {
            return;
        }

        throw ValidationException::withMessages([
            'tithe' => db_trans('you_are_not_allowed_to_access_this_tithe_record'),
        ]);
    }
}