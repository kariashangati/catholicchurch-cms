<?php

namespace App\Listeners\Communication;

use App\Events\Communication\MemberCreatedForCommunication;
use App\Services\Communication\GeneralAutomationExecutionService;

class SendMemberCreatedAutomation
{
    public function __construct(protected GeneralAutomationExecutionService $service)
    {
    }

    public function handle(MemberCreatedForCommunication $event): void
    {
        $this->service->handle('member.created', [
            'member' => $event->member,
        ]);
    }
}
