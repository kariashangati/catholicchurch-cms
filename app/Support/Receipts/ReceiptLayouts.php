<?php

namespace App\Support\Receipts;

class ReceiptLayouts
{
    public const MEMBER = 'mwanajumuiya';
    public const JUMUIYA = 'jumuiya';
    public const KANDA = 'kanda';

    public static function values(): array
    {
        return [self::MEMBER, self::JUMUIYA, self::KANDA];
    }

    public static function options(): array
    {
        return [
            self::MEMBER => db_trans('receipt_layout_member'),
            self::JUMUIYA => db_trans('receipt_layout_jumuiya'),
            self::KANDA => db_trans('receipt_layout_kanda'),
        ];
    }

    public static function normalize(?string $layout): string
    {
        return match ($layout) {
            'individual', 'member', self::MEMBER => self::MEMBER,
            'jumuiya_summary', self::JUMUIYA => self::JUMUIYA,
            'kanda_summary', self::KANDA => self::KANDA,
            default => self::MEMBER,
        };
    }
}
