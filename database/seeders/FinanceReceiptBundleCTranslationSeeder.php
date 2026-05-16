<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class FinanceReceiptBundleCTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'finance.receipts.analytics.title' => ['en' => 'Receipt Analytics', 'sw' => 'Uchambuzi wa Stakabadhi'],
            'finance.receipts.analytics.subtitle' => ['en' => 'Monitor receipt performance and delivery trends.', 'sw' => 'Fuatilia mwenendo wa stakabadhi na utoaji wake.'],
            'finance.receipts.analytics.actions.view_exceptions' => ['en' => 'View Exceptions', 'sw' => 'Tazama Hitilafu'],
            'finance.receipts.analytics.cards.issued_today' => ['en' => 'Issued Today', 'sw' => 'Zilizotolewa Leo'],
            'finance.receipts.analytics.cards.printed_this_month' => ['en' => 'Printed This Month', 'sw' => 'Zilizochapishwa Mwezi Huu'],
            'finance.receipts.analytics.cards.downloads' => ['en' => 'Downloads', 'sw' => 'Upakuaji'],
            'finance.receipts.analytics.cards.verification_checks' => ['en' => 'Verification Checks', 'sw' => 'Uhakiki'],
            'finance.receipts.analytics.charts.title' => ['en' => 'Charts', 'sw' => 'Chati'],
            'finance.receipts.analytics.charts.monthly_issuance' => ['en' => 'Monthly Issuance', 'sw' => 'Utowaji wa Kila Mwezi'],
            'finance.receipts.analytics.charts.delivery_performance' => ['en' => 'Delivery Performance', 'sw' => 'Utendaji wa Uwasilishaji'],
            'finance.receipts.analytics.insights.title' => ['en' => 'Insights', 'sw' => 'Mwenendo Muhimu'],
            'finance.receipts.analytics.insights.top_jumuiyas' => ['en' => 'Top Jumuiyas', 'sw' => 'Jumuiya Zinazoongoza'],
            'finance.receipts.analytics.insights.delivery_failures' => ['en' => 'Delivery Failures', 'sw' => 'Hitilafu za Uwasilishaji'],
            'finance.receipts.exceptions.title' => ['en' => 'Receipt Exceptions', 'sw' => 'Hitilafu za Stakabadhi'],
            'finance.receipts.exceptions.subtitle' => ['en' => 'Review broken or suspicious receipt records.', 'sw' => 'Kagua kumbukumbu za stakabadhi zilizovunjika au zinazotiliwa shaka.'],
            'finance.receipts.exceptions.show_title' => ['en' => 'Exception Details', 'sw' => 'Maelezo ya Hitilafu'],
            'finance.receipts.exceptions.context_title' => ['en' => 'Context', 'sw' => 'Muktadha'],
            'finance.receipts.exceptions.fields.type' => ['en' => 'Type', 'sw' => 'Aina'],
            'finance.receipts.exceptions.fields.severity' => ['en' => 'Severity', 'sw' => 'Uzito'],
            'finance.receipts.exceptions.fields.message' => ['en' => 'Message', 'sw' => 'Ujumbe'],
            'finance.receipts.exceptions.fields.receipt' => ['en' => 'Receipt', 'sw' => 'Stakabadhi'],
            'finance.receipts.exceptions.fields.status' => ['en' => 'Status', 'sw' => 'Hali'],
            'finance.receipts.exceptions.fields.resolution_note' => ['en' => 'Resolution Note', 'sw' => 'Maelezo ya Utatuzi'],
            'finance.receipts.exceptions.actions.back' => ['en' => 'Back', 'sw' => 'Rudi'],
            'finance.receipts.exceptions.actions.resolve' => ['en' => 'Resolve Exception', 'sw' => 'Tatua Hitilafu'],
            'finance.receipts.exceptions.open' => ['en' => 'Open', 'sw' => 'Wazi'],
            'finance.receipts.exceptions.resolved' => ['en' => 'Resolved', 'sw' => 'Imetatuliwa'],
            'finance.receipts.exceptions.severity.low' => ['en' => 'Low', 'sw' => 'Ndogo'],
            'finance.receipts.exceptions.severity.medium' => ['en' => 'Medium', 'sw' => 'Wastani'],
            'finance.receipts.exceptions.severity.high' => ['en' => 'High', 'sw' => 'Kubwa'],
            'finance.receipts.exceptions.severity.critical' => ['en' => 'Critical', 'sw' => 'Muhimu Sana'],
            'finance.receipts.pdf.individual_title' => ['en' => 'Official Receipt', 'sw' => 'Stakabadhi Rasmi'],
            'finance.receipts.pdf.summary_title' => ['en' => 'Official Summary Receipt', 'sw' => 'Muhtasari Rasmi wa Stakabadhi'],
            'finance.receipts.pdf.footer_note' => ['en' => 'This receipt was generated electronically and remains valid.', 'sw' => 'Stakabadhi hii imezalishwa kielektroniki na ni halali.'],
            'finance.receipts.pdf.generated_at' => ['en' => 'Generated At', 'sw' => 'Imezalishwa Tarehe'],
        ];
        foreach ($translations as $key => $value) {
            foreach ($value as $locale => $text) {
                DB::table('translations')->updateOrInsert(
                    ['translation_key' => $key, 'locale' => $locale],
                    ['translation_value' => $text, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }
}
