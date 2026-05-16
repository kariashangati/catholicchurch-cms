<?php

namespace App\Services\Receipts;

use App\Support\Receipts\ReceiptStatuses;

class ReceiptStatusService
{
    public function dashboardStatusCards(): array
    {
        return [
            ['value' => ReceiptStatuses::PENDING_ISSUE, 'label' => db_trans('receipts.status.pending_issue')],
            ['value' => ReceiptStatuses::ISSUED, 'label' => db_trans('receipts.status.issued')],
            ['value' => ReceiptStatuses::PRINTED, 'label' => db_trans('receipts.status.printed')],
            ['value' => ReceiptStatuses::SMS_SENT, 'label' => db_trans('receipts.status.sms_sent')],
            ['value' => ReceiptStatuses::DOWNLOADED, 'label' => db_trans('receipts.status.downloaded')],
            ['value' => ReceiptStatuses::EXCEPTION, 'label' => db_trans('receipts.status.exception')],
        ];
    }

    public function historyStatusOptions(): array
    {
        return collect(ReceiptStatuses::options())
            ->map(fn (string $value) => [
                'value' => $value,
                'label' => db_trans('receipts.status.' . $value),
            ])
            ->values()
            ->all();
    }
}
