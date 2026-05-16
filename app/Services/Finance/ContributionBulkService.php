<?php

namespace App\Services\Finance;

use App\Models\BankAccount;
use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionBatch;
use App\Models\ContributionBatchDenomination;
use App\Models\ContributionType;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContributionBulkService
{
    public function __construct(
        protected ContributionAccessService $access,
        protected ContributionSmsService $sms,
        protected ScopeAccessGate $scopeAccessGate,
        protected UserScopeResolver $scopeResolver,
    ) {
    }

    public function pageData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        [$kandas, $jumuiyas] = $this->scopedHierarchyOptions($user, $filters);

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::query()
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        $rows = collect();
        $existingMembers = collect();

        if (
            ! empty($filters['kanda_id'])
            && ! empty($filters['jumuiya_id'])
            && ! empty($filters['contribution_type_id'])
            && ! empty($filters['source_type'])
            && ! empty($filters['contribution_date'])
        ) {
            $jumuiya = Jumuiya::query()->findOrFail((int) $filters['jumuiya_id']);
            $this->access->ensureCanAccessJumuiya($user, (int) $jumuiya->id);

            if ((int) $jumuiya->kanda_id !== (int) $filters['kanda_id']) {
                throw ValidationException::withMessages([
                    'jumuiya_id' => db_trans('selected_jumuiya_does_not_belong_to_kanda'),
                ]);
            }

            $rows = Member::query()
                ->whereHas('familia', fn (Builder $query) => $query->where('jumuiya_id', (int) $filters['jumuiya_id']))
                ->with('familia')
                ->orderBy('first_name')
                ->orderBy('middle_name')
                ->orderBy('last_name')
                ->get();

            $existingMembers = $this->existingForPeriod($filters);
        }

        return [
            'pageTitle' => db_trans('bulk_contributions'),
            'filters' => $filters,
            'kandas' => $kandas,
            'jumuiyas' => $jumuiyas,
            'types' => $types,
            'bankAccounts' => $bankAccounts,
            'rows' => $rows,
            'existingMembers' => $existingMembers,
            'denominationOptions' => [100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100, 50],
            'sourceOptions' => ContributionBatch::availableSources(),
            'cashStatuses' => CashContribution::availableStatuses(),
            'bankStatuses' => BankContribution::availableStatuses(),
        ];
    }

    public function store(array $payload, User $user): array
    {
        $date = Carbon::parse($payload['contribution_date']);
        $source = (string) $payload['source_type'];
        $status = $payload['status'] ?? ($source === ContributionBatch::SOURCE_BANK ? BankContribution::STATUS_VERIFIED : CashContribution::STATUS_APPROVED);

        $jumuiya = Jumuiya::with('kanda')->findOrFail((int) $payload['jumuiya_id']);
        $this->access->ensureCanAccessJumuiya($user, (int) $jumuiya->id);

        if ((int) $jumuiya->kanda_id !== (int) $payload['kanda_id']) {
            throw ValidationException::withMessages([
                'jumuiya_id' => db_trans('selected_jumuiya_does_not_belong_to_kanda'),
            ]);
        }

        if ($source === ContributionBatch::SOURCE_BANK && empty($payload['bank_account_id'])) {
            throw ValidationException::withMessages([
                'bank_account_id' => db_trans('bank_account_required'),
            ]);
        }

        $rows = collect($payload['rows'])
            ->filter(fn ($row) => (float) ($row['amount'] ?? 0) > 0)
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'rows' => db_trans('please_enter_at_least_one_amount'),
            ]);
        }

        $expectedTotal = round((float) $payload['expected_total'], 2);
        $enteredTotal = round((float) $rows->sum(fn ($row) => (float) ($row['amount'] ?? 0)), 2);
        $denominationTotal = round($this->denominationTotal(collect($payload['denominations'])), 2);

        if ($expectedTotal !== $enteredTotal) {
            throw ValidationException::withMessages([
                'expected_total' => db_trans('expected_total_must_match_entered_total'),
            ]);
        }

        if ($expectedTotal !== $denominationTotal) {
            throw ValidationException::withMessages([
                'denominations' => db_trans('denomination_total_must_match_contribution_total'),
            ]);
        }

        return DB::transaction(function () use ($payload, $user, $date, $source, $status, $rows, $expectedTotal) {
            $batch = ContributionBatch::create([
                'kanda_id' => (int) $payload['kanda_id'],
                'jumuiya_id' => (int) $payload['jumuiya_id'],
                'contribution_type_id' => (int) $payload['contribution_type_id'],
                'source_type' => $source,
                'bank_account_id' => $source === ContributionBatch::SOURCE_BANK ? (int) $payload['bank_account_id'] : null,
                'contribution_date' => $date->toDateString(),
                'period_year' => (int) $date->format('Y'),
                'period_month' => (int) $date->format('n'),
                'total_amount' => $expectedTotal,
                'rows_count' => 0,
                'recorded_by' => $user->id,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($payload['denominations'] as $denom) {
                $qty = (int) ($denom['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                ContributionBatchDenomination::create([
                    'contribution_batch_id' => $batch->id,
                    'denomination_value' => (float) $denom['value'],
                    'quantity' => $qty,
                    'total_amount' => round(((float) $denom['value']) * $qty, 2),
                ]);
            }

            $saved = 0;
            $skippedExact = 0;
            $sendSms = (bool) ($payload['send_sms'] ?? false);

            foreach ($rows as $row) {
                $member = Member::with('familia.jumuiya')->findOrFail((int) $row['member_id']);
                $this->access->ensureCanAccessMember($user, (int) $member->id);

                if ((int) $member->familia?->jumuiya_id !== (int) $payload['jumuiya_id']) {
                    continue;
                }

                if ($this->exactDuplicateExists($source, (int) $member->id, (int) $payload['contribution_type_id'], $date->toDateString(), (float) $row['amount'])) {
                    $skippedExact++;
                    continue;
                }

                if ($source === ContributionBatch::SOURCE_BANK) {
                    $contribution = BankContribution::create([
                        'member_id' => $member->id,
                        'familia_id' => $member->familia_id,
                        'jumuiya_id' => (int) $payload['jumuiya_id'],
                        'kanda_id' => (int) $payload['kanda_id'],
                        'contribution_type_id' => (int) $payload['contribution_type_id'],
                        'bank_account_id' => (int) $payload['bank_account_id'],
                        'amount' => (float) $row['amount'],
                        'contribution_date' => $date->toDateString(),
                        'reference_no' => 'BULK-' . $batch->id . '-' . $member->id . '-' . now()->format('His'),
                        'receipt_no' => null,
                        'notes' => $row['notes'] ?? null,
                        'status' => BankContribution::normalizeStatus($status),
                        'recorded_by' => $user->id,
                        'approved_by' => BankContribution::normalizeStatus($status) === BankContribution::STATUS_VERIFIED ? $user->id : null,
                        'approved_at' => BankContribution::normalizeStatus($status) === BankContribution::STATUS_VERIFIED ? now() : null,
                        'contribution_batch_id' => $batch->id,
                    ])->load('contributionType');

                    if ($sendSms) {
                        $this->sms->sendAcknowledgement($member, $this->sms->bankMessage($member, $contribution), $user);
                    }
                } else {
                    $contribution = CashContribution::create([
                        'member_id' => $member->id,
                        'familia_id' => $member->familia_id,
                        'jumuiya_id' => (int) $payload['jumuiya_id'],
                        'kanda_id' => (int) $payload['kanda_id'],
                        'contribution_type_id' => (int) $payload['contribution_type_id'],
                        'amount' => (float) $row['amount'],
                        'contribution_date' => $date->toDateString(),
                        'notes' => $row['notes'] ?? null,
                        'status' => CashContribution::normalizeStatus($status),
                        'recorded_by' => $user->id,
                        'approved_by' => CashContribution::normalizeStatus($status) === CashContribution::STATUS_APPROVED ? $user->id : null,
                        'approved_at' => CashContribution::normalizeStatus($status) === CashContribution::STATUS_APPROVED ? now() : null,
                        'contribution_batch_id' => $batch->id,
                    ])->load('contributionType');

                    if ($sendSms) {
                        $this->sms->sendAcknowledgement($member, $this->sms->cashMessage($member, $contribution), $user);
                    }
                }

                $saved++;
            }

            $this->syncBatchAfterContributionChange((int) $batch->id);

            return [
                'batch_id' => $batch->id,
                'saved_count' => $saved,
                'skipped_exact_duplicates' => $skippedExact,
            ];
        });
    }

    public function assertContributionIsNotLockedByBatch(object $contribution): void
    {
        if (! empty($contribution->contribution_batch_id)) {
            throw ValidationException::withMessages([
                'contribution_batch_id' => db_trans('bulk_contribution_record_locked'),
            ]);
        }
    }

    public function syncBatchAfterContributionChange(int $batchId): void
    {
        $batch = ContributionBatch::query()->find($batchId);

        if (! $batch) {
            return;
        }

        $query = $batch->source_type === ContributionBatch::SOURCE_BANK
            ? BankContribution::query()->where('contribution_batch_id', $batch->id)
            : CashContribution::query()->where('contribution_batch_id', $batch->id);

        $items = $query->get();

        if ($items->isEmpty()) {
            $batch->denominations()->delete();
            $batch->delete();
            return;
        }

        $batch->update([
            'rows_count' => $items->count(),
            'total_amount' => $items->sum(fn ($item) => (float) $item->amount),
        ]);
    }

    protected function normalizeFilters(array $filters): array
    {
        $source = ! empty($filters['source_type']) ? (string) $filters['source_type'] : ContributionBatch::SOURCE_CASH;

        if (! in_array($source, ContributionBatch::availableSources(), true)) {
            $source = ContributionBatch::SOURCE_CASH;
        }

        return [
            'kanda_id' => ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null,
            'jumuiya_id' => ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null,
            'contribution_type_id' => ! empty($filters['contribution_type_id']) ? (int) $filters['contribution_type_id'] : null,
            'source_type' => $source,
            'bank_account_id' => ! empty($filters['bank_account_id']) ? (int) $filters['bank_account_id'] : null,
            'contribution_date' => $filters['contribution_date'] ?? now()->toDateString(),
        ];
    }

  protected function scopedHierarchyOptions(User $user, array $filters): array
{
    $scope = $this->scopeResolver->resolve($user);

    if ($scope->isInvalid()) {
        return [collect(), collect()];
    }

    $kandas = Kanda::query()
        ->orderBy('name')
        ->when($scope->isKanda(), fn (Builder $query) => $query->whereKey((int) $scope->kandaId))
        ->when($scope->isJumuiya(), fn (Builder $query) => $query->whereKey((int) $scope->kandaId))
        ->get();

    /*
     * Important:
     * We load all scoped Jumuiya options here, then the Blade JavaScript filters them
     * after the user selects Kanda. Do not return empty jumuiyas on initial page load.
     */
    $jumuiyas = Jumuiya::query()
        ->orderBy('name')
        ->when($scope->isKanda(), fn (Builder $query) => $query->where('kanda_id', (int) $scope->kandaId))
        ->when($scope->isJumuiya(), fn (Builder $query) => $query->whereKey((int) $scope->jumuiyaId))
        ->get();

    return [$kandas, $jumuiyas];
}

    protected function existingForPeriod(array $filters): Collection
    {
        $source = (string) $filters['source_type'];
        $query = $source === ContributionBatch::SOURCE_BANK
            ? BankContribution::query()
            : CashContribution::query();

        return $query
            ->where('jumuiya_id', (int) $filters['jumuiya_id'])
            ->where('contribution_type_id', (int) $filters['contribution_type_id'])
            ->whereDate('contribution_date', $filters['contribution_date'])
            ->get()
            ->groupBy('member_id');
    }

    protected function exactDuplicateExists(string $source, int $memberId, int $typeId, string $date, float $amount): bool
    {
        $query = $source === ContributionBatch::SOURCE_BANK
            ? BankContribution::query()
            : CashContribution::query();

        return $query
            ->where('member_id', $memberId)
            ->where('contribution_type_id', $typeId)
            ->whereDate('contribution_date', $date)
            ->where('amount', $amount)
            ->exists();
    }

    protected function denominationTotal(Collection $denominations): float
    {
        return $denominations->sum(fn ($row) => ((float) ($row['value'] ?? 0)) * ((int) ($row['quantity'] ?? 0)));
    }
}