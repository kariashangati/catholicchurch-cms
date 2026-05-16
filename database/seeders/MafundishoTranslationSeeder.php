<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class MafundishoTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'en' => [
                'mafundisho_teaching_types' => 'Teaching Types',
                'mafundisho_teaching_types_subtitle' => 'Manage dynamic Mafundisho categories used when enrolling students.',
                'mafundisho_add_teaching_type' => 'Add Teaching Type',
                'mafundisho_edit_teaching_type' => 'Edit Teaching Type',
                'mafundisho_search_teaching_types' => 'Search by name, slug, or description',
                'mafundisho_no_teaching_types_found' => 'No teaching types found.',
                'mafundisho_sacrament_link' => 'Sacrament Link',
                'mafundisho_eligibility_rule' => 'Eligibility Rule',
                'mafundisho_requires_partner_info' => 'Requires partner information',
                'mafundisho_slug_help' => 'Leave blank to generate automatically. System type slugs are protected.',
                'mafundisho_sacrament_key_help' => 'Only link a type to a sacrament if completion should update member sacrament fields.',
                'mafundisho_type_created' => 'Teaching type created successfully.',
                'mafundisho_type_updated' => 'Teaching type updated successfully.',
                'mafundisho_type_deleted_or_deactivated' => 'Teaching type deleted or deactivated successfully.',
                'mafundisho_confirm_delete_teaching_type' => 'Delete this teaching type? If it has students, it should be deactivated instead.',
                'mafundisho_system_type_cannot_be_deleted' => 'System teaching types cannot be deleted.',
                'mafundisho_sacrament_communion' => 'Communion',
                'mafundisho_sacrament_confirmation' => 'Confirmation',
                'mafundisho_sacrament_marriage' => 'Marriage',
                'mafundisho_eligibility_all' => 'All members',
                'mafundisho_eligibility_communion' => 'Members without Communion',
                'mafundisho_eligibility_confirmation' => 'Members without Confirmation',
                'mafundisho_eligibility_marriage' => 'Members not marked as married',
                'mafundisho_status_continuing' => 'Continuing',
                'mafundisho_status_completed' => 'Completed',
                'mafundisho_status_failed' => 'Failed',
                'mafundisho_status_repeated' => 'Repeated',
                'mafundisho_status_withdrawn' => 'Withdrawn',
                'teaching_types' => 'Teaching Types',
                'students' => 'Students',
                'slug' => 'Slug',
                'sort_order' => 'Sort Order',
                'inactive' => 'Inactive',
                'none' => 'None',
                'fix_errors' => 'Please fix the following errors.',
            ],
            'sw' => [
                'mafundisho_teaching_types' => 'Aina za Mafundisho',
                'mafundisho_teaching_types_subtitle' => 'Simamia aina za mafundisho zinazotumika kuandikisha wanafunzi.',
                'mafundisho_add_teaching_type' => 'Ongeza Aina ya Mafundisho',
                'mafundisho_edit_teaching_type' => 'Hariri Aina ya Mafundisho',
                'mafundisho_search_teaching_types' => 'Tafuta kwa jina, slug, au maelezo',
                'mafundisho_no_teaching_types_found' => 'Hakuna aina ya mafundisho iliyopatikana.',
                'mafundisho_sacrament_link' => 'Muunganiko wa Sakramenti',
                'mafundisho_eligibility_rule' => 'Kanuni ya Ustahiki',
                'mafundisho_requires_partner_info' => 'Inahitaji taarifa za mwenza',
                'mafundisho_slug_help' => 'Acha wazi ili itengenezwe kiotomatiki. Slug za mfumo zinalindwa.',
                'mafundisho_sacrament_key_help' => 'Unganisha na sakramenti tu kama kukamilisha kunapaswa kusasisha taarifa za muumini.',
                'mafundisho_type_created' => 'Aina ya mafundisho imeundwa kikamilifu.',
                'mafundisho_type_updated' => 'Aina ya mafundisho imesasishwa kikamilifu.',
                'mafundisho_type_deleted_or_deactivated' => 'Aina ya mafundisho imefutwa au imezimwa kikamilifu.',
                'mafundisho_confirm_delete_teaching_type' => 'Unataka kufuta aina hii ya mafundisho? Kama ina wanafunzi, izimwe badala ya kufutwa.',
                'mafundisho_system_type_cannot_be_deleted' => 'Aina za mafundisho za mfumo haziwezi kufutwa.',
                'mafundisho_sacrament_communion' => 'Komunio',
                'mafundisho_sacrament_confirmation' => 'Kipaimara',
                'mafundisho_sacrament_marriage' => 'Ndoa',
                'mafundisho_eligibility_all' => 'Waumini wote',
                'mafundisho_eligibility_communion' => 'Waumini ambao hawajapata Komunio',
                'mafundisho_eligibility_confirmation' => 'Waumini ambao hawajapata Kipaimara',
                'mafundisho_eligibility_marriage' => 'Waumini ambao hawajawekwa kama waliooa/kuolewa',
                'mafundisho_status_continuing' => 'Inaendelea',
                'mafundisho_status_completed' => 'Imekamilika',
                'mafundisho_status_failed' => 'Imeshindwa',
                'mafundisho_status_repeated' => 'Inarudia',
                'mafundisho_status_withdrawn' => 'Imejiondoa',
                'teaching_types' => 'Aina za Mafundisho',
                'students' => 'Wanafunzi',
                'slug' => 'Slug',
                'sort_order' => 'Mpangilio',
                'inactive' => 'Haifanyi kazi',
                'none' => 'Hakuna',
                'fix_errors' => 'Tafadhali rekebisha makosa yafuatayo.',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                Translation::query()->updateOrCreate(
                    ['locale' => $locale, 'translation_key' => $key],
                    ['translation_value' => $value]
                );

                Cache::forget("translation_{$locale}_{$key}");
            }
        }
    }
}
