<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Services\Communication\CommunicationPreferenceService;
use Illuminate\Database\Seeder;

class CommunicationCenterPhaseThreeSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CommunicationPreferenceService::class);

        Member::query()
            ->select('id')
            ->chunk(200, function ($members) use ($service) {
                foreach ($members as $member) {
                    $service->getOrCreateForMember($member);
                }
            });
    }
}
