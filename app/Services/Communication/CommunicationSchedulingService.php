<?php

namespace App\Services\Communication;

use App\Jobs\Communication\LaunchScheduledCampaignJob;
use App\Models\CommunicationCampaign;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CommunicationSchedulingService
{
    public function getScheduledCampaigns(int $limit = 50): Collection
    {
        return CommunicationCampaign::query()
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    public function scheduleCampaign(
        CommunicationCampaign $campaign,
        string $scheduledAt,
        ?string $timezone,
        ?string $notes,
        ?int $userId
    ): CommunicationCampaign {
        $tz = $timezone ?: config('app.timezone', 'UTC');

        $at = Carbon::parse($scheduledAt, $tz)->utc();

        $campaign->fill([
            'status' => 'scheduled',
            'scheduled_at' => $at,
            'schedule_timezone' => $tz,
            'schedule_notes' => $notes,
            'scheduled_by' => $userId,
            'cancelled_at' => null,
        ]);

        $campaign->save();

        return $campaign->refresh();
    }

    public function cancelScheduledCampaign(CommunicationCampaign $campaign): CommunicationCampaign
    {
        $campaign->fill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        return $campaign->refresh();
    }

    public function dueScheduledCampaigns(int $limit = 20): Collection
    {
        return CommunicationCampaign::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    public function dispatchDueCampaigns(int $limit = 20): int
    {
        $count = 0;

        $this->dueScheduledCampaigns($limit)->each(function (CommunicationCampaign $campaign) use (&$count) {
            DB::transaction(function () use ($campaign, &$count) {
                $fresh = CommunicationCampaign::query()
                    ->lockForUpdate()
                    ->find($campaign->id);

                if (! $fresh || $fresh->status !== 'scheduled') {
                    return;
                }

                $fresh->update([
                    'status' => 'processing',
                    'launched_at' => now(),
                ]);

                LaunchScheduledCampaignJob::dispatch($fresh->id);
                $count++;
            });
        });

        return $count;
    }
}
