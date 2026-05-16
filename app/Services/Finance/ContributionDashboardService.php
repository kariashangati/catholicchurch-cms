<?php

namespace App\Services\Finance;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContributionDashboardService
{
    public function __construct(
        protected ContributionAccessService $access
    ) {
    }

    public function getDashboardData(User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $cashQuery = CashContribution::query()->select('cash_contributions.*');
        $bankQuery = BankContribution::query()->select('bank_contributions.*');

        $cashQuery = $this->access->applyMemberScope($cashQuery, $user);
        $bankQuery = $this->access->applyMemberScope($bankQuery, $user);

        if (! empty($filters['contribution_type_id'])) {
            $cashQuery->where('cash_contributions.contribution_type_id', (int) $filters['contribution_type_id']);
            $bankQuery->where('bank_contributions.contribution_type_id', (int) $filters['contribution_type_id']);
        }

        if (! empty($filters['status'])) {
            $cashQuery->where('cash_contributions.status', $filters['status']);
            $bankQuery->where('bank_contributions.status', $filters['status']);
        }

        $cashPeriodQuery = (clone $cashQuery)
            ->whereYear('cash_contributions.contribution_date', $year)
            ->when($month, function (Builder $query) use ($month): void {
                $query->whereMonth('cash_contributions.contribution_date', $month);
            });

        $bankPeriodQuery = (clone $bankQuery)
            ->whereYear('bank_contributions.contribution_date', $year)
            ->when($month, function (Builder $query) use ($month): void {
                $query->whereMonth('bank_contributions.contribution_date', $month);
            });

        return [
            'year' => $year,
            'month' => $month,
            'filters' => [
                'year' => $year,
                'month' => $month,
                'contribution_type_id' => $filters['contribution_type_id'] ?? null,
                'status' => $filters['status'] ?? null,
            ],
            'stats' => [
                'cash_total' => (float) (clone $cashPeriodQuery)->sum('cash_contributions.amount'),
                'bank_total' => (float) (clone $bankPeriodQuery)->sum('bank_contributions.amount'),
                'grand_total' => (float) (clone $cashPeriodQuery)->sum('cash_contributions.amount') + (float) (clone $bankPeriodQuery)->sum('bank_contributions.amount'),
                'cash_count' => (int) (clone $cashPeriodQuery)->count(),
                'bank_count' => (int) (clone $bankPeriodQuery)->count(),
                'type_count' => (int) ContributionType::query()->where('is_active', true)->count(),
            ],
            'monthly' => $this->monthlyBreakdown($cashQuery, $bankQuery, $year),
            'topTypes' => $this->topContributionTypes($cashQuery, $bankQuery, $year, $month),
            'recentCash' => (clone $cashPeriodQuery)
                ->with(['member', 'contributionType'])
                ->latest('cash_contributions.contribution_date')
                ->latest('cash_contributions.id')
                ->limit(100)
                ->get(),
            'recentBank' => (clone $bankPeriodQuery)
                ->with(['member', 'contributionType', 'bankAccount'])
                ->latest('bank_contributions.contribution_date')
                ->latest('bank_contributions.id')
                ->limit(100)
                ->get(),
        ];
    }

public function getCashExportRows(User $user, array $filters = []): Collection
{
    $query = $this->cashExportBaseQuery($user, $filters);

    return $query
        ->with(['member', 'contributionType'])
        ->latest('cash_contributions.contribution_date')
        ->latest('cash_contributions.id')
        ->get()
        ->map(function (CashContribution $item, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member' => $item->member->name ?? $item->member->full_name ?? '—',
                'contribution_type' => $item->contributionType->name ?? '—',
                'amount' => (float) ($item->amount ?? 0),
                'contribution_date' => $item->contribution_date ? $item->contribution_date->format('Y-m-d') : '—',
                'status' => $item->status_label ?? db_trans($item->status ?? CashContribution::STATUS_PENDING),
            ];
        })
        ->values();
}

