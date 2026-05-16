<?php

namespace App\Services\Finance;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class GiversNonGiversReportService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ContributionAccessService $contributionAccess,
    ) {
    }

    public function getPageData(User $user, array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        [$kandas, $jumuiyas] = $this->scopedHierarchyOptions($user, $filters);
        $this->validateHierarchy($filters);

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = $this->reportRows($user, $filters);

        return [
            'pageTitle' => db_trans('waliotoa_wasiotoa'),
            'filters' => $filters,
            'kandas' => $kandas,
            'jumuiyas' => $jumuiyas,
            'types' => $types,
            'dataOptions' => $this->dataOptions($types),
            'selectedDataLabel' => $this->selectedDataLabel($filters['data_type'], $types),
            'showJumuiyaColumn' => empty($filters['jumuiya_id']),
            'rows' => $rows,
            'stats' => [
                'total_members' => $rows->count(),
                'total_amount' => (float) $rows->sum('amount'),
                'givers_count' => $rows->where('has_given', true)->count(),
                'non_givers_count' => $rows->where('has_given', false)->count(),
            ],
        ];
    }

    public function getExportRows(User $user, array $filters): Collection
    {
        $filters = $this->normalizeFilters($filters);

        return $this->reportRows($user, $filters)
            ->values()
            ->map(function ($row, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'member' => $row->member_name,
                    'member_code' => $row->member?->member_code ?? '—',
                    'phone' => $row->phone ?: '—',
                    'jumuiya' => $row->jumuiya_name ?: '—',
                    'date' => $row->last_date ? Carbon::parse($row->last_date)->format('Y-m-d') : '—',
                    'amount' => (float) $row->amount,
                    'status' => $row->has_given ? db_trans('ametoa') : db_trans('hajatoa'),
                    'has_given' => (bool) $row->has_given,
                ];
            });
    }

    public function getExportPdfData(User $user, array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = $this->getExportRows($user, $filters);
        $selectedDataLabel = $this->selectedDataLabel($filters['data_type'], $types);
        $filterLabel = $this->filterLabel($filters, $types);

        return [
            'pageTitle' => db_trans('waliotoa_wasiotoa'),
            'reportTitle' => db_trans('waliotoa_wasiotoa_report_title_for') . ' ' . $filterLabel,
            'metaItems' => [
                [
                    'label' => db_trans('filters'),
                    'value' => $filterLabel,
                ],
                [
                    'label' => db_trans('report_type'),
                    'value' => $selectedDataLabel,
                ],
                [
                    'label' => db_trans('records'),
                    'value' => number_format($rows->count()),
                ],
                [
                    'label' => db_trans('waliotoa'),
                    'value' => number_format($rows->where('has_given', true)->count()),
                ],
                [
                    'label' => db_trans('wasiotoa'),
                    'value' => number_format($rows->where('has_given', false)->count()),
                ],
                [
                    'label' => db_trans('total_amount'),
                    'value' => number_format((float) $rows->sum('amount'), 2),
                ],
                [
                    'label' => db_trans('generated_on'),
                    'value' => now()->translatedFormat('d F Y'),
                ],
            ],
            'rows' => $rows,
            'selectedDataLabel' => $selectedDataLabel,
            'showJumuiyaColumn' => empty($filters['jumuiya_id']),
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function selectedExportDataLabel(array $filters): string
    {
        $filters = $this->normalizeFilters($filters);

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->selectedDataLabel($filters['data_type'], $types);
    }

    public function shouldShowJumuiyaColumn(array $filters): bool
    {
        $filters = $this->normalizeFilters($filters);

        return empty($filters['jumuiya_id']);
    }

    protected function reportRows(User $user, array $filters): Collection
    {
        $filters = $this->normalizeFilters($filters);

        $membersQuery = $this->scopedMembersQuery($user, $filters);

        $members = $membersQuery
            ->with(['familia.jumuiya.kanda'])
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get();

        $memberIds = $members->pluck('id')->values();
        $amounts = $this->amountsByMember($filters, $memberIds);

        $rows = $members->map(function (Member $member) use ($amounts) {
            $payment = $amounts->get((int) $member->id, [
                'amount' => 0.0,
                'last_date' => null,
            ]);

            $amount = (float) ($payment['amount'] ?? 0);

            return (object) [
                'member' => $member,
                'member_name' => $this->memberName($member),
                'phone' => $member->phone,
                'jumuiya_name' => $member->familia?->jumuiya?->name,
                'kanda_name' => $member->familia?->jumuiya?->kanda?->name,
                'amount' => $amount,
                'last_date' => $payment['last_date'] ?? null,
                'has_given' => $amount > 0,
            ];
        });

        if ($filters['giver_status'] === 'waliotoa') {
            $rows = $rows->where('has_given', true)->values();
        }

        if ($filters['giver_status'] === 'wasiotoa') {
            $rows = $rows->where('has_given', false)->values();
        }

        return $rows->values();
    }

    protected function normalizeFilters(array $filters): array
    {
        return [
            'kanda_id' => ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null,
            'jumuiya_id' => ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null,
            'data_type' => ! empty($filters['data_type']) ? (string) $filters['data_type'] : 'tithe',
            'year' => ! empty($filters['year']) ? (int) $filters['year'] : now()->year,
            'month' => ! empty($filters['month']) ? (int) $filters['month'] : null,
            'giver_status' => in_array(($filters['giver_status'] ?? 'all'), ['all', 'waliotoa', 'wasiotoa'], true)
                ? (string) $filters['giver_status']
                : 'all',
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

        $jumuiyas = Jumuiya::query()
            ->orderBy('name')
            ->when($scope->isGlobal(), function (Builder $query) use ($filters): void {
                if (! empty($filters['kanda_id'])) {
                    $query->where('kanda_id', (int) $filters['kanda_id']);
                }
            })
            ->when($scope->isKanda(), fn (Builder $query) => $query->where('kanda_id', (int) $scope->kandaId))
            ->when($scope->isJumuiya(), fn (Builder $query) => $query->whereKey((int) $scope->jumuiyaId))
            ->get();

        return [$kandas, $jumuiyas];
    }

    protected function validateHierarchy(array $filters): void
    {
        if (! empty($filters['kanda_id']) && ! empty($filters['jumuiya_id'])) {
            $valid = Jumuiya::query()
                ->whereKey((int) $filters['jumuiya_id'])
                ->where('kanda_id', (int) $filters['kanda_id'])
                ->exists();

            if (! $valid) {
                throw ValidationException::withMessages([
                    'jumuiya_id' => db_trans('selected_jumuiya_does_not_belong_to_kanda'),
                ]);
            }
        }
    }

    protected function scopedMembersQuery(User $user, array $filters): Builder
    {
        $query = Member::query()->where('is_active', true);

        $this->contributionAccess->applyMemberScope($query, $user);

        return $query
            ->when(! empty($filters['kanda_id']), function (Builder $query) use ($filters): void {
                $query->whereHas('familia.jumuiya', fn (Builder $builder) => $builder->where('kanda_id', (int) $filters['kanda_id']));
            })
            ->when(! empty($filters['jumuiya_id']), function (Builder $query) use ($filters): void {
                $query->whereHas('familia', fn (Builder $builder) => $builder->where('jumuiya_id', (int) $filters['jumuiya_id']));
            });
    }

    protected function amountsByMember(array $filters, Collection $memberIds): Collection
    {
        if ($memberIds->isEmpty()) {
            return collect();
        }

        if ($filters['data_type'] === 'tithe') {
            return $this->titheAmountsByMember($filters, $memberIds);
        }

        if (str_starts_with($filters['data_type'], 'contribution_')) {
            $typeId = (int) str_replace('contribution_', '', $filters['data_type']);

            return $this->contributionAmountsByMember($filters, $memberIds, $typeId);
        }

        return collect();
    }

    protected function titheAmountsByMember(array $filters, Collection $memberIds): Collection
    {
        return Tithe::query()
            ->whereIn('member_id', $memberIds)
            ->whereYear('contribution_date', (int) $filters['year'])
            ->when(! empty($filters['month']), fn (Builder $query) => $query->whereMonth('contribution_date', (int) $filters['month']))
            ->whereIn('status', ['approved', 'imeidhinishwa'])
            ->selectRaw('member_id, SUM(amount) as amount, MAX(contribution_date) as last_date')
            ->groupBy('member_id')
            ->get()
            ->keyBy('member_id')
            ->map(fn ($row) => [
                'amount' => (float) $row->amount,
                'last_date' => $row->last_date,
            ]);
    }

    protected function contributionAmountsByMember(array $filters, Collection $memberIds, int $typeId): Collection
    {
        $cash = CashContribution::query()
            ->whereIn('member_id', $memberIds)
            ->where('contribution_type_id', $typeId)
            ->whereYear('contribution_date', (int) $filters['year'])
            ->when(! empty($filters['month']), fn (Builder $query) => $query->whereMonth('contribution_date', (int) $filters['month']))
            ->whereIn('status', ['approved', 'imeidhinishwa'])
            ->selectRaw('member_id, SUM(amount) as amount, MAX(contribution_date) as last_date')
            ->groupBy('member_id')
            ->get()
            ->keyBy('member_id');

        $bank = BankContribution::query()
            ->whereIn('member_id', $memberIds)
            ->where('contribution_type_id', $typeId)
            ->whereYear('contribution_date', (int) $filters['year'])
            ->when(! empty($filters['month']), fn (Builder $query) => $query->whereMonth('contribution_date', (int) $filters['month']))
            ->whereIn('status', ['verified', 'imethibitishwa'])
            ->selectRaw('member_id, SUM(amount) as amount, MAX(contribution_date) as last_date')
            ->groupBy('member_id')
            ->get()
            ->keyBy('member_id');

        return $memberIds->mapWithKeys(function ($memberId) use ($cash, $bank) {
            $cashRow = $cash->get($memberId);
            $bankRow = $bank->get($memberId);

            $lastDates = collect([$cashRow?->last_date, $bankRow?->last_date])
                ->filter()
                ->sortDesc()
                ->values();

            return [
                (int) $memberId => [
                    'amount' => (float) ($cashRow?->amount ?? 0) + (float) ($bankRow?->amount ?? 0),
                    'last_date' => $lastDates->first(),
                ],
            ];
        });
    }

    protected function dataOptions(Collection $types): array
    {
        return collect([
            [
                'value' => 'tithe',
                'label' => db_trans('tithes'),
            ],
        ])
            ->merge($types->map(fn (ContributionType $type) => [
                'value' => 'contribution_' . $type->id,
                'label' => $type->name,
            ]))
            ->values()
            ->all();
    }

    protected function selectedDataLabel(string $dataType, Collection $types): string
    {
        if ($dataType === 'tithe') {
            return db_trans('tithes');
        }

        if (str_starts_with($dataType, 'contribution_')) {
            $typeId = (int) str_replace('contribution_', '', $dataType);

            return $types->firstWhere('id', $typeId)?->name ?: db_trans('amount');
        }

        return db_trans('amount');
    }

    protected function filterLabel(array $filters, Collection $types): string
    {
        $parts = [];

        $year = (int) ($filters['year'] ?? now()->year);

        if (! empty($filters['month'])) {
            $parts[] = Carbon::create($year, (int) $filters['month'], 1)->translatedFormat('F Y');
        } else {
            $parts[] = (string) $year;
        }

        $parts[] = db_trans('report_type') . ': ' . $this->selectedDataLabel((string) $filters['data_type'], $types);

        if (! empty($filters['kanda_id'])) {
            $kanda = Kanda::query()->find((int) $filters['kanda_id']);
            $parts[] = db_trans('kanda') . ': ' . ($kanda?->name ?? '—');
        } else {
            $parts[] = db_trans('kanda') . ': ' . db_trans('all');
        }

        if (! empty($filters['jumuiya_id'])) {
            $jumuiya = Jumuiya::query()->find((int) $filters['jumuiya_id']);
            $parts[] = db_trans('jumuiya') . ': ' . ($jumuiya?->name ?? '—');
        } else {
            $parts[] = db_trans('jumuiya') . ': ' . db_trans('all');
        }

        $parts[] = db_trans('status') . ': ' . match ($filters['giver_status'] ?? 'all') {
            'waliotoa' => db_trans('waliotoa'),
            'wasiotoa' => db_trans('wasiotoa'),
            default => db_trans('all'),
        };

        return implode(' | ', $parts);
    }

    protected function memberName(Member $member): string
    {
        return $member->full_name
            ?? trim(collect([$member->first_name, $member->middle_name, $member->last_name])->filter()->implode(' '))
            ?: ('#' . $member->id);
    }
}