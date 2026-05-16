<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('site_settings')->insertOrIgnore([
            ['setting_key' => 'hero_badge', 'setting_value' => 'Rooted in faith. Built for community.', 'setting_type' => 'string', 'group_name' => 'hero', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'homepage.show_announcements', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'homepage', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'homepage.show_masses', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'homepage', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'homepage.show_projects', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'homepage', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'homepage.show_ministries', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'homepage', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'homepage.show_gallery', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'homepage', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'nav.show_history', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'navigation', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'nav.show_giving', 'setting_value' => '1', 'setting_type' => 'boolean', 'group_name' => 'navigation', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'footer.tagline', 'setting_value' => 'A welcoming digital home for worship, service, and community life.', 'setting_type' => 'string', 'group_name' => 'footer', 'is_public' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('hero_banners')->insertOrIgnore([
            [
                'id' => 1,
                'title' => 'A beautiful digital home for your parish community.',
                'subtitle' => 'Landing Experience',
                'description' => 'Modern, bilingual, and mobile-first pages designed to invite people in, communicate clearly, and guide them toward worship, service, and belonging.',
                'background_type' => 'image',
                'background_value' => null,
                'poster_image' => null,
                'primary_button_text' => 'Plan a Visit',
                'primary_button_link' => '/misa',
                'secondary_button_text' => 'Support the Mission',
                'secondary_button_link' => '/changia',
                'is_active' => 1,
                'display_order' => 1,
                'starts_at' => null,
                'ends_at' => null,
                'overlay_opacity' => 0.55,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('announcements')->insertOrIgnore([
            ['id' => 1, 'title' => 'Welcome to the new parish experience', 'slug' => 'welcome-to-the-new-parish-experience', 'summary' => 'This new landing experience is designed to make parish information easy to discover and beautiful to explore.', 'content' => '<p>This page can be edited by administrators once the CMS screens are added.</p>', 'is_published' => 1, 'is_featured' => 1, 'show_on_homepage' => 1, 'display_order' => 1, 'publish_from' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('pages')->insertOrIgnore([
            ['id' => 1, 'title' => 'Historia', 'slug' => 'historia-leo', 'excerpt' => 'A living story of faith, service, and community life.', 'content' => '<div class="content-card"><h2>Historia</h2><p>Use this page to tell the story of your parish: milestones, pastoral vision, growth, and the moments that shaped your community.</p><p>You can enrich this page with timelines, photos, or embedded media later.</p></div>', 'template' => 'default', 'is_published' => 1, 'show_in_menu' => 1, 'show_in_footer' => 1, 'menu_title' => 'Historia', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'title' => 'About the Parish', 'slug' => 'about-parish', 'excerpt' => 'Mission, vision, and values.', 'content' => '<div class="content-card"><h2>About the Parish</h2><p>This page is ready for your mission, vision, sacramental life, and pastoral priorities.</p></div>', 'template' => 'default', 'is_published' => 1, 'show_in_menu' => 0, 'show_in_footer' => 1, 'menu_title' => 'About', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('page_sections')->insertOrIgnore([
            ['page_key' => 'home', 'section_key' => 'hero', 'title' => 'Hero', 'is_enabled' => 1, 'display_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'home', 'section_key' => 'announcements', 'title' => 'Announcements', 'is_enabled' => 1, 'display_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'home', 'section_key' => 'projects', 'title' => 'Projects', 'is_enabled' => 1, 'display_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'home', 'section_key' => 'community', 'title' => 'Community', 'is_enabled' => 1, 'display_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'home', 'section_key' => 'gallery', 'title' => 'Gallery', 'is_enabled' => 1, 'display_order' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('page_sections')->where('page_key', 'home')->delete();
        DB::table('pages')->whereIn('slug', ['historia-leo', 'about-parish'])->delete();
        DB::table('announcements')->where('slug', 'welcome-to-the-new-parish-experience')->delete();
        DB::table('hero_banners')->where('id', 1)->delete();
        DB::table('site_settings')->whereIn('setting_key', [
            'hero_badge',
            'homepage.show_announcements',
            'homepage.show_masses',
            'homepage.show_projects',
            'homepage.show_ministries',
            'homepage.show_gallery',
            'nav.show_history',
            'nav.show_giving',
            'footer.tagline',
        ])->delete();
    }
};
