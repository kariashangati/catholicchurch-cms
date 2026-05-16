<?php

namespace App\Listeners\Finance;

use App\Services\Communication\Automation\FinanceCommunicationAutomationService;

class SendOfferingRecordedSmsListener
{
    public function __construct(protected FinanceCommunicationAutomationService $service)
    {
    }

    public function handle(object $event): void
    {
        $this->service->handleEvent('offering.recorded', $event->offering ?? $event->record ?? null);
    }
}
