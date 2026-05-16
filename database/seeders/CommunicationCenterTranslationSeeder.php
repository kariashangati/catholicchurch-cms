<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CommunicationCenterTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'communication.title' => ['en' => 'Communication Center', 'sw' => 'Kituo cha Mawasiliano'],
            'communication.menu' => ['en' => 'Communication Center', 'sw' => 'Kituo cha Mawasiliano'],
            'communication.dashboard' => ['en' => 'Dashboard', 'sw' => 'Dashibodi'],
            'communication.templates' => ['en' => 'Templates', 'sw' => 'Templeti'],
            'communication.automations' => ['en' => 'Automations', 'sw' => 'Otomesheni'],
            'communication.campaigns' => ['en' => 'Campaigns', 'sw' => 'Kampeni'],
            'communication.messages' => ['en' => 'Messages', 'sw' => 'Ujumbe'],
            'communication.preferences' => ['en' => 'Preferences', 'sw' => 'Mapendeleo'],
            'communication.balance' => ['en' => 'SMS Balance', 'sw' => 'Salio la SMS'],
            'communication.logs' => ['en' => 'Logs', 'sw' => 'Kumbukumbu'],
            'communication.send_now' => ['en' => 'Send Now', 'sw' => 'Tuma Sasa'],
            'communication.schedule_for_later' => ['en' => 'Schedule for Later', 'sw' => 'Panga Kutuma Baadaye'],
            'communication.allow_sms' => ['en' => 'Allow SMS', 'sw' => 'Ruhusu SMS'],
            'communication.opt_out' => ['en' => 'Opt Out', 'sw' => 'Jiondoe'],
            'communication.template_body' => ['en' => 'Template Body', 'sw' => 'Mwili wa Templeti'],
            'communication.audience_type' => ['en' => 'Audience Type', 'sw' => 'Aina ya Wapokeaji'],
            'communication.event_key' => ['en' => 'Event Key', 'sw' => 'Ufunguo wa Tukio'],
            'communication.channel' => ['en' => 'Channel', 'sw' => 'Njia'],
            'communication.status' => ['en' => 'Status', 'sw' => 'Hali'],
            'communication.locale' => ['en' => 'Locale', 'sw' => 'Lugha'],
            'communication.created_by' => ['en' => 'Created By', 'sw' => 'Imeundwa na'],
            'communication.updated_by' => ['en' => 'Updated By', 'sw' => 'Imesasishwa na'],
            'communication.notes' => ['en' => 'Notes', 'sw' => 'Maelezo'],
        ];

        foreach ($translations as $key => $values) {
            foreach ($values as $locale => $value) {
                Translation::updateOrCreate(
                    ['locale' => $locale, 'translation_key' => $key],
                    ['translation_value' => $value]
                );

                Cache::forget("translation_{$locale}_{$key}");
            }
        }
    }
}
