<?php

namespace App\Services\Finance;

use App\Models\Jumuiya;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;

class TitheAccessService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver
    ) {
    }

    public function scopeForUser(User $user): object
    {
        return $this->scopeResolver->resolve($user);
    }

    public function applyScope(Builder $query, object $scope): Builder
    {
        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereHas('jumuiya', function (Builder $builder) use ($scope) {
                $builder->where('kanda_id', (int) $scope->kandaId);
            });
        }

        if ($scope->isJumuiya()) {
            return $query->where('jumuiya_id', (int) $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function ensureCanAccessJumuiya(User $user, int $jumuiyaId): void
    {
        $scope = $this->scopeForUser($user);

        if ($scope->isInvalid()) {
            abort(403, $scope->reason ?? 'Unauthorized tithe access.');
        }

        if ($scope->isGlobal()) {
            return;
        }

        if ($scope->isJumuiya() && (int) $scope->jumuiyaId === $jumuiyaId) {
            return;
        }

        if ($scope->isKanda()) {
            $exists = Jumuiya::query()
                ->where('id', $jumuiyaId)
                ->where('kanda_id', (int) $scope->kandaId)
                ->exists();

            if ($exists) {
                return;
            }
        }

        abort(403, 'Unauthorized tithe access.');
    }
}