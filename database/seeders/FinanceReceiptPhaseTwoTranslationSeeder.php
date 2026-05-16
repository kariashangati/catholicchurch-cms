<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FinanceReceiptPhaseTwoTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            ['group' => 'finance.receipts', 'key' => 'batch_pack_title', 'locale' => 'en', 'value' => 'Receipt Batch Pack'],
            ['group' => 'finance.receipts', 'key' => 'batch_pack_title', 'locale' => 'sw', 'value' => 'Kifurushi cha Stakabadhi'],
            ['group' => 'finance.receipts', 'key' => 'register', 'locale' => 'en', 'value' => 'Receipt Register'],
            ['group' => 'finance.receipts', 'key' => 'register', 'locale' => 'sw', 'value' => 'Daftari la Stakabadhi'],
            ['group' => 'finance.receipts', 'key' => 'history', 'locale' => 'en', 'value' => 'Receipt History'],
            ['group' => 'finance.receipts', 'key' => 'history', 'locale' => 'sw', 'value' => 'Historia ya Stakabadhi'],
            ['group' => 'finance.receipts', 'key' => 'reprint_reason', 'locale' => 'en', 'value' => 'Reprint Reason'],
            ['group' => 'finance.receipts', 'key' => 'reprint_reason', 'locale' => 'sw', 'value' => 'Sababu ya Kuchapisha Tena'],
            ['group' => 'finance.receipts', 'key' => 'void_reason', 'locale' => 'en', 'value' => 'Void Reason'],
            ['group' => 'finance.receipts', 'key' => 'void_reason', 'locale' => 'sw', 'value' => 'Sababu ya Kubatilisha'],
            ['group' => 'finance.receipts', 'key' => 'export', 'locale' => 'en', 'value' => 'Export'],
            ['group' => 'finance.receipts', 'key' => 'export', 'locale' => 'sw', 'value' => 'Hamisha'],
            ['group' => 'finance.receipts', 'key' => 'batch_issue', 'locale' => 'en', 'value' => 'Issue Batch'],
            ['group' => 'finance.receipts', 'key' => 'batch_issue', 'locale' => 'sw', 'value' => 'Toa kwa Mkusanyiko'],
            ['group' => 'finance.receipts', 'key' => 'status.reprinted', 'locale' => 'en', 'value' => 'Reprinted'],
            ['group' => 'finance.receipts', 'key' => 'status.reprinted', 'locale' => 'sw', 'value' => 'Imechapishwa Tena'],
            ['group' => 'finance.receipts', 'key' => 'status.voided', 'locale' => 'en', 'value' => 'Voided'],
            ['group' => 'finance.receipts', 'key' => 'status.voided', 'locale' => 'sw', 'value' => 'Imebatilishwa'],
        ];

        foreach ($rows as $row) {
            DB::table('translations')->updateOrInsert(
                [
                    'translation_group' => $row['group'],
                    'translation_key' => $row['key'],
                    'locale' => $row['locale'],
                ],
                [
                    'translation_value' => $row['value'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
