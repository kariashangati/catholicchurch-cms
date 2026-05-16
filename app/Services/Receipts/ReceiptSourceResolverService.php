<?php

namespace App\Services\Receipts;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\Offering;
use App\Models\Tithe;
use App\Support\Receipts\ReceiptTypes;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class ReceiptSourceResolverService
{
    public function resolve(string $type, int|string $sourceId): Model
    {
        return match ($type) {
            ReceiptTypes::TITHE => Tithe::query()
                ->with(['member.familia.jumuiya.kanda', 'jumuiya'])
                ->findOrFail($sourceId),

            ReceiptTypes::CASH_CONTRIBUTION => CashContribution::query()
                ->with(['member.familia.jumuiya.kanda', 'contributionType'])
                ->findOrFail($sourceId),

            ReceiptTypes::BANK_CONTRIBUTION => BankContribution::query()
                ->with(['member.familia.jumuiya.kanda', 'contributionType', 'bankAccount'])
                ->findOrFail($sourceId),

            ReceiptTypes::OFFERING => Offering::query()
                ->with(['jumuiya.kanda', 'kanda', 'offeringType', 'massType'])
                ->findOrFail($sourceId),

            default => throw new InvalidArgumentException("Unsupported receipt source type [{$type}]."),
        };
    }

    public function resolveForReceipt(array $payload, ?Authenticatable $user = null): array
    {
        $type = $payload['receipt_type'] ?? $payload['source_type'] ?? null;

        if (! $type) {
            throw new InvalidArgumentException('Receipt type is required.');
        }

        return match ($type) {
            ReceiptTypes::TITHE => $this->resolveTithes($payload),
            ReceiptTypes::CASH_CONTRIBUTION => $this->resolveCashContributions($payload),
            ReceiptTypes::BANK_CONTRIBUTION => $this->resolveBankContributions($payload),
            ReceiptTypes::OFFERING => $this->resolveOfferings($payload),
            ReceiptTypes::PROJECT_CONTRIBUTION => $this->resolveProjectContributions($payload),

            // keep explicit until a dedicated grouped summary builder is added
            ReceiptTypes::GROUP_SUMMARY => throw new InvalidArgumentException(
                'Group summary preview is not yet supported by the current resolver.'
            ),

            default => throw new InvalidArgumentException("Unsupported receipt source type [{$type}]."),
        };
    }

    protected function resolveTithes(array $payload): array
    {
        $query = Tithe::query()->with(['member.familia.jumuiya.kanda', 'jumuiya']);

        $this->applyCommonHierarchyFilters($query, $payload);
        $this->applyPeriodFilter($query, new Tithe(), $payload);

        $rows = $query->get()->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                'member_id' => $item->member_id ?? null,
                'familia_id' => $item->familia_id ?? $item->member?->familia_id,
                'jumuiya_id' => $item->jumuiya_id ?? $item->member?->jumuiya_id ?? $item->jumuiya?->id,
                'kanda_id' => $item->kanda_id ?? $item->member?->kanda_id ?? $item->jumuiya?->kanda_id,
                'date' => $this->extractDateValue($item),
                'reference_no' => $item->reference_no ?? null,
            ];
        })->values()->all();

        return $this->buildResolvedPayload(ReceiptTypes::TITHE, $rows, $payload);
    }

    protected function resolveCashContributions(array $payload): array
    {
        $query = CashContribution::query()->with(['member.familia.jumuiya.kanda', 'contributionType']);

        $this->applyCommonHierarchyFilters($query, $payload);
        $this->applyContributionTypeFilter($query, $payload);
        $this->applyPeriodFilter($query, new CashContribution(), $payload);

        $rows = $query->get()->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                'member_id' => $item->member_id ?? null,
                'familia_id' => $item->familia_id ?? $item->member?->familia_id,
                'jumuiya_id' => $item->jumuiya_id ?? $item->member?->jumuiya_id,
                'kanda_id' => $item->kanda_id ?? $item->member?->kanda_id,
                'contribution_type_id' => $item->contribution_type_id ?? null,
                'date' => $this->extractDateValue($item),
                'reference_no' => $item->reference_no ?? null,
            ];
        })->values()->all();

        return $this->buildResolvedPayload(ReceiptTypes::CASH_CONTRIBUTION, $rows, $payload);
    }

    protected function resolveBankContributions(array $payload): array
    {
        $query = BankContribution::query()->with(['member.familia.jumuiya.kanda', 'contributionType', 'bankAccount']);

        $this->applyCommonHierarchyFilters($query, $payload);
        $this->applyContributionTypeFilter($query, $payload);
        $this->applyPeriodFilter($query, new BankContribution(), $payload);

        $rows = $query->get()->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                'member_id' => $item->member_id ?? null,
                'familia_id' => $item->familia_id ?? $item->member?->familia_id,
                'jumuiya_id' => $item->jumuiya_id ?? $item->member?->jumuiya_id,
                'kanda_id' => $item->kanda_id ?? $item->member?->kanda_id,
                'contribution_type_id' => $item->contribution_type_id ?? null,
                'date' => $this->extractDateValue($item),
                'reference_no' => $item->reference_no ?? null,
            ];
        })->values()->all();

        return $this->buildResolvedPayload(ReceiptTypes::BANK_CONTRIBUTION, $rows, $payload);
    }

    protected function resolveProjectContributions(array $payload): array
    {
        $cash = CashContribution::query()->with(['member.familia.jumuiya.kanda', 'contributionType']);
        $bank = BankContribution::query()->with(['member.familia.jumuiya.kanda', 'contributionType', 'bankAccount']);

        $this->applyCommonHierarchyFilters($cash, $payload);
        $this->applyCommonHierarchyFilters($bank, $payload);

        $this->applyContributionTypeFilter($cash, $payload);
        $this->applyContributionTypeFilter($bank, $payload);

        $this->applyPeriodFilter($cash, new CashContribution(), $payload);
        $this->applyPeriodFilter($bank, new BankContribution(), $payload);

        $rows = $cash->get()
            ->map(fn ($item) => [
                'id' => 'cash-' . $item->getKey(),
                'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                'member_id' => $item->member_id ?? null,
                'familia_id' => $item->familia_id ?? $item->member?->familia_id,
                'jumuiya_id' => $item->jumuiya_id ?? $item->member?->jumuiya_id,
                'kanda_id' => $item->kanda_id ?? $item->member?->kanda_id,
                'contribution_type_id' => $item->contribution_type_id ?? null,
                'date' => $this->extractDateValue($item),
                'reference_no' => $item->reference_no ?? null,
            ])
            ->merge(
                $bank->get()->map(fn ($item) => [
                    'id' => 'bank-' . $item->getKey(),
                    'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                    'member_id' => $item->member_id ?? null,
                    'familia_id' => $item->familia_id ?? $item->member?->familia_id,
                    'jumuiya_id' => $item->jumuiya_id ?? $item->member?->jumuiya_id,
                    'kanda_id' => $item->kanda_id ?? $item->member?->kanda_id,
                    'contribution_type_id' => $item->contribution_type_id ?? null,
                    'date' => $this->extractDateValue($item),
                    'reference_no' => $item->reference_no ?? null,
                ])
            )
            ->values()
            ->all();

        return $this->buildResolvedPayload(ReceiptTypes::PROJECT_CONTRIBUTION, $rows, $payload);
    }

    protected function resolveOfferings(array $payload): array
    {
        $query = Offering::query()->with(['jumuiya.kanda', 'kanda', 'offeringType', 'massType']);

        if (! empty($payload['jumuiya_id'])) {
            $query->where('jumuiya_id', $payload['jumuiya_id']);
        }

        if (! empty($payload['kanda_id'])) {
            $query->where('kanda_id', $payload['kanda_id']);
        }

        $this->applyPeriodFilter($query, new Offering(), $payload);

        $rows = $query->get()->map(function ($item) {
            return [
                'id' => $item->getKey(),
                'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                'member_id' => $item->member_id ?? null,
                'familia_id' => $item->familia_id ?? null,
                'jumuiya_id' => $item->jumuiya_id ?? $item->jumuiya?->id,
                'kanda_id' => $item->kanda_id ?? $item->kanda?->id ?? $item->jumuiya?->kanda_id,
                'date' => $this->extractDateValue($item),
                'reference_no' => $item->reference_no ?? null,
            ];
        })->values()->all();

        return $this->buildResolvedPayload(ReceiptTypes::OFFERING, $rows, $payload);
    }

    protected function applyCommonHierarchyFilters(Builder $query, array $payload): void
    {
        if (! empty($payload['member_id'])) {
            $query->where('member_id', $payload['member_id']);
        }

        if (! empty($payload['familia_id']) && Schema::hasColumn($query->getModel()->getTable(), 'familia_id')) {
            $query->where('familia_id', $payload['familia_id']);
        }

        if (! empty($payload['jumuiya_id']) && Schema::hasColumn($query->getModel()->getTable(), 'jumuiya_id')) {
            $query->where('jumuiya_id', $payload['jumuiya_id']);
        }

        if (! empty($payload['kanda_id']) && Schema::hasColumn($query->getModel()->getTable(), 'kanda_id')) {
            $query->where('kanda_id', $payload['kanda_id']);
        }
    }

    protected function applyContributionTypeFilter(Builder $query, array $payload): void
    {
        if (! empty($payload['contribution_type_id']) && Schema::hasColumn($query->getModel()->getTable(), 'contribution_type_id')) {
            $query->where('contribution_type_id', $payload['contribution_type_id']);
        }
    }

    protected function applyPeriodFilter(Builder $query, Model $model, array $payload): void
    {
        $table = $model->getTable();
        $dateColumn = $this->detectDateColumn($table);

        if (! $dateColumn) {
            return;
        }

        if (! empty($payload['date_from'])) {
            $query->whereDate($dateColumn, '>=', $payload['date_from']);
        }

        if (! empty($payload['date_to'])) {
            $query->whereDate($dateColumn, '<=', $payload['date_to']);
        }

        if (! empty($payload['month'])) {
            $query->whereMonth($dateColumn, (int) $payload['month']);
        }

        if (! empty($payload['year'])) {
            $query->whereYear($dateColumn, (int) $payload['year']);
        }
    }

    protected function detectDateColumn(string $table): ?string
    {
        foreach (['contribution_date', 'payment_date', 'offering_date', 'date', 'paid_at', 'created_at'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function extractDateValue(Model $model): ?string
    {
        foreach (['contribution_date', 'payment_date', 'offering_date', 'date', 'paid_at', 'created_at'] as $column) {
            if (isset($model->{$column}) && $model->{$column}) {
                return (string) $model->{$column};
            }
        }

        return null;
    }

    protected function buildResolvedPayload(string $type, array $rows, array $payload): array
    {
        $first = $rows[0] ?? [];

        return [
            'source_type' => $type,
            'source_id' => count($rows) === 1 ? ($first['id'] ?? null) : null,
            'member_id' => $payload['member_id'] ?? ($first['member_id'] ?? null),
            'familia_id' => $payload['familia_id'] ?? ($first['familia_id'] ?? null),
            'jumuiya_id' => $payload['jumuiya_id'] ?? ($first['jumuiya_id'] ?? null),
            'kanda_id' => $payload['kanda_id'] ?? ($first['kanda_id'] ?? null),
            'rows' => $rows,
        ];
    }
}