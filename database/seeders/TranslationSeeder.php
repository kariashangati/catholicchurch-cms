<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['locale' => 'en', 'translation_key' => 'dashboard', 'translation_value' => 'Dashboard'],
            ['locale' => 'sw', 'translation_key' => 'dashboard', 'translation_value' => 'Dashibodi'],

            ['locale' => 'en', 'translation_key' => 'admin_overview', 'translation_value' => 'Admin Overview'],
            ['locale' => 'sw', 'translation_key' => 'admin_overview', 'translation_value' => 'Muhtasari wa Usimamizi'],

            ['locale' => 'en', 'translation_key' => 'welcome_back', 'translation_value' => 'Welcome back'],
            ['locale' => 'sw', 'translation_key' => 'welcome_back', 'translation_value' => 'Karibu tena'],

            ['locale' => 'en', 'translation_key' => 'generate_report', 'translation_value' => 'Generate Report'],
            ['locale' => 'sw', 'translation_key' => 'generate_report', 'translation_value' => 'Tengeneza Ripoti'],

            ['locale' => 'en', 'translation_key' => 'total_kandas', 'translation_value' => 'Total Kandas'],
            ['locale' => 'sw', 'translation_key' => 'total_kandas', 'translation_value' => 'Jumla ya Kanda'],

            ['locale' => 'en', 'translation_key' => 'total_jumuiyas', 'translation_value' => 'Total Jumuiyas'],
            ['locale' => 'sw', 'translation_key' => 'total_jumuiyas', 'translation_value' => 'Jumla ya Jumuiya'],

            ['locale' => 'en', 'translation_key' => 'total_familias', 'translation_value' => 'Total Families'],
            ['locale' => 'sw', 'translation_key' => 'total_familias', 'translation_value' => 'Jumla ya Familia'],

            ['locale' => 'en', 'translation_key' => 'total_members', 'translation_value' => 'Total Members'],
            ['locale' => 'sw', 'translation_key' => 'total_members', 'translation_value' => 'Jumla ya Waumini'],

            ['locale' => 'en', 'translation_key' => 'male_members', 'translation_value' => 'Male Members'],
            ['locale' => 'sw', 'translation_key' => 'male_members', 'translation_value' => 'Waumini wa Kiume'],

            ['locale' => 'en', 'translation_key' => 'female_members', 'translation_value' => 'Female Members'],
            ['locale' => 'sw', 'translation_key' => 'female_members', 'translation_value' => 'Waumini wa Kike'],

            ['locale' => 'en', 'translation_key' => 'users', 'translation_value' => 'Users'],
            ['locale' => 'sw', 'translation_key' => 'users', 'translation_value' => 'Watumiaji'],

            ['locale' => 'en', 'translation_key' => 'apostolic_groups', 'translation_value' => 'Apostolic Groups'],
            ['locale' => 'sw', 'translation_key' => 'apostolic_groups', 'translation_value' => 'Vyama vya Kitume'],

            ['locale' => 'en', 'translation_key' => 'member_growth', 'translation_value' => 'Member Growth'],
            ['locale' => 'sw', 'translation_key' => 'member_growth', 'translation_value' => 'Ongezeko la Waumini'],

            ['locale' => 'en', 'translation_key' => 'member_growth_over_time', 'translation_value' => 'Member growth over time'],
            ['locale' => 'sw', 'translation_key' => 'member_growth_over_time', 'translation_value' => 'Ongezeko la waumini kwa muda'],

            ['locale' => 'en', 'translation_key' => 'current_language', 'translation_value' => 'Current Language'],
            ['locale' => 'sw', 'translation_key' => 'current_language', 'translation_value' => 'Lugha ya Sasa'],

            ['locale' => 'en', 'translation_key' => 'active_users', 'translation_value' => 'Active Users'],
            ['locale' => 'sw', 'translation_key' => 'active_users', 'translation_value' => 'Watumiaji Hai'],

            ['locale' => 'en', 'translation_key' => 'new_members_this_month', 'translation_value' => 'New Members This Month'],
            ['locale' => 'sw', 'translation_key' => 'new_members_this_month', 'translation_value' => 'Waumini Wapya Mwezi Huu'],

            ['locale' => 'en', 'translation_key' => 'quick_actions', 'translation_value' => 'Quick Actions'],
            ['locale' => 'sw', 'translation_key' => 'quick_actions', 'translation_value' => 'Vitendo vya Haraka'],

            ['locale' => 'en', 'translation_key' => 'members', 'translation_value' => 'Members'],
            ['locale' => 'sw', 'translation_key' => 'members', 'translation_value' => 'Waumini'],

            ['locale' => 'en', 'translation_key' => 'translations', 'translation_value' => 'Translations'],
            ['locale' => 'sw', 'translation_key' => 'translations', 'translation_value' => 'Tafsiri'],

            ['locale' => 'en', 'translation_key' => 'profile', 'translation_value' => 'Profile'],
            ['locale' => 'sw', 'translation_key' => 'profile', 'translation_value' => 'Wasifu'],

            ['locale' => 'en', 'translation_key' => 'financial_overview', 'translation_value' => 'Financial Overview'],
            ['locale' => 'sw', 'translation_key' => 'financial_overview', 'translation_value' => 'Muhtasari wa Fedha'],

            ['locale' => 'en', 'translation_key' => 'this_month', 'translation_value' => 'This Month'],
            ['locale' => 'sw', 'translation_key' => 'this_month', 'translation_value' => 'Mwezi Huu'],

            ['locale' => 'en', 'translation_key' => 'tithes', 'translation_value' => 'Tithes'],
            ['locale' => 'sw', 'translation_key' => 'tithes', 'translation_value' => 'Zaka'],

            ['locale' => 'en', 'translation_key' => 'main_offerings', 'translation_value' => 'Main Offerings'],
            ['locale' => 'sw', 'translation_key' => 'main_offerings', 'translation_value' => 'Sadaka Kuu'],

            ['locale' => 'en', 'translation_key' => 'harvest', 'translation_value' => 'Harvest'],
            ['locale' => 'sw', 'translation_key' => 'harvest', 'translation_value' => 'Mavuno'],

            ['locale' => 'en', 'translation_key' => 'pending_finance_records', 'translation_value' => 'Pending Finance Records'],
            ['locale' => 'sw', 'translation_key' => 'pending_finance_records', 'translation_value' => 'Taarifa za Fedha Zinazosubiri'],

            ['locale' => 'en', 'translation_key' => 'sacrament_overview', 'translation_value' => 'Sacrament Overview'],
            ['locale' => 'sw', 'translation_key' => 'sacrament_overview', 'translation_value' => 'Muhtasari wa Sakramenti'],

            ['locale' => 'en', 'translation_key' => 'baptized', 'translation_value' => 'Baptized'],
            ['locale' => 'sw', 'translation_key' => 'baptized', 'translation_value' => 'Waliobatizwa'],

            ['locale' => 'en', 'translation_key' => 'communion', 'translation_value' => 'Communion'],
            ['locale' => 'sw', 'translation_key' => 'communion', 'translation_value' => 'Komunio'],

            ['locale' => 'en', 'translation_key' => 'confirmation', 'translation_value' => 'Confirmation'],
            ['locale' => 'sw', 'translation_key' => 'confirmation', 'translation_value' => 'Kipaimara'],

            ['locale' => 'en', 'translation_key' => 'married', 'translation_value' => 'Married'],
            ['locale' => 'sw', 'translation_key' => 'married', 'translation_value' => 'Wenye Ndoa'],

            ['locale' => 'en', 'translation_key' => 'receiving_eucharist', 'translation_value' => 'Receiving Eucharist'],
            ['locale' => 'sw', 'translation_key' => 'receiving_eucharist', 'translation_value' => 'Wanaopokea Ekaristi'],

            ['locale' => 'en', 'translation_key' => 'recent_members', 'translation_value' => 'Recent Members'],
            ['locale' => 'sw', 'translation_key' => 'recent_members', 'translation_value' => 'Waumini wa Hivi Karibuni'],

            ['locale' => 'en', 'translation_key' => 'latest_registered_members', 'translation_value' => 'Latest registered members'],
            ['locale' => 'sw', 'translation_key' => 'latest_registered_members', 'translation_value' => 'Waumini waliosajiliwa karibuni'],

            ['locale' => 'en', 'translation_key' => 'member', 'translation_value' => 'Member'],
            ['locale' => 'sw', 'translation_key' => 'member', 'translation_value' => 'Muumini'],

            ['locale' => 'en', 'translation_key' => 'gender', 'translation_value' => 'Gender'],
            ['locale' => 'sw', 'translation_key' => 'gender', 'translation_value' => 'Jinsia'],

            ['locale' => 'en', 'translation_key' => 'phone', 'translation_value' => 'Phone'],
            ['locale' => 'sw', 'translation_key' => 'phone', 'translation_value' => 'Simu'],

            ['locale' => 'en', 'translation_key' => 'joined', 'translation_value' => 'Joined'],
            ['locale' => 'sw', 'translation_key' => 'joined', 'translation_value' => 'Amejiunga'],

            ['locale' => 'en', 'translation_key' => 'no_members_found', 'translation_value' => 'No members found'],
            ['locale' => 'sw', 'translation_key' => 'no_members_found', 'translation_value' => 'Hakuna waumini waliopatikana'],
        ];

        foreach ($rows as $row) {
            Translation::updateOrCreate(
                [
                    'locale' => $row['locale'],
                    'translation_key' => $row['translation_key'],
                ],
                [
                    'translation_value' => $row['translation_value'],
                ]
            );
        }
    }
}