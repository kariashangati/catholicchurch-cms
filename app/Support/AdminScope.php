<?php

namespace App\Support;

final class AdminScope
{
    public const TYPE_GLOBAL = 'global';
    public const TYPE_KANDA = 'kanda';
    public const TYPE_JUMUIYA = 'jumuiya';
    public const TYPE_INVALID = 'invalid';

    public function __construct(
        public readonly string $type,
        public readonly ?int $kandaId = null,
        public readonly ?int $jumuiyaId = null,
        public readonly ?string $label = null,
        public readonly ?string $reason = null,
    ) {
    }

    public static function global(?string $label = 'Parish Overview'): self
    {
        return new self(
            type: self::TYPE_GLOBAL,
            label: $label,
        );
    }

    public static function kanda(int $kandaId, ?string $label = null): self
    {
        return new self(
            type: self::TYPE_KANDA,
            kandaId: $kandaId,
            label: $label,
        );
    }

    public static function jumuiya(int $jumuiyaId, ?int $kandaId = null, ?string $label = null): self
    {
        return new self(
            type: self::TYPE_JUMUIYA,
            kandaId: $kandaId,
            jumuiyaId: $jumuiyaId,
            label: $label,
        );
    }

    public static function invalid(string $reason, ?string $label = 'Invalid Scope'): self
    {
        return new self(
            type: self::TYPE_INVALID,
            label: $label,
            reason: $reason,
        );
    }

    public function isGlobal(): bool
    {
        return $this->type === self::TYPE_GLOBAL;
    }

    public function isKanda(): bool
    {
        return $this->type === self::TYPE_KANDA;
    }

    public function isJumuiya(): bool
    {
        return $this->type === self::TYPE_JUMUIYA;
    }

    public function isInvalid(): bool
    {
        return $this->type === self::TYPE_INVALID;
    }

    public function isScoped(): bool
    {
        return $this->isKanda() || $this->isJumuiya();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'kanda_id' => $this->kandaId,
            'jumuiya_id' => $this->jumuiyaId,
            'label' => $this->label,
            'reason' => $this->reason,
        ];
    }
}