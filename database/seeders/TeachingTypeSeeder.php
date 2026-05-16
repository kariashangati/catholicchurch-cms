<?php

namespace Database\Seeders;

use App\Models\TeachingType;
use Illuminate\Database\Seeder;

class TeachingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Komunio',
                'slug' => 'komunio',
                'description' => 'Mafundisho ya Komunio ya kwanza.',
                'sacrament_key' => TeachingType::SACRAMENT_COMMUNION,
                'eligibility_rule' => TeachingType::ELIGIBILITY_COMMUNION,
                'requires_partner_info' => false,
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Kipaimara',
                'slug' => 'kipaimara',
                'description' => 'Mafundisho ya Kipaimara.',
                'sacrament_key' => TeachingType::SACRAMENT_CONFIRMATION,
                'eligibility_rule' => TeachingType::ELIGIBILITY_CONFIRMATION,
                'requires_partner_info' => false,
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Ndoa',
                'slug' => 'ndoa',
                'description' => 'Mafundisho ya Ndoa.',
                'sacrament_key' => TeachingType::SACRAMENT_MARRIAGE,
                'eligibility_rule' => TeachingType::ELIGIBILITY_MARRIAGE,
                'requires_partner_info' => true,
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 30,
            ],
        ];

        foreach ($types as $type) {
            TeachingType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
