<?php

namespace App\Services\Finance;

use App\Models\User;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;

class FinanceAccessService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver
    ) {
    }

    /**
     * Resolve scope using central resolver
     */
    public function scopeForUser(User $user): object
    {
        return $this->scopeResolver->resolve($user);
    }

    /**
     * Apply scope to offerings
     */
    public function applyOfferingScope(Builder $query, object $scope): Builder
    {
        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('offerings.kanda_id', (int) $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->where('offerings.jumuiya_id', (int) $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply scope to tithes
     */
    public function applyTitheScope(Builder $query, object $scope): Builder
    {
        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereHas('jumuiya', function ($q) use ($scope) {
                $q->where('kanda_id', (int) $scope->kandaId);
            });
        }

        if ($scope->isJumuiya()) {
            return $query->where('tithes.jumuiya_id', (int) $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply scope to member-based contributions (cash)
     */
    public function applyCashScope(Builder $query, object $scope): Builder
    {
        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereHas('member.familia.jumuiya', function ($q) use ($scope) {
                $q->where('kanda_id', (int) $scope->kandaId);
            });
        }

        if ($scope->isJumuiya()) {
            return $query->whereHas('member.familia', function ($q) use ($scope) {
                $q->where('jumuiya_id', (int) $scope->jumuiyaId);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply scope to bank contributions
     */
    public function applyBankScope(Builder $query, object $scope): Builder
    {
        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('bank_contributions.kanda_id', (int) $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->where('bank_contributions.jumuiya_id', (int) $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply scope to project transactions
     */
    public function applyProjectScope(Builder $query, object $scope): Builder
    {
        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('project_transactions.kanda_id', (int) $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->where('project_transactions.jumuiya_id', (int) $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * HARD security: ensure entity belongs to scope
     */
    public function ensureCanAccessJumuiya(object $scope, int $jumuiyaId): void
    {
        if ($scope->isGlobal()) {
            return;
        }

        if ($scope->isJumuiya() && (int) $scope->jumuiyaId === $jumuiyaId) {
            return;
        }

        if ($scope->isKanda()) {
            $exists = \App\Models\Jumuiya::query()
                ->where('id', $jumuiyaId)
                ->where('kanda_id', (int) $scope->kandaId)
                ->exists();

            if ($exists) {
                return;
            }
        }

        abort(403, 'Unauthorized finance access.');
    }
}