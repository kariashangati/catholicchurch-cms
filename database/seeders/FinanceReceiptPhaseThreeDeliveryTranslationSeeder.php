<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceReceiptPhaseThreeDeliveryTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'finance.receipts.delivery.title', 'en' => 'Receipt Delivery', 'sw' => 'Usambazaji wa Stakabadhi'],
            ['key' => 'finance.receipts.delivery.subtitle', 'en' => 'Send secure receipt links via SMS.', 'sw' => 'Tuma viungo salama vya stakabadhi kupitia SMS.'],
            ['key' => 'finance.receipts.delivery.back_to_receipt', 'en' => 'Back to Receipt', 'sw' => 'Rudi Kwenye Stakabadhi'],
            ['key' => 'finance.receipts.delivery.view_history', 'en' => 'View Delivery History', 'sw' => 'Angalia Historia ya Usambazaji'],
            ['key' => 'finance.receipts.delivery.sms_delivery', 'en' => 'SMS Delivery', 'sw' => 'Usambazaji wa SMS'],
            ['key' => 'finance.receipts.delivery.sms_delivery_help', 'en' => 'Send a secure receipt link to the member phone number.', 'sw' => 'Tuma kiungo salama cha stakabadhi kwa namba ya simu ya muumini.'],
            ['key' => 'finance.receipts.delivery.recipient_phone', 'en' => 'Recipient Phone', 'sw' => 'Simu ya Mpokeaji'],
            ['key' => 'finance.receipts.delivery.template', 'en' => 'Template', 'sw' => 'Kiolezo'],
            ['key' => 'finance.receipts.delivery.custom_message', 'en' => 'Custom Message', 'sw' => 'Ujumbe Maalum'],
            ['key' => 'finance.receipts.delivery.custom_message_placeholder', 'en' => 'Optional: edit the outgoing SMS content.', 'sw' => 'Hiari: hariri maudhui ya SMS inayotoka.'],
            ['key' => 'finance.receipts.delivery.custom_message_help', 'en' => 'Leave blank to use the active SMS template.', 'sw' => 'Acha wazi kutumia kiolezo cha SMS kilicho hai.'],
            ['key' => 'finance.receipts.delivery.rotate_link', 'en' => 'Generate a fresh secure link', 'sw' => 'Tengeneza kiungo kipya salama'],
            ['key' => 'finance.receipts.delivery.send_sms', 'en' => 'Send SMS', 'sw' => 'Tuma SMS'],
            ['key' => 'finance.receipts.delivery.preview_title', 'en' => 'SMS Preview', 'sw' => 'Hakiki ya SMS'],
            ['key' => 'finance.receipts.delivery.preview_help', 'en' => 'Review the SMS before sending.', 'sw' => 'Pitia SMS kabla ya kutuma.'],
            ['key' => 'finance.receipts.delivery.preview_fallback', 'en' => 'Preview will appear here once a message is prepared.', 'sw' => 'Hakiki itaonekana hapa baada ya ujumbe kuandaliwa.'],
            ['key' => 'finance.receipts.delivery.secure_link', 'en' => 'Secure Link', 'sw' => 'Kiungo Salama'],
            ['key' => 'finance.receipts.delivery.link_title', 'en' => 'Receipt Link', 'sw' => 'Kiungo cha Stakabadhi'],
            ['key' => 'finance.receipts.delivery.link_help', 'en' => 'Use this tokenized link for controlled access.', 'sw' => 'Tumia kiungo hiki chenye tokeni kwa ufikivu unaodhibitiwa.'],
            ['key' => 'finance.receipts.delivery.expires_at', 'en' => 'Expires At', 'sw' => 'Kinaisha'],
            ['key' => 'finance.receipts.delivery.no_expiry', 'en' => 'No expiry', 'sw' => 'Hakina mwisho'],
            ['key' => 'finance.receipts.delivery.copy_link', 'en' => 'Copy Link', 'sw' => 'Nakili Kiungo'],
            ['key' => 'finance.receipts.delivery.resend_sms', 'en' => 'Resend SMS', 'sw' => 'Tuma Tena SMS'],
            ['key' => 'finance.receipts.delivery.resend_title', 'en' => 'Resend Receipt SMS', 'sw' => 'Tuma Tena SMS ya Stakabadhi'],
            ['key' => 'finance.receipts.delivery.resend_reason', 'en' => 'Resend Reason', 'sw' => 'Sababu ya Kutuma Tena'],
            ['key' => 'finance.receipts.delivery.history_title', 'en' => 'Delivery History', 'sw' => 'Historia ya Usambazaji'],
            ['key' => 'finance.receipts.delivery.history_subtitle', 'en' => 'Track every receipt SMS delivery event.', 'sw' => 'Fuatilia kila tukio la usambazaji wa SMS ya stakabadhi.'],
            ['key' => 'finance.receipts.delivery.back_to_delivery', 'en' => 'Back to Delivery', 'sw' => 'Rudi Kwenye Usambazaji'],
            ['key' => 'finance.receipts.delivery.log_title', 'en' => 'Delivery Log', 'sw' => 'Kumbukumbu ya Usambazaji'],
            ['key' => 'finance.receipts.delivery.log_help', 'en' => 'Each SMS attempt is recorded below.', 'sw' => 'Kila jaribio la SMS limerekodiwa hapa chini.'],
            ['key' => 'finance.receipts.delivery.sent_at', 'en' => 'Sent At', 'sw' => 'Imetumwa'],
            ['key' => 'finance.receipts.delivery.channel', 'en' => 'Channel', 'sw' => 'Njia'],
            ['key' => 'finance.receipts.delivery.destination', 'en' => 'Destination', 'sw' => 'Inakokwenda'],
            ['key' => 'finance.receipts.delivery.sent_by', 'en' => 'Sent By', 'sw' => 'Imetumwa na'],
            ['key' => 'finance.receipts.delivery.no_logs', 'en' => 'No delivery logs yet.', 'sw' => 'Hakuna kumbukumbu za usambazaji bado.'],
            ['key' => 'finance.receipts.settings.title', 'en' => 'Receipt Delivery Settings', 'sw' => 'Mipangilio ya Usambazaji wa Stakabadhi'],
            ['key' => 'finance.receipts.settings.subtitle', 'en' => 'Control SMS receipt delivery behaviour.', 'sw' => 'Dhibiti tabia ya usambazaji wa stakabadhi kwa SMS.'],
            ['key' => 'finance.receipts.settings.sms_enabled', 'en' => 'Enable receipt SMS delivery', 'sw' => 'Washa usambazaji wa stakabadhi kwa SMS'],
            ['key' => 'finance.receipts.settings.sms_manual_only', 'en' => 'Manual send only', 'sw' => 'Kutuma kwa mikono tu'],
            ['key' => 'finance.receipts.settings.sms_auto_after_issue', 'en' => 'Auto-send after issue', 'sw' => 'Tuma moja kwa moja baada ya kutoa'],
            ['key' => 'finance.receipts.settings.sms_auto_after_print', 'en' => 'Auto-send after print', 'sw' => 'Tuma moja kwa moja baada ya kuchapisha'],
            ['key' => 'finance.receipts.settings.link_expiry_hours', 'en' => 'Link expiry (hours)', 'sw' => 'Muda wa kuisha kwa kiungo (saa)'],
            ['key' => 'finance.receipts.settings.active_template', 'en' => 'Active SMS Template', 'sw' => 'Kiolezo Hai cha SMS'],
            ['key' => 'finance.receipts.settings.allow_resend', 'en' => 'Allow resend', 'sw' => 'Ruhusu kutuma tena'],
            ['key' => 'finance.receipts.settings.max_resends', 'en' => 'Maximum resends', 'sw' => 'Idadi ya juu ya kutuma tena'],
            ['key' => 'finance.receipts.settings.save', 'en' => 'Save Settings', 'sw' => 'Hifadhi Mipangilio'],
        ];

        foreach ($rows as $row) {
            DB::table('translations')->updateOrInsert(
                ['translation_key' => $row['key']],
                [
                    'en' => $row['en'],
                    'sw' => $row['sw'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
