<?php

namespace App\Console\Commands\Communication;

use App\Services\Communication\CommunicationCampaignDispatcherService;
use Illuminate\Console\Command;

class RunScheduledCommunicationCampaignsCommand extends Command
{
    protected $signature = 'communication:run-scheduled';

    protected $description = 'Dispatch scheduled SMS campaigns that are due.';

    public function handle(CommunicationCampaignDispatcherService $dispatcher): int
    {
        $count = $dispatcher->dispatchScheduledDueCampaigns();

        $this->info("Dispatched {$count} scheduled communication campaign(s).");

        return self::SUCCESS;
    }
}
