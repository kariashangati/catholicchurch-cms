<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CommunicationCenterPhaseTwelveSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'communication.approve_campaigns',
            'communication.cancel_campaigns',
            'communication.retry_failed',
            'communication.manage_controls',
        ] as $permission) {
            Permission::findOrCreate($permission, config('auth.defaults.guard', 'web'));
        }

        $translationModel = app(config('communication_center.translation_model', \App\Models\Translation::class));

        $translations = [
            'communication.common.kicker' => ['en' => 'Communication Center', 'sw' => 'Kituo cha Mawasiliano'],
            'communication.common.title' => ['en' => 'Title', 'sw' => 'Kichwa'],
            'communication.common.type' => ['en' => 'Type', 'sw' => 'Aina'],
            'communication.common.recipients' => ['en' => 'Recipients', 'sw' => 'Wapokeaji'],
            'communication.common.status' => ['en' => 'Status', 'sw' => 'Hali'],
            'communication.common.scheduled_at' => ['en' => 'Scheduled At', 'sw' => 'Imepangwa'],
            'communication.common.actions' => ['en' => 'Actions', 'sw' => 'Vitendo'],
            'communication.common.approve' => ['en' => 'Approve', 'sw' => 'Idhinisha'],
            'communication.common.reject' => ['en' => 'Reject', 'sw' => 'Kataa'],
            'communication.common.confirm_reject' => ['en' => 'Confirm Reject', 'sw' => 'Thibitisha Kukataa'],
            'communication.common.yes' => ['en' => 'Yes', 'sw' => 'Ndiyo'],
            'communication.common.no' => ['en' => 'No', 'sw' => 'Hapana'],
            'communication.common.save_changes' => ['en' => 'Save Changes', 'sw' => 'Hifadhi Mabadiliko'],

            'communication.approvals.kicker' => ['en' => 'Review & Control', 'sw' => 'Mapitio na Udhibiti'],
            'communication.approvals.title' => ['en' => 'Campaign Approvals', 'sw' => 'Idhini za Kampeni'],
            'communication.approvals.subtitle' => ['en' => 'Review sensitive, high-volume, or scheduled campaigns before they are launched.', 'sw' => 'Kagua kampeni nyeti, zenye wapokeaji wengi, au zilizopangwa kabla hazijazinduliwa.'],
            'communication.approvals.pending_count' => ['en' => 'Pending', 'sw' => 'Zinazosubiri'],
            'communication.approvals.empty' => ['en' => 'No campaigns are waiting for approval.', 'sw' => 'Hakuna kampeni zinazongoja idhini.'],
            'communication.approvals.reject_reason' => ['en' => 'Reason for rejection', 'sw' => 'Sababu ya kukataa'],
            'communication.approvals.approved_success' => ['en' => 'Campaign approved successfully.', 'sw' => 'Kampeni imeidhinishwa kikamilifu.'],
            'communication.approvals.rejected_success' => ['en' => 'Campaign rejected successfully.', 'sw' => 'Kampeni imekataliwa kikamilifu.'],
            'communication.approvals.cancelled_success' => ['en' => 'Campaign cancelled successfully.', 'sw' => 'Kampeni imeghairiwa kikamilifu.'],
            'communication.approvals.retry_requested_success' => ['en' => 'Retry for failed messages has been queued.', 'sw' => 'Jaribio la kutuma tena meseji zilizoshindikana limepangwa kwenye foleni.'],

            'communication.controls.kicker' => ['en' => 'Premium Controls', 'sw' => 'Vidhibiti vya Premium'],
            'communication.controls.title' => ['en' => 'Communication Controls', 'sw' => 'Vidhibiti vya Mawasiliano'],
            'communication.controls.subtitle' => ['en' => 'Configure quiet hours, approval thresholds, duplicate protection, and retry behavior.', 'sw' => 'Sanidi saa za utulivu, viwango vya idhini, ulinzi wa meseji rudufu, na tabia ya kutuma tena.'],
            'communication.controls.quiet_hours_title' => ['en' => 'Quiet Hours', 'sw' => 'Saa za Utulivu'],
            'communication.controls.quiet_hours_enabled' => ['en' => 'Enable quiet hours protection', 'sw' => 'Washa ulinzi wa saa za utulivu'],
            'communication.controls.quiet_hours_start' => ['en' => 'Quiet hours start', 'sw' => 'Mwanzo wa saa za utulivu'],
            'communication.controls.quiet_hours_end' => ['en' => 'Quiet hours end', 'sw' => 'Mwisho wa saa za utulivu'],
            'communication.controls.approvals_title' => ['en' => 'Approval Rules', 'sw' => 'Sheria za Idhini'],
            'communication.controls.default_requires_approval' => ['en' => 'Require approval by default', 'sw' => 'Hitaji idhini kwa chaguomsingi'],
            'communication.controls.approval_threshold_recipients' => ['en' => 'Approval threshold by recipients', 'sw' => 'Kizingiti cha idhini kwa wapokeaji'],
            'communication.controls.approval_threshold_segments' => ['en' => 'Approval threshold by segments', 'sw' => 'Kizingiti cha idhini kwa segmenti'],
            'communication.controls.delivery_rules_title' => ['en' => 'Delivery Rules', 'sw' => 'Sheria za Utumaji'],
            'communication.controls.default_duplicate_window_hours' => ['en' => 'Duplicate protection window (hours)', 'sw' => 'Dirisha la ulinzi wa meseji rudufu (saa)'],
            'communication.controls.allow_retry_failed' => ['en' => 'Allow retry for failed messages', 'sw' => 'Ruhusu kutuma tena meseji zilizoshindikana'],
            'communication.controls.note' => ['en' => 'These rules help prevent accidental bulk sends, late-night disruptions, and repeated duplicate messages.', 'sw' => 'Sheria hizi husaidia kuzuia utumaji wa kundi kwa bahati mbaya, usumbufu wa usiku, na meseji rudufu zinazojirudia.'],
            'communication.controls.updated_success' => ['en' => 'Communication controls updated successfully.', 'sw' => 'Vidhibiti vya mawasiliano vimeboreshwa kikamilifu.'],
        ];

        foreach ($translations as $key => $locales) {
            foreach ($locales as $locale => $value) {
                $translationModel::updateOrCreate(
                    ['locale' => $locale, 'translation_key' => $key],
                    ['translation_value' => $value]
                );
            }
        }
    }
}
