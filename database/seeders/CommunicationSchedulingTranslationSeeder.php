<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunicationSchedulingTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['en', 'communication.schedules', 'Schedules'],
            ['sw', 'communication.schedules', 'Ratiba'],

            ['en', 'communication.scheduling_center', 'Scheduling Center'],
            ['sw', 'communication.scheduling_center', 'Kituo cha Ratiba'],

            ['en', 'communication.scheduled_campaigns', 'Scheduled Campaigns'],
            ['sw', 'communication.scheduled_campaigns', 'Kampeni Zilizoratibiwa'],

            ['en', 'communication.manage_send_later_campaigns_from_one_place', 'Manage send-later campaigns from one place.'],
            ['sw', 'communication.manage_send_later_campaigns_from_one_place', 'Simamia kampeni za kutuma baadaye kutoka sehemu moja.'],

            ['en', 'communication.total_scheduled', 'Total Scheduled'],
            ['sw', 'communication.total_scheduled', 'Jumla Zilizoratibiwa'],

            ['en', 'communication.ready_to_launch', 'Ready To Launch'],
            ['sw', 'communication.ready_to_launch', 'Tayari Kuzinduliwa'],

            ['en', 'communication.upcoming', 'Upcoming'],
            ['sw', 'communication.upcoming', 'Zinazokuja'],

            ['en', 'communication.review_reschedule_or_cancel_campaigns', 'Review, reschedule, or cancel campaigns before they are launched.'],
            ['sw', 'communication.review_reschedule_or_cancel_campaigns', 'Kagua, badili ratiba, au ghairi kampeni kabla hazijazinduliwa.'],

            ['en', 'communication.queue_ready', 'Queue Ready'],
            ['sw', 'communication.queue_ready', 'Tayari kwa Queue'],

            ['en', 'communication.title', 'Title'],
            ['sw', 'communication.title', 'Kichwa'],

            ['en', 'communication.type', 'Type'],
            ['sw', 'communication.type', 'Aina'],

            ['en', 'communication.recipients', 'Recipients'],
            ['sw', 'communication.recipients', 'Wapokeaji'],

            ['en', 'communication.scheduled_at', 'Scheduled At'],
            ['sw', 'communication.scheduled_at', 'Imepangwa Saa'],

            ['en', 'communication.status', 'Status'],
            ['sw', 'communication.status', 'Hali'],

            ['en', 'communication.actions', 'Actions'],
            ['sw', 'communication.actions', 'Vitendo'],

            ['en', 'communication.reschedule', 'Reschedule'],
            ['sw', 'communication.reschedule', 'Badili Ratiba'],

            ['en', 'communication.cancel', 'Cancel'],
            ['sw', 'communication.cancel', 'Ghairi'],

            ['en', 'communication.reschedule_campaign', 'Reschedule Campaign'],
            ['sw', 'communication.reschedule_campaign', 'Badili Ratiba ya Kampeni'],

            ['en', 'communication.timezone', 'Timezone'],
            ['sw', 'communication.timezone', 'Saa za Eneo'],

            ['en', 'communication.notes', 'Notes'],
            ['sw', 'communication.notes', 'Maelezo'],

            ['en', 'communication.close', 'Close'],
            ['sw', 'communication.close', 'Funga'],

            ['en', 'communication.save_schedule', 'Save Schedule'],
            ['sw', 'communication.save_schedule', 'Hifadhi Ratiba'],

            ['en', 'communication.no_scheduled_campaigns', 'No scheduled campaigns yet.'],
            ['sw', 'communication.no_scheduled_campaigns', 'Hakuna kampeni zilizoratibiwa bado.'],

            ['en', 'communication.schedule_campaigns_to_see_them_here', 'Schedule campaigns and they will appear here for review.'],
            ['sw', 'communication.schedule_campaigns_to_see_them_here', 'Panga kampeni na zitaonekana hapa kwa ukaguzi.'],

            ['en', 'communication.schedule_success', 'Campaign scheduled successfully.'],
            ['sw', 'communication.schedule_success', 'Kampeni imepangwa kikamilifu.'],

            ['en', 'communication.reschedule_success', 'Campaign rescheduled successfully.'],
            ['sw', 'communication.reschedule_success', 'Ratiba ya kampeni imebadilishwa kikamilifu.'],

            ['en', 'communication.cancel_schedule_success', 'Scheduled campaign cancelled successfully.'],
            ['sw', 'communication.cancel_schedule_success', 'Kampeni iliyopangwa imeghairiwa kikamilifu.'],

            ['en', 'communication.only_scheduled_campaign_can_be_rescheduled', 'Only a scheduled campaign can be rescheduled.'],
            ['sw', 'communication.only_scheduled_campaign_can_be_rescheduled', 'Ni kampeni iliyopangwa tu inaweza kubadilishwa ratiba.'],

            ['en', 'communication.only_scheduled_campaign_can_be_cancelled', 'Only a scheduled campaign can be cancelled.'],
            ['sw', 'communication.only_scheduled_campaign_can_be_cancelled', 'Ni kampeni iliyopangwa tu inaweza kughairiwa.'],

            ['en', 'communication.campaign_status_is_not_schedulable', 'This campaign status cannot be scheduled.'],
            ['sw', 'communication.campaign_status_is_not_schedulable', 'Hali hii ya kampeni haiwezi kupangiwa ratiba.'],
        ];

        foreach ($rows as [$locale, $key, $value]) {
            DB::table('translations')->updateOrInsert(
                ['locale' => $locale, 'translation_key' => $key],
                ['translation_value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
