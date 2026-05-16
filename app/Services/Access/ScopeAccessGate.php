<?php

namespace App\Services\Access;

use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ScopeAccessGate
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
    ) {
    }

    public function canAccessKanda(User $user, Kanda $kanda): bool
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return false;
        }

        if ($scope->isGlobal()) {
            return true;
        }

        if ($scope->isKanda()) {
            return (int) $kanda->id === (int) $scope->kandaId;
        }

        if ($scope->isJumuiya()) {
            return (int) $kanda->id === (int) $scope->kandaId;
        }

        return false;
    }

    public function canAccessJumuiya(User $user, Jumuiya $jumuiya): bool
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return false;
        }

        if ($scope->isGlobal()) {
            return true;
        }

        if ($scope->isKanda()) {
            return (int) $jumuiya->kanda_id === (int) $scope->kandaId;
        }

        if ($scope->isJumuiya()) {
            return (int) $jumuiya->id === (int) $scope->jumuiyaId;
        }

        return false;
    }

    public function canAccessFamilia(User $user, Familia $familia): bool
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return false;
        }

        if ($scope->isGlobal()) {
            return true;
        }

        $familia->loadMissing('jumuiya');

        if ($scope->isKanda()) {
            return (int) $familia->jumuiya?->kanda_id === (int) $scope->kandaId;
        }

        if ($scope->isJumuiya()) {
            return (int) $familia->jumuiya_id === (int) $scope->jumuiyaId;
        }

        return false;
    }

    public function canAccessMember(User $user, Member $member): bool
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return false;
        }

        if ($scope->isGlobal()) {
            return true;
        }

        $member->loadMissing('familia.jumuiya');

        if ($scope->isKanda()) {
            return (int) $member->familia?->jumuiya?->kanda_id === (int) $scope->kandaId;
        }

        if ($scope->isJumuiya()) {
            return (int) $member->familia?->jumuiya_id === (int) $scope->jumuiyaId;
        }

        return false;
    }

    public function authorizeKanda(User $user, Kanda $kanda, ?string $message = null): void
    {
        if (! $this->canAccessKanda($user, $kanda)) {
            throw new AuthorizationException($message ?: 'You are not allowed to access this kanda.');
        }
    }

    public function authorizeJumuiya(User $user, Jumuiya $jumuiya, ?string $message = null): void
    {
        if (! $this->canAccessJumuiya($user, $jumuiya)) {
            throw new AuthorizationException($message ?: 'You are not allowed to access this jumuiya.');
        }
    }

    public function authorizeFamilia(User $user, Familia $familia, ?string $message = null): void
    {
        if (! $this->canAccessFamilia($user, $familia)) {
            throw new AuthorizationException($message ?: 'You are not allowed to access this familia.');
        }
    }

    public function authorizeMember(User $user, Member $member, ?string $message = null): void
    {
        if (! $this->canAccessMember($user, $member)) {
            throw new AuthorizationException($message ?: 'You are not allowed to access this member.');
        }
    }
}