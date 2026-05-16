<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunicationAutomationTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['sw', 'communication.automations.title', 'Kanuni za Ujumbe Otomatiki'],
            ['en', 'communication.automations.title', 'Communication Automations'],
            ['sw', 'communication.automations.subtitle', 'Dhibiti lini mfumo utume SMS na ujumbe gani utumike.'],
            ['en', 'communication.automations.subtitle', 'Control when the system should send SMS and which message should be used.'],
            ['sw', 'communication.automations.create', 'Ongeza Kanuni'],
            ['en', 'communication.automations.create', 'Create Automation'],
            ['sw', 'communication.automations.created_successfully', 'Kanuni imeongezwa kikamilifu.'],
            ['en', 'communication.automations.created_successfully', 'Automation created successfully.'],
            ['sw', 'communication.automations.updated_successfully', 'Kanuni imeboreshwa kikamilifu.'],
            ['en', 'communication.automations.updated_successfully', 'Automation updated successfully.'],
            ['sw', 'communication.automations.deleted_successfully', 'Kanuni imefutwa kikamilifu.'],
            ['en', 'communication.automations.deleted_successfully', 'Automation deleted successfully.'],
            ['sw', 'communication.automations.toggled_successfully', 'Hali ya kanuni imebadilishwa.'],
            ['en', 'communication.automations.toggled_successfully', 'Automation status updated.'],
            ['sw', 'communication.automations.events.tithe_recorded', 'Zaka imerekodiwa'],
            ['en', 'communication.automations.events.tithe_recorded', 'Tithe recorded'],
            ['sw', 'communication.automations.events.offering_recorded', 'Sadaka imerekodiwa'],
            ['en', 'communication.automations.events.offering_recorded', 'Offering recorded'],
            ['sw', 'communication.automations.events.cash_contribution_recorded', 'Mchango wa taslimu umerekodiwa'],
            ['en', 'communication.automations.events.cash_contribution_recorded', 'Cash contribution recorded'],
            ['sw', 'communication.automations.events.bank_contribution_approved', 'Mchango wa benki umeidhinishwa'],
            ['en', 'communication.automations.events.bank_contribution_approved', 'Bank contribution approved'],
            ['sw', 'communication.automations.events.member_created', 'Muumini ameongezwa'],
            ['en', 'communication.automations.events.member_created', 'Member created'],
            ['sw', 'communication.automations.events.member_updated', 'Taarifa za muumini zimebadilishwa'],
            ['en', 'communication.automations.events.member_updated', 'Member updated'],
            ['sw', 'communication.automations.events.leadership_assigned', 'Kiongozi amepewa nafasi'],
            ['en', 'communication.automations.events.leadership_assigned', 'Leadership assigned'],
            ['sw', 'communication.automations.events.apostolic_group_joined', 'Muumini amejiunga na kikundi'],
            ['en', 'communication.automations.events.apostolic_group_joined', 'Member joined apostolic group'],
            ['sw', 'communication.automations.trigger_modes.immediate', 'Tuma mara moja'],
            ['en', 'communication.automations.trigger_modes.immediate', 'Send immediately'],
            ['sw', 'communication.automations.trigger_modes.scheduled', 'Tuma kwa kucheleweshwa'],
            ['en', 'communication.automations.trigger_modes.scheduled', 'Send with delay'],
            ['sw', 'communication.automations.trigger_modes.manual_review', 'Subiri uhakiki wa mkono'],
            ['en', 'communication.automations.trigger_modes.manual_review', 'Wait for manual review'],
            ['sw', 'communication.automations.schema.requires_approval', 'Lazima iwe imeidhinishwa'],
            ['en', 'communication.automations.schema.requires_approval', 'Requires approval'],
            ['sw', 'communication.automations.schema.minimum_amount', 'Kiasi cha chini'],
            ['en', 'communication.automations.schema.minimum_amount', 'Minimum amount'],
            ['sw', 'communication.automations.schema.only_active_members', 'Waalikwe waumini hai tu'],
            ['en', 'communication.automations.schema.only_active_members', 'Only active members'],
            ['sw', 'communication.automations.schema.fields', 'Sehemu zinazofuatiliwa'],
            ['en', 'communication.automations.schema.fields', 'Tracked fields'],
            ['sw', 'communication.automations.schema.only_when_phone_changes', 'Tuma simu ikibadilika tu'],
            ['en', 'communication.automations.schema.only_when_phone_changes', 'Only when phone changes'],
            ['sw', 'communication.audience.member', 'Muumini'],
            ['en', 'communication.audience.member', 'Member'],
            ['sw', 'communication.audience.familia', 'Familia'],
            ['en', 'communication.audience.familia', 'Familia'],
            ['sw', 'communication.audience.jumuiya', 'Jumuiya'],
            ['en', 'communication.audience.jumuiya', 'Jumuiya'],
            ['sw', 'communication.audience.kanda', 'Kanda'],
            ['en', 'communication.audience.kanda', 'Kanda'],
            ['sw', 'communication.audience.apostolic_group', 'Kikundi cha kitume'],
            ['en', 'communication.audience.apostolic_group', 'Apostolic group'],
            ['sw', 'communication.audience.leadership', 'Uongozi'],
            ['en', 'communication.audience.leadership', 'Leadership'],
            ['sw', 'communication.audience.custom', 'Maalum'],
            ['en', 'communication.audience.custom', 'Custom'],
        ];

        foreach ($rows as [$locale, $key, $value]) {
            DB::table('translations')->updateOrInsert(
                ['locale' => $locale, 'translation_key' => $key],
                ['translation_value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
