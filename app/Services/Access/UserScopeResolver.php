<?php

namespace App\Services\Access;

use App\Models\Jumuiya;
use App\Models\User;
use App\Support\AdminScope;

class UserScopeResolver
{
    public function resolve(User $user): AdminScope
    {
        $jumuiyaId = $user->jumuiya_id ? (int) $user->jumuiya_id : null;
        $kandaId = $user->kanda_id ? (int) $user->kanda_id : null;

        if ($jumuiyaId !== null) {
            return $this->resolveJumuiyaScope($user, $jumuiyaId, $kandaId);
        }

        if ($kandaId !== null) {
            return $this->resolveKandaScope($user, $kandaId);
        }

        return AdminScope::global();
    }

    public function isGlobal(User $user): bool
    {
        return $this->resolve($user)->isGlobal();
    }

    public function isKanda(User $user): bool
    {
        return $this->resolve($user)->isKanda();
    }

    public function isJumuiya(User $user): bool
    {
        return $this->resolve($user)->isJumuiya();
    }

    public function isInvalid(User $user): bool
    {
        return $this->resolve($user)->isInvalid();
    }

    protected function resolveKandaScope(User $user, int $kandaId): AdminScope
    {
        $label = $user->relationLoaded('kanda')
            ? $user->kanda?->name
            : $user->kanda()->value('name');

        return AdminScope::kanda(
            kandaId: $kandaId,
            label: $label ? "Kanda: {$label}" : 'Kanda Scope',
        );
    }

    protected function resolveJumuiyaScope(User $user, int $jumuiyaId, ?int $kandaId): AdminScope
    {
        /** @var Jumuiya|null $jumuiya */
        $jumuiya = $user->relationLoaded('jumuiya')
            ? $user->jumuiya
            : Jumuiya::query()->select(['id', 'name', 'kanda_id'])->find($jumuiyaId);

        if (! $jumuiya) {
            return AdminScope::invalid("Assigned jumuiya [{$jumuiyaId}] was not found.");
        }

        if ($kandaId !== null && (int) $jumuiya->kanda_id !== $kandaId) {
            return AdminScope::invalid(
                "Assigned jumuiya [{$jumuiyaId}] does not belong to assigned kanda [{$kandaId}]."
            );
        }

        return AdminScope::jumuiya(
            jumuiyaId: $jumuiyaId,
            kandaId: (int) $jumuiya->kanda_id,
            label: $jumuiya->name ? "Jumuiya: {$jumuiya->name}" : 'Jumuiya Scope',
        );
    }
}