public function getBankExportRows(User $user, array $filters = []): Collection
{
    $query = $this->bankExportBaseQuery($user, $filters);

    return $query
        ->with(['member', 'contributionType', 'bankAccount'])
        ->latest('bank_contributions.contribution_date')
        ->latest('bank_contributions.id')
        ->get()
        ->map(function (BankContribution $item, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member' => $item->member->name ?? $item->member->full_name ?? '—',
                'contribution_type' => $item->contributionType->name ?? '—',
                'bank_account' => $item->bankAccount->display_name ?? $item->bankAccount->account_name ?? '—',
                'amount' => (float) ($item->amount ?? 0),
                'contribution_date' => $item->contribution_date ? $item->contribution_date->format('Y-m-d') : '—',
                'reference_no' => $item->reference_no ?? '—',
                'status' => $item->status_label ?? db_trans($item->status ?? BankContribution::STATUS_PENDING),
            ];
        })
        ->values();
}

  public function getCashExportPdfData(User $user, array $filters = []): array
{
    $rows = $this->getCashExportRows($user, $filters);
    $filterLabel = $this->filterLabel($filters);

    return [
        'pageTitle' => db_trans('cash_contributions'),
        'reportTitle' => db_trans('cash_contributions_report_title_for') . ' ' . $filterLabel,
        'metaItems' => [
            ['label' => db_trans('filters'), 'value' => $filterLabel],
            ['label' => db_trans('records'), 'value' => number_format($rows->count())],
            ['label' => db_trans('grand_total'), 'value' => number_format((float) $rows->sum('amount'), 2)],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d F Y')],
        ],
        'rows' => $rows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}
public function getBankExportPdfData(User $user, array $filters = []): array
{
    $rows = $this->getBankExportRows($user, $filters);
    $filterLabel = $this->filterLabel($filters);

    return [
        'pageTitle' => db_trans('bank_contributions'),
        'reportTitle' => db_trans('bank_contributions_report_title_for') . ' ' . $filterLabel,
        'metaItems' => [
            ['label' => db_trans('filters'), 'value' => $filterLabel],
            ['label' => db_trans('records'), 'value' => number_format($rows->count())],
            ['label' => db_trans('grand_total'), 'value' => number_format((float) $rows->sum('amount'), 2)],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d F Y')],
        ],
        'rows' => $rows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

    protected function cashExportBaseQuery(User $user, array $filters = []): Builder
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $query = CashContribution::query()->select('cash_contributions.*');
        $query = $this->access->applyMemberScope($query, $user);

        return $query
            ->whereYear('cash_contributions.contribution_date', $year)
            ->when($month, function (Builder $query) use ($month): void {
                $query->whereMonth('cash_contributions.contribution_date', $month);
            })
            ->when(! empty($filters['contribution_type_id']), function (Builder $query) use ($filters): void {
                $query->where('cash_contributions.contribution_type_id', (int) $filters['contribution_type_id']);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('cash_contributions.status', $filters['status']);
            });
    }

    protected function bankExportBaseQuery(User $user, array $filters = []): Builder
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $query = BankContribution::query()->select('bank_contributions.*');
        $query = $this->access->applyMemberScope($query, $user);

        return $query
            ->whereYear('bank_contributions.contribution_date', $year)
            ->when($month, function (Builder $query) use ($month): void {
                $query->whereMonth('bank_contributions.contribution_date', $month);
            })
            ->when(! empty($filters['contribution_type_id']), function (Builder $query) use ($filters): void {
                $query->where('bank_contributions.contribution_type_id', (int) $filters['contribution_type_id']);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('bank_contributions.status', $filters['status']);
            });
    }

    protected function filterLabel(array $filters = []): string
    {
        $parts = [];

        $year = (int) ($filters['year'] ?? now()->year);

        if (! empty($filters['month'])) {
            $parts[] = Carbon::create($year, (int) $filters['month'], 1)->translatedFormat('F Y');
        } else {
            $parts[] = (string) $year;
        }

        if (! empty($filters['contribution_type_id'])) {
            $type = ContributionType::query()->find((int) $filters['contribution_type_id']);
            $parts[] = db_trans('contribution_type') . ': ' . ($type?->name ?? '—');
        } else {
            $parts[] = db_trans('contribution_type') . ': ' . db_trans('all');
        }

        if (! empty($filters['status'])) {
            $parts[] = db_trans('status') . ': ' . (db_trans($filters['status']) ?: ucfirst((string) $filters['status']));
        } else {
            $parts[] = db_trans('status') . ': ' . db_trans('all_statuses');
        }

        return implode(' | ', $parts);
    }

    protected function monthlyBreakdown(Builder $cashQuery, Builder $bankQuery, int $year): Collection
    {
        return collect(range(1, 12))
            ->map(function (int $month) use ($cashQuery, $bankQuery, $year) {
                return [
                    'label' => Carbon::create(null, $month, 1)->translatedFormat('M'),
                    'cash_amount' => (float) (clone $cashQuery)
                        ->whereYear('cash_contributions.contribution_date', $year)
                        ->whereMonth('cash_contributions.contribution_date', $month)
                        ->sum('cash_contributions.amount'),
                    'bank_amount' => (float) (clone $bankQuery)
                        ->whereYear('bank_contributions.contribution_date', $year)
                        ->whereMonth('bank_contributions.contribution_date', $month)
                        ->sum('bank_contributions.amount'),
                ];
            })
            ->map(function (array $row) {
                $row['total_amount'] = (float) $row['cash_amount'] + (float) $row['bank_amount'];

                return $row;
            });
    }

    protected function topContributionTypes(Builder $cashQuery, Builder $bankQuery, int $year, ?int $month = null): Collection
    {
        $cash = (clone $cashQuery)
            ->join('contribution_types', 'contribution_types.id', '=', 'cash_contributions.contribution_type_id')
            ->whereYear('cash_contributions.contribution_date', $year)
            ->when($month, function (Builder $query) use ($month): void {
                $query->whereMonth('cash_contributions.contribution_date', $month);
            })
            ->select('contribution_types.name', DB::raw('SUM(cash_contributions.amount) as amount'))
            ->groupBy('contribution_types.name')
            ->pluck('amount', 'name');

        $bank = (clone $bankQuery)
            ->join('contribution_types', 'contribution_types.id', '=', 'bank_contributions.contribution_type_id')
            ->whereYear('bank_contributions.contribution_date', $year)
            ->when($month, function (Builder $query) use ($month): void {
                $query->whereMonth('bank_contributions.contribution_date', $month);
            })
            ->select('contribution_types.name', DB::raw('SUM(bank_contributions.amount) as amount'))
            ->groupBy('contribution_types.name')
            ->pluck('amount', 'name');

        return $cash->keys()
            ->merge($bank->keys())
            ->unique()
            ->map(function (string $name) use ($cash, $bank) {
                return [
                    'name' => $name,
                    'cash_amount' => (float) ($cash[$name] ?? 0),
                    'bank_amount' => (float) ($bank[$name] ?? 0),
                    'total_amount' => (float) ($cash[$name] ?? 0) + (float) ($bank[$name] ?? 0),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(8)
            ->values();
    }
}