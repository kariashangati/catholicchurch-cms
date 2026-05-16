<?php

namespace App\Listeners\Communication;

use App\Events\Communication\MemberUpdatedForCommunication;
use App\Services\Communication\GeneralAutomationExecutionService;

class SendMemberUpdatedAutomation
{
    public function __construct(protected GeneralAutomationExecutionService $service)
    {
    }

    public function handle(MemberUpdatedForCommunication $event): void
    {
        $eventKey = array_key_exists('phone', $event->changes) || array_key_exists('phone_number', $event->changes)
            ? 'member.phone_changed'
            : 'member.updated';

        $this->service->handle($eventKey, [
            'member' => $event->member,
            'changes' => $event->changes,
        ]);
    }
}
