<?php

namespace App\Listeners\Communication;

use App\Events\Communication\ApostolicGroupJoinedForCommunication;
use App\Services\Communication\GeneralAutomationExecutionService;

class SendApostolicGroupJoinedAutomation
{
    public function __construct(protected GeneralAutomationExecutionService $service)
    {
    }

    public function handle(ApostolicGroupJoinedForCommunication $event): void
    {
        $this->service->handle('apostolic_group.joined', [
            'membership' => $event->membership,
        ]);
    }
}
