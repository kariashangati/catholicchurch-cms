<?php

namespace App\Listeners\Communication;

use App\Events\Communication\AnnouncementPublishedForCommunication;
use App\Services\Communication\GeneralAutomationExecutionService;

class SendAnnouncementPublishedAutomation
{
    public function __construct(protected GeneralAutomationExecutionService $service)
    {
    }

    public function handle(AnnouncementPublishedForCommunication $event): void
    {
        $this->service->handle('announcement.published', [
            'announcement' => $event->announcement,
        ]);
    }
}
