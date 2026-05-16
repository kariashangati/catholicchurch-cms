<?php

namespace App\Console\Commands;

use App\Services\Communication\CommunicationSchedulingService;
use Illuminate\Console\Command;

class RunScheduledCommunicationCampaigns extends Command
{
    protected $signature = 'communication:run-scheduled-campaigns {--limit=20 : Maximum campaigns to launch per run}';

    protected $description = 'Find due scheduled communication campaigns and dispatch them to the queue';

    public function handle(CommunicationSchedulingService $service): int
    {
        $limit = (int) $this->option('limit');

        $count = $service->dispatchDueCampaigns($limit);

        $this->info("Launched {$count} scheduled communication campaign(s).");

        return self::SUCCESS;
    }
}
