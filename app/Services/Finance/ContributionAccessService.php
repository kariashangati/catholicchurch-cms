<?php

namespace App\Services\Finance;

use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Member;
use App\Services\Access\UserScopeResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;

class ContributionAccessService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver
    ) {
    }

    public function applyMemberScope(Builder $query, User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $this->applyKandaScope($query, (int) $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $this->applyJumuiyaScope($query, (int) $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function ensureCanAccessMember(User $user, int $memberId): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        if ($scope->isGlobal()) {
            return;
        }

        $member = Member::query()
            ->with('familia.jumuiya')
            ->findOrFail($memberId);

        if ($scope->isKanda() && (int) $member->familia?->jumuiya?->kanda_id === (int) $scope->kandaId) {
            return;
        }

        if ($scope->isJumuiya() && (int) $member->familia?->jumuiya_id === (int) $scope->jumuiyaId) {
            return;
        }

        throw new AuthorizationException('You are not allowed to access this member contribution record.');
    }

    public function ensureCanAccessFamilia(User $user, int $familiaId): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        if ($scope->isGlobal()) {
            return;
        }

        $familia = Familia::query()
            ->with('jumuiya')
            ->findOrFail($familiaId);

        if ($scope->isKanda() && (int) $familia->jumuiya?->kanda_id === (int) $scope->kandaId) {
            return;
        }

        if ($scope->isJumuiya() && (int) $familia->jumuiya_id === (int) $scope->jumuiyaId) {
            return;
        }

        throw new AuthorizationException('You are not allowed to access this familia contribution scope.');
    }

    public function ensureCanAccessJumuiya(User $user, int $jumuiyaId): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        if ($scope->isGlobal()) {
            return;
        }

        $jumuiya = Jumuiya::query()->findOrFail($jumuiyaId);

        if ($scope->isKanda() && (int) $jumuiya->kanda_id === (int) $scope->kandaId) {
            return;
        }

        if ($scope->isJumuiya() && (int) $jumuiya->id === (int) $scope->jumuiyaId) {
            return;
        }

        throw new AuthorizationException('You are not allowed to access this jumuiya contribution scope.');
    }

    protected function applyKandaScope(Builder $query, int $kandaId): Builder
    {
        $table = $query->getModel()->getTable();

        if ($this->hasColumn($query, 'kanda_id')) {
            return $query->where("{$table}.kanda_id", $kandaId);
        }

        if ($this->hasColumn($query, 'jumuiya_id')) {
            return $query->whereHas('jumuiya', function (Builder $builder) use ($kandaId) {
                $builder->where('kanda_id', $kandaId);
            });
        }

        if ($this->hasColumn($query, 'familia_id')) {
            return $query->whereHas('familia.jumuiya', function (Builder $builder) use ($kandaId) {
                $builder->where('kanda_id', $kandaId);
            });
        }

        if ($this->hasColumn($query, 'member_id')) {
            return $query->whereHas('member.familia.jumuiya', function (Builder $builder) use ($kandaId) {
                $builder->where('kanda_id', $kandaId);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    protected function applyJumuiyaScope(Builder $query, int $jumuiyaId): Builder
    {
        $table = $query->getModel()->getTable();

        if ($this->hasColumn($query, 'jumuiya_id')) {
            return $query->where("{$table}.jumuiya_id", $jumuiyaId);
        }

        if ($this->hasColumn($query, 'familia_id')) {
            return $query->whereHas('familia', function (Builder $builder) use ($jumuiyaId) {
                $builder->where('jumuiya_id', $jumuiyaId);
            });
        }

        if ($this->hasColumn($query, 'member_id')) {
            return $query->whereHas('member.familia', function (Builder $builder) use ($jumuiyaId) {
                $builder->where('jumuiya_id', $jumuiyaId);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    protected function hasColumn(Builder $query, string $column): bool
    {
        $table = $query->getModel()->getTable();

        try {
            return \Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}