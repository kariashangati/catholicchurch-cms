<?php

namespace App\Services\Cms;

use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\HeroBanner;
use App\Models\History;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CmsDashboardService
{
    public function data(): array
    {
        return [
            'page' => [
                'title' => db_trans('content_management_center'),
                'updated_at' => now(),
            ],

            'kpis' => [
                [
                    'title' => db_trans('hero_banners'),
                    'value' => HeroBanner::query()->count(),
                    'icon' => 'fas fa-images',
                    'tone' => 'primary',
                ],
                [
                    'title' => db_trans('histories'),
                    'value' => History::query()->count(),
                    'icon' => 'fas fa-landmark',
                    'tone' => 'info',
                ],
                [
                    'title' => db_trans('announcements'),
                    'value' => Announcement::query()->count(),
                    'icon' => 'fas fa-bullhorn',
                    'tone' => 'warning',
                ],
                [
                    'title' => db_trans('pages'),
                    'value' => Page::query()->count(),
                    'icon' => 'fas fa-file-alt',
                    'tone' => 'secondary',
                ],
                [
                    'title' => db_trans('galleries'),
                    'value' => Gallery::query()->count(),
                    'icon' => 'fas fa-camera-retro',
                    'tone' => 'success',
                ],
                [
                    'title' => db_trans('visitors'),
                    'value' => $this->visitorTotal(),
                    'icon' => 'fas fa-eye',
                    'tone' => 'danger',
                ],
            ],

            'visitorTrend' => $this->visitorTrend(),

            'recent' => [
                'hero_banners' => HeroBanner::query()->latest()->take(5)->get(),
                'histories' => History::query()->latest()->take(5)->get(),
                'announcements' => Announcement::query()->latest()->take(5)->get(),
                'pages' => Page::query()->latest()->take(5)->get(),
                'galleries' => Gallery::query()->latest()->take(5)->get(),
            ],

            'navigation' => [
                'show_home' => $this->setting('nav.show_home', true),
                'show_history' => $this->setting('nav.show_history', true),
                'show_giving' => $this->setting('nav.show_giving', true),
                'show_kanda' => $this->setting('nav.show_kanda', true),
                'show_jumuiya' => $this->setting('nav.show_jumuiya', true),
                'show_masses' => $this->setting('nav.show_masses', true),
                'show_announcements' => $this->setting('nav.show_announcements', true),
                'show_projects' => $this->setting('nav.show_projects', true),
                'show_leadership' => $this->setting('nav.show_leadership', true),
                'show_gallery' => $this->setting('nav.show_gallery', true),
                'show_contact' => $this->setting('nav.show_contact', true),
                'show_login' => $this->setting('nav.show_login', true),
                'show_ministries' => $this->setting('nav.show_ministries', false),
            ],
        ];
    }

    protected function visitorTotal(): int
    {
        if (! Schema::hasTable('visitor_logs')) {
            return 0;
        }

        return (int) DB::table('visitor_logs')->count();
    }

    protected function visitorTrend(int $days = 14): array
    {
        $labels = [];
        $values = [];

        if (! Schema::hasTable('visitor_logs')) {
            return ['labels' => $labels, 'values' => $values];
        }

        $start = now()->subDays($days - 1)->toDateString();
        $raw = DB::table('visitor_logs')
            ->select('visit_date', DB::raw('COUNT(DISTINCT COALESCE(session_id, ip_address, id)) as total'))
            ->whereDate('visit_date', '>=', $start)
            ->groupBy('visit_date')
            ->pluck('total', 'visit_date');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->translatedFormat('d M');
            $values[] = (int) ($raw[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        $setting = SiteSetting::query()->where('setting_key', $key)->first();

        return $setting?->typed_value ?? $default;
    }
}
