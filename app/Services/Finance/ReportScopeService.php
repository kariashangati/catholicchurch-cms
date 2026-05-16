<?php

namespace App\Services\Finance;

use App\Models\Member;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;

class ReportScopeService
{
    public function __construct(
        protected ContributionAccessService $contributionAccess,
        protected TitheAccessService $titheAccess,
        protected FinanceAccessService $financeAccess,
        protected UserScopeResolver $scopeResolver,
    ) {
    }

    public function applyMemberScope(Builder $query, User $user): Builder
    {
        return $this->contributionAccess->applyMemberScope($query, $user);
    }

    public function applyTitheScope(Builder $query, User $user): Builder
    {
        $scope = $this->titheAccess->scopeForUser($user);

        return $this->titheAccess->applyScope($query, $scope);
    }

    public function applyOfferingScope(Builder $query, User $user): Builder
    {
        $scope = $this->financeAccess->scopeForUser($user);

        return $this->financeAccess->applyOfferingScope($query, $scope);
    }

    public function scopedMembers(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Member::query()->with(['familia.jumuiya.kanda']);

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereHas('familia.jumuiya', function (Builder $builder) use ($scope) {
                $builder->where('kanda_id', (int) $scope->kandaId);
            });
        }

        if ($scope->isJumuiya()) {
            return $query->whereHas('familia', function (Builder $builder) use ($scope) {
                $builder->where('jumuiya_id', (int) $scope->jumuiyaId);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}