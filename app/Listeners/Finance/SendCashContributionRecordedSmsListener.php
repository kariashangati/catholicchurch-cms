<?php

namespace App\Listeners\Finance;

use App\Services\Communication\Automation\FinanceCommunicationAutomationService;

class SendCashContributionRecordedSmsListener
{
    public function __construct(protected FinanceCommunicationAutomationService $service)
    {
    }

    public function handle(object $event): void
    {
        $this->service->handleEvent('cash_contribution.recorded', $event->cashContribution ?? $event->record ?? null);
    }
}
