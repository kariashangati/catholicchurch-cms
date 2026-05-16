<?php

namespace App\Services\Finance;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class ContributionComplianceService
{
    public function __construct(
        protected ReportScopeService $scope,
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getPageData(User $user, array $filters): array
    {
        $filters = $this->normalizeHierarchyFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $status = $filters['payment_status'] ?? 'all';
        $source = $filters['source'] ?? 'all';

        $types = ContributionType::query()
            ->where('is_active', true)
            ->with('plan')
            ->orderBy('name')
            ->get();

        [$kandas, $jumuiyas] = $this->scopedHierarchyOptions($user);

        $members = $this->scope->scopedMembers($user)
            ->where('is_active', true)
            ->when(! empty($filters['kanda_id']), fn ($q) => $q->whereHas('familia.jumuiya', fn ($b) => $b->where('kanda_id', $filters['kanda_id'])))
            ->when(! empty($filters['jumuiya_id']), fn ($q) => $q->whereHas('familia', fn ($b) => $b->where('jumuiya_id', $filters['jumuiya_id'])))
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = trim((string) $filters['search']);

                $q->where(function ($builder) use ($search) {
                    $builder->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%")
                        ->orWhere('bahasha', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $memberIds = $members->pluck('id');
        $typeId = $filters['contribution_type_id'] ?? null;

        $cash = CashContribution::query()
            ->whereIn('member_id', $memberIds)
            ->where('status', CashContribution::STATUS_APPROVED)
            ->when($typeId, fn ($q) => $q->where('contribution_type_id', $typeId))
            ->whereYear('contribution_date', $year)
            ->when($month, fn ($q) => $q->whereMonth('contribution_date', $month))
            ->selectRaw('member_id, SUM(amount) total_amount, MAX(contribution_date) last_date')
            ->groupBy('member_id')
            ->get()
            ->keyBy('member_id');

        $bank = BankContribution::query()
            ->whereIn('member_id', $memberIds)
            ->where('status', BankContribution::STATUS_VERIFIED)
            ->when($typeId, fn ($q) => $q->where('contribution_type_id', $typeId))
            ->whereYear('contribution_date', $year)
            ->when($month, fn ($q) => $q->whereMonth('contribution_date', $month))
            ->selectRaw('member_id, SUM(amount) total_amount, MAX(contribution_date) last_date')
            ->groupBy('member_id')
            ->get()
            ->keyBy('member_id');

        $selectedType = $typeId ? $types->firstWhere('id', (int) $typeId) : null;
        $expected = (float) ($selectedType?->plan?->target_amount ?? 0);
        $installments = max(1, (int) ($selectedType?->plan?->installments_count ?? 1));

        $rows = $members->map(function (Member $member) use ($cash, $bank, $expected, $installments, $month) {
            $cashAmount = (float) data_get($cash, $member->id . '.total_amount', 0);
            $bankAmount = (float) data_get($bank, $member->id . '.total_amount', 0);
            $paid = $cashAmount + $bankAmount;
            $expectedAmount = $expected > 0 ? ($month ? round($expected / $installments, 2) : $expected) : 0;

            $paymentStatus = 'unpaid';

            if ($paid > 0 && $expectedAmount > 0 && $paid < $expectedAmount) {
                $paymentStatus = 'partial';
            } elseif ($paid > 0) {
                $paymentStatus = 'paid';
            }

            $paymentSource = 'all';

            if ($cashAmount > 0 && $bankAmount > 0) {
                $paymentSource = 'mixed';
            } elseif ($cashAmount > 0) {
                $paymentSource = 'cash';
            } elseif ($bankAmount > 0) {
                $paymentSource = 'bank';
            }

            $lastPaidAt = collect([
                data_get($cash, $member->id . '.last_date'),
                data_get($bank, $member->id . '.last_date'),
            ])->filter()->map(fn ($date) => Carbon::parse($date))->sortDesc()->first();

            return [
                'member' => $member,
                'expected_amount' => $expectedAmount,
                'paid_amount' => $paid,
                'status' => $paymentStatus,
                'source' => $paymentSource,
                'last_paid_at' => $lastPaidAt,
            ];
        });

        if ($status !== 'all') {
            $rows = $rows->where('status', $status)->values();
        }

        if ($source !== 'all') {
            $rows = $rows->where('source', $source)->values();
        }

        $page = request()->integer('page', 1);
        $perPage = 50;

        $paginated = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return [
            'filters' => [
                'year' => $year,
                'month' => $month,
                'contribution_type_id' => $typeId,
                'kanda_id' => $filters['kanda_id'] ?? null,
                'jumuiya_id' => $filters['jumuiya_id'] ?? null,
                'payment_status' => $status,
                'source' => $source,
                'search' => $filters['search'] ?? null,
            ],
            'types' => $types,
            'kandas' => $kandas,
            'jumuiyas' => $jumuiyas,
            'selectedType' => $selectedType,
            'rows' => $paginated,
            'stats' => [
                'members_total' => $rows->count(),
                'paid_members' => $rows->where('status', 'paid')->count(),
                'partial_members' => $rows->where('status', 'partial')->count(),
                'unpaid_members' => $rows->where('status', 'unpaid')->count(),
                'paid_total' => (float) $rows->sum('paid_amount'),
            ],
        ];
    }

    protected function scopedHierarchyOptions(User $user): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return [collect(), collect()];
        }

        $kandas = Kanda::query()
            ->orderBy('name')
            ->when($scope->isKanda(), fn ($q) => $q->whereKey($scope->kandaId))
            ->when($scope->isJumuiya(), fn ($q) => $q->whereKey($scope->kandaId))
            ->get();

        $jumuiyas = Jumuiya::query()
            ->orderBy('name')
            ->when($scope->isKanda(), fn ($q) => $q->where('kanda_id', $scope->kandaId))
            ->when($scope->isJumuiya(), fn ($q) => $q->whereKey($scope->jumuiyaId))
            ->get();

        return [$kandas, $jumuiyas];
    }

    protected function normalizeHierarchyFilters(User $user, array $filters): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        $filters['kanda_id'] = ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null;
        $filters['jumuiya_id'] = ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null;
        $filters['contribution_type_id'] = ! empty($filters['contribution_type_id']) ? (int) $filters['contribution_type_id'] : null;

        if ($scope->isGlobal()) {
            $this->validateGlobalHierarchy($user, $filters);
            return $filters;
        }

        if ($scope->isKanda()) {
            if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda compliance scope.');
            }

            $filters['kanda_id'] = (int) $scope->kandaId;

            if ($filters['jumuiya_id']) {
                $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);
                $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);
            }

            return $filters;
        }

        if ($scope->isJumuiya()) {
            if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda compliance scope.');
            }

            if ($filters['jumuiya_id'] && (int) $filters['jumuiya_id'] !== (int) $scope->jumuiyaId) {
                throw new AuthorizationException('You cannot access another jumuiya compliance scope.');
            }

            $filters['kanda_id'] = (int) $scope->kandaId;
            $filters['jumuiya_id'] = (int) $scope->jumuiyaId;

            return $filters;
        }

        return $filters;
    }

    protected function validateGlobalHierarchy(User $user, array $filters): void
    {
        if ($filters['jumuiya_id'] && $filters['kanda_id']) {
            $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);

            if ((int) $jumuiya->kanda_id !== (int) $filters['kanda_id']) {
                throw new AuthorizationException('Selected jumuiya does not belong to the selected kanda.');
            }

            $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);
        }
    }
}