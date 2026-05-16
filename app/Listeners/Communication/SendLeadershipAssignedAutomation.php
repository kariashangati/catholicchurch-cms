<?php

namespace App\Listeners\Communication;

use App\Events\Communication\LeadershipAssignedForCommunication;
use App\Services\Communication\GeneralAutomationExecutionService;

class SendLeadershipAssignedAutomation
{
    public function __construct(protected GeneralAutomationExecutionService $service)
    {
    }

    public function handle(LeadershipAssignedForCommunication $event): void
    {
        $this->service->handle('leadership.assigned', [
            'assignment' => $event->assignment,
        ]);
    }
}
