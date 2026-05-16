<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceReceiptPhaseThreeTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['group' => 'finance.receipts.verify', 'key' => 'title', 'text_sw' => 'Uhakiki wa Stakabadhi', 'text_en' => 'Receipt Verification'],
            ['group' => 'finance.receipts.verify', 'key' => 'subtitle', 'text_sw' => 'Thibitisha uhalali wa stakabadhi kwa namba au tokeni ya uthibitisho.', 'text_en' => 'Confirm receipt authenticity by receipt number or verification token.'],
            ['group' => 'finance.receipts.verify', 'key' => 'search_title', 'text_sw' => 'Tafuta stakabadhi', 'text_en' => 'Search receipt'],
            ['group' => 'finance.receipts.verify', 'key' => 'result_title', 'text_sw' => 'Matokeo ya uhakiki', 'text_en' => 'Verification result'],
            ['group' => 'finance.receipts.verify', 'key' => 'result_subtitle', 'text_sw' => 'Mfumo unaonyesha hali ya uhalali wa stakabadhi iliyotafutwa.', 'text_en' => 'The system shows the validity status of the searched receipt.'],
            ['group' => 'finance.receipts.verify', 'key' => 'valid', 'text_sw' => 'Halali', 'text_en' => 'Valid'],
            ['group' => 'finance.receipts.verify', 'key' => 'invalid', 'text_sw' => 'Si halali', 'text_en' => 'Invalid'],
            ['group' => 'finance.receipts.verify', 'key' => 'empty_title', 'text_sw' => 'Bado hujaanza uhakiki', 'text_en' => 'No verification yet'],
            ['group' => 'finance.receipts.verify', 'key' => 'empty_text', 'text_sw' => 'Ingiza taarifa za stakabadhi ili kupata majibu ya uhakiki.', 'text_en' => 'Enter receipt details to see verification results.'],
            ['group' => 'finance.receipts.verify.fields', 'key' => 'receipt_reference', 'text_sw' => 'Namba ya Stakabadhi', 'text_en' => 'Receipt Number'],
            ['group' => 'finance.receipts.verify.fields', 'key' => 'verification_token', 'text_sw' => 'Tokeni ya Uthibitisho', 'text_en' => 'Verification Token'],
            ['group' => 'finance.receipts.verify.fields', 'key' => 'message', 'text_sw' => 'Ufafanuzi', 'text_en' => 'Message'],
            ['group' => 'finance.receipts.verify.placeholders', 'key' => 'receipt_reference', 'text_sw' => 'Mfano: RCT-TITHE-2026-000021', 'text_en' => 'Example: RCT-TITHE-2026-000021'],
            ['group' => 'finance.receipts.verify.placeholders', 'key' => 'verification_token', 'text_sw' => 'Mfano: abc123token', 'text_en' => 'Example: abc123token'],
            ['group' => 'finance.receipts.verify.actions', 'key' => 'verify', 'text_sw' => 'Thibitisha', 'text_en' => 'Verify'],
            ['group' => 'finance.receipts.verify.actions', 'key' => 'copy_access_link', 'text_sw' => 'Nakili Kiungo', 'text_en' => 'Copy Access Link'],
            ['group' => 'finance.receipts.public', 'key' => 'title', 'text_sw' => 'Upatikanaji Salama wa Stakabadhi', 'text_en' => 'Secure Receipt Access'],
            ['group' => 'finance.receipts.public', 'key' => 'secure_access', 'text_sw' => 'Kiungo Salama', 'text_en' => 'Secure Access'],
            ['group' => 'finance.receipts.public', 'key' => 'receipt_ready', 'text_sw' => 'Stakabadhi yako iko tayari', 'text_en' => 'Your receipt is ready'],
            ['group' => 'finance.receipts.public', 'key' => 'receipt_ready_text', 'text_sw' => 'Unaweza kuona, kuthibitisha au kupakua stakabadhi yako kupitia ukurasa huu salama.', 'text_en' => 'You can view, verify, or download your receipt from this secure page.'],
            ['group' => 'finance.receipts.public.actions', 'key' => 'download_pdf', 'text_sw' => 'Pakua PDF', 'text_en' => 'Download PDF'],
            ['group' => 'finance.receipts.public.actions', 'key' => 'verify_receipt', 'text_sw' => 'Thibitisha Stakabadhi', 'text_en' => 'Verify Receipt'],
            ['group' => 'finance.receipts.public.status', 'key' => 'valid', 'text_sw' => 'Kiungo halali', 'text_en' => 'Valid link'],
            ['group' => 'finance.receipts.public.status', 'key' => 'expired', 'text_sw' => 'Kiungo kimekwisha muda', 'text_en' => 'Link expired'],
            ['group' => 'finance.receipts.public.status', 'key' => 'revoked', 'text_sw' => 'Kiungo kimefutwa', 'text_en' => 'Link revoked'],
            ['group' => 'finance.receipts.public.status', 'key' => 'invalid', 'text_sw' => 'Kiungo si sahihi', 'text_en' => 'Invalid link'],
            ['group' => 'finance.receipts.public.status', 'key' => 'disabled', 'text_sw' => 'Upatikanaji umezimwa', 'text_en' => 'Access disabled'],
            ['group' => 'finance.receipts.public.status', 'key' => 'loading', 'text_sw' => 'Inapakia', 'text_en' => 'Loading'],
            ['group' => 'finance.receipts.pdf', 'key' => 'secure_access_link', 'text_sw' => 'Kiungo Salama cha Upakuaji', 'text_en' => 'Secure Download Link'],
            ['group' => 'finance.receipts.pdf', 'key' => 'link_expires_at', 'text_sw' => 'Kiungo kitaisha', 'text_en' => 'Link expires at'],
            ['group' => 'finance.receipts.pdf', 'key' => 'verify_online', 'text_sw' => 'Thibitisha Mtandaoni', 'text_en' => 'Verify Online'],
        ];

        foreach ($rows as $row) {
            DB::table('translations')->updateOrInsert(
                ['group' => $row['group'], 'key' => $row['key']],
                ['text_sw' => $row['text_sw'], 'text_en' => $row['text_en'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
