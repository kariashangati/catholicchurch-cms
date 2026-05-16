<?php

namespace App\Support\Receipts;

class ReceiptChannels
{
    public const PRINT = 'chapisho';
    public const SMS_LINK = 'sms_link';
    public const DOWNLOAD = 'pakua';

    public static function values(): array
    {
        return [self::PRINT, self::SMS_LINK, self::DOWNLOAD];
    }
}
