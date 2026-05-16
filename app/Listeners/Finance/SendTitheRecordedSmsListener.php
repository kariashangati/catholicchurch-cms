<?php

namespace App\Listeners\Finance;

use App\Services\Communication\Automation\FinanceCommunicationAutomationService;

class SendTitheRecordedSmsListener
{
    public function __construct(protected FinanceCommunicationAutomationService $service)
    {
    }

    public function handle(object $event): void
    {
        $this->service->handleEvent('tithe.recorded', $event->tithe ?? $event->record ?? null);
    }
}
