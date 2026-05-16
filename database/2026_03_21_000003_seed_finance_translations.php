<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $rows = [
            ['en','finance_dashboard','Finance Dashboard'],
            ['sw','finance_dashboard','Dashibodi ya Fedha'],
            ['en','finance_module_description','Manage tithes, main offerings, and other contributions safely.'],
            ['sw','finance_module_description','Simamia zaka, sadaka kuu, na michango mingine kwa usalama.'],
            ['en','cash_contributions','Cash Contributions'],
            ['sw','cash_contributions','Michango ya Fedha Taslimu'],
            ['en','mass_type','Mass Type'],
            ['sw','mass_type','Aina ya Misa'],
            ['en','payment_method','Payment Method'],
            ['sw','payment_method','Njia ya Malipo'],
            ['en','reference_no','Reference No'],
            ['sw','reference_no','Namba ya Kumbukumbu'],
            ['en','receipt_no','Receipt No'],
            ['sw','receipt_no','Namba ya Risiti'],
            ['en','approved_by','Approved By'],
            ['sw','approved_by','Imeidhinishwa na'],
            ['en','approved_at','Approved At'],
            ['sw','approved_at','Tarehe ya Uidhinishaji'],
            ['en','record_created_successfully','Record created successfully.'],
            ['sw','record_created_successfully','Taarifa imeongezwa kikamilifu.'],
            ['en','record_updated_successfully','Record updated successfully.'],
            ['sw','record_updated_successfully','Taarifa imeboreshwa kikamilifu.'],
            ['en','record_deleted_successfully','Record deleted successfully.'],
            ['sw','record_deleted_successfully','Taarifa imefutwa kikamilifu.'],
            ['en','add_tithe','Add Tithe'],
            ['sw','add_tithe','Ongeza Zaka'],
            ['en','add_contribution','Add Contribution'],
            ['sw','add_contribution','Ongeza Mchango'],
            ['en','add_main_offering','Add Main Offering'],
            ['sw','add_main_offering','Ongeza Sadaka Kuu'],
            ['en','manage_tithes','Manage Tithes'],
            ['sw','manage_tithes','Simamia Zaka'],
            ['en','manage_contributions','Manage Contributions'],
            ['sw','manage_contributions','Simamia Michango'],
            ['en','manage_main_offerings','Manage Main Offerings'],
            ['sw','manage_main_offerings','Simamia Sadaka Kuu'],
            ['en','amount','Amount'],
            ['sw','amount','Kiasi'],
            ['en','contribution_date','Contribution Date'],
            ['sw','contribution_date','Tarehe ya Mchango'],
            ['en','offering_date','Offering Date'],
            ['sw','offering_date','Tarehe ya Sadaka'],
            ['en','actions','Actions'],
            ['sw','actions','Vitendo'],
            ['en','notes','Notes'],
            ['sw','notes','Maelezo'],
            ['en','member_optional','Member (Optional)'],
            ['sw','member_optional','Mshiriki (Si lazima)'],
            ['en','group_name','Group Name'],
            ['sw','group_name','Jina la Kundi'],
            ['en','finance_reports','Finance Reports'],
            ['sw','finance_reports','Ripoti za Fedha'],
            ['en','filter_records','Filter Records'],
            ['sw','filter_records','Chuja Taarifa'],
            ['en','all_statuses','All Statuses'],
            ['sw','all_statuses','Hali Zote'],
            ['en','all_months','All Months'],
            ['sw','all_months','Miezi Yote'],
            ['en','all_types','All Types'],
            ['sw','all_types','Aina Zote'],
            ['en','all_mass_types','All Mass Types'],
            ['sw','all_mass_types','Aina Zote za Misa'],
            ['en','this_year_financial_summary','This year financial summary'],
            ['sw','this_year_financial_summary','Muhtasari wa fedha wa mwaka huu'],
            ['en','grand_total','Grand Total'],
            ['sw','grand_total','Jumla Kuu'],
        ];

        foreach ($rows as [$locale, $key, $value]) {
            DB::table('translations')->updateOrInsert(
                ['locale' => $locale, 'translation_key' => $key],
                ['translation_value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('translations')->whereIn('translation_key', [
            'finance_dashboard','finance_module_description','cash_contributions','mass_type','payment_method','reference_no','receipt_no',
            'approved_by','approved_at','record_created_successfully','record_updated_successfully','record_deleted_successfully','add_tithe',
            'add_contribution','add_main_offering','manage_tithes','manage_contributions','manage_main_offerings','amount','contribution_date',
            'offering_date','actions','notes','member_optional','group_name','finance_reports','filter_records','all_statuses','all_months',
            'all_types','all_mass_types','this_year_financial_summary','grand_total'
        ])->delete();
    }
};
