<?php

namespace App\Listeners\Finance;

use App\Services\Communication\Automation\FinanceCommunicationAutomationService;

class SendBankContributionApprovedSmsListener
{
    public function __construct(protected FinanceCommunicationAutomationService $service)
    {
    }

    public function handle(object $event): void
    {
        $this->service->handleEvent('bank_contribution.approved', $event->bankContribution ?? $event->record ?? null);
    }
}
