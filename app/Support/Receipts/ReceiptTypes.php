<?php

namespace App\Support\Receipts;

class ReceiptTypes
{
    public const TITHE = 'zaka';
    public const CONTRIBUTION = 'mchango';
    public const CASH_CONTRIBUTION = 'mchango_taslimu';
    public const BANK_CONTRIBUTION = 'mchango_benki';

    /** Legacy aliases used by the older receipt subsystem. */
    public const LEGACY_TITHE = 'tithe';
    public const LEGACY_CASH_CONTRIBUTION = 'cash_contribution';
    public const LEGACY_BANK_CONTRIBUTION = 'bank_contribution';
    public const LEGACY_OFFERING = 'offering';
    public const LEGACY_PROJECT_CONTRIBUTION = 'project_contribution';
    public const LEGACY_GROUP_SUMMARY = 'group_summary';

    public static function values(): array
    {
        return [
            self::TITHE,
            self::CONTRIBUTION,
            self::CASH_CONTRIBUTION,
            self::BANK_CONTRIBUTION,
            self::LEGACY_TITHE,
            self::LEGACY_CASH_CONTRIBUTION,
            self::LEGACY_BANK_CONTRIBUTION,
            self::LEGACY_OFFERING,
            self::LEGACY_PROJECT_CONTRIBUTION,
            self::LEGACY_GROUP_SUMMARY,
        ];
    }

    public static function labels(): array
    {
        return [
            self::TITHE => db_trans('tithes'),
            self::CONTRIBUTION => db_trans('contributions'),
            self::CASH_CONTRIBUTION => db_trans('cash_contributions'),
            self::BANK_CONTRIBUTION => db_trans('bank_contributions'),
            self::LEGACY_TITHE => db_trans('receipts.types.tithe'),
            self::LEGACY_CASH_CONTRIBUTION => db_trans('receipts.types.cash_contribution'),
            self::LEGACY_BANK_CONTRIBUTION => db_trans('receipts.types.bank_contribution'),
            self::LEGACY_OFFERING => db_trans('receipts.types.offering'),
            self::LEGACY_PROJECT_CONTRIBUTION => db_trans('receipts.types.project_contribution'),
            self::LEGACY_GROUP_SUMMARY => db_trans('receipts.types.group_summary'),
        ];
    }

    /**
     * Used by ReceiptService::supportedReceiptTypes().
     */
    public static function options(): array
    {
        return self::labels();
    }

    public static function normalize(?string $type): string
    {
        return match ($type) {
            self::LEGACY_TITHE, self::TITHE => self::TITHE,
            'contribution', self::LEGACY_CASH_CONTRIBUTION, self::LEGACY_BANK_CONTRIBUTION,
            self::LEGACY_PROJECT_CONTRIBUTION, self::CONTRIBUTION, self::CASH_CONTRIBUTION, self::BANK_CONTRIBUTION => self::CONTRIBUTION,
            default => self::TITHE,
        };
    }
}
