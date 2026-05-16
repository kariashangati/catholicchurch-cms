<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CommunicationCenterPhaseOneSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CommunicationCenterPermissionSeeder::class,
            CommunicationCenterTranslationSeeder::class,
        ]);
    }
}
