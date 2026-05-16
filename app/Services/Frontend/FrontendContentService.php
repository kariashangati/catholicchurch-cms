<?php

namespace App\Services\Frontend;

use App\Models\Announcement;
use App\Models\ApostolicGroup;
use App\Models\BankAccount;
use App\Models\CentreDetail;
use App\Models\ContributionType;
use App\Models\Gallery;
use App\Models\HeroBanner;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\LeadershipAssignment;
use App\Models\MassSchedule;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use App\Models\History;
use App\Models\ContactMessage;
use App\Models\ContactReason;

class FrontendContentService
{
	
	

public function contactPage(): array
{
    $settings = $this->settings();

    $reasons = Schema::hasTable('contact_reasons')
        ? ContactReason::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
        : collect();

    return [
        'settings' => $settings,

        'reasons' => $reasons,
        'kandas' => $this->kandas(100),
        'jumuiyas' => $this->jumuiyas(200),

      
        'contactEmail' => $settings['church.email'] ?? null,
        'contactPhone' => $settings['church.phone'] ?? null,
        'contactAddress' => $settings['church.address'] ?? null,

        
        'mapEmbed' => $settings['contact.map_embed'] ?? null,

     
        'workingHours' => $settings['contact.working_hours'] ?? null,
    ];
}
	public function histories(int $limit = 50): Collection
{
    if (! Schema::hasTable('histories')) {
        return collect();
    }

    return History::query()
        ->where('is_published', true)
        ->orderByDesc('is_featured')
        ->orderBy('display_order')
        ->orderByDesc('published_at')
        ->limit($limit)
        ->get()
        ->map(function ($history) {

            $history->image_url = $this->assetUrl($history->featured_image);

            $history->card_date = optional($history->published_at)->format('M d');
            $history->card_date_full = optional($history->published_at)->format('M d, Y');

            $history->card_excerpt = $history->excerpt
                ?: \Illuminate\Support\Str::limit(strip_tags($history->content), 140);

            return $history;
        });
}

public function historiesPage(): array
{
    $items = $this->histories(100)->values();

    $featured = $items->first(fn ($item) => $item->is_featured);

    if (! $featured && $items->isNotEmpty()) {
        $featured = $items->first();
    }

    $gridItems = $featured
        ? $items->reject(fn ($item) => $item->id === $featured->id)->values()
        : $items;

    return [
        'items' => $items,
        'featured' => $featured,
        'gridItems' => $gridItems,
        'totalCount' => $items->count(),
        'featuredCount' => $items->where('is_featured', true)->count(),
    ];
}

public function historyBySlug(string $slug): ?History
{
    if (! Schema::hasTable('histories')) {
        return null;
    }

    $history = History::query()
        ->where('slug', $slug)
        ->where('is_published', true)
        ->first();

    if (! $history) {
        return null;
    }

    $history->image_url = $this->assetUrl($history->featured_image);

    return $history;
}
public function relatedHistories(?History $current = null, int $limit = 3): Collection
{
    if (! Schema::hasTable('histories')) {
        return collect();
    }

    $query = History::query()
        ->where('is_published', true);

    if ($current) {
        $query->where('id', '!=', $current->id);
    }

    return $query
        ->orderByDesc('is_featured')
        ->orderByDesc('published_at')
        ->limit($limit)
        ->get()
        ->map(function ($history) {

            $history->image_url = $this->assetUrl($history->featured_image);

            $history->card_excerpt = $history->excerpt
                ?: \Illuminate\Support\Str::limit(strip_tags($history->content), 120);

            return $history;
        });
}
	
	public function galleriesPage(): array
{
    $items = $this->galleries(100)->values();

    $featured = $items->first(function ($gallery) {
        return (bool) ($gallery->is_featured ?? false);
    });

    if (! $featured && $items->isNotEmpty()) {
        $featured = $items->first();
    }

    $gridItems = $featured
        ? $items->reject(fn ($gallery) => $gallery->id === $featured->id)->values()
        : $items;

    $totalImages = $items->sum(function ($gallery) {
        return (int) ($gallery->images_count ?? 0);
    });

    $featuredCount = $items->where('is_featured', true)->count();

    return [
        'items' => $items,
        'featured' => $featured,
        'gridItems' => $gridItems,
        'totalCount' => $items->count(),
        'totalImages' => $totalImages,
        'featuredCount' => $featuredCount,
    ];
}
	
	public function leadershipPage(): array
{
    $items = $this->leadership(100)->values();

    // FEATURED (top priority already sorted by position display_order)
    $featured = $items->first();

    // GRID (exclude featured)
    $gridItems = $featured
        ? $items->reject(fn ($item) => $item->id === $featured->id)->values()
        : $items;

    // COUNTS
    $totalCount = $items->count();

    $positionCount = $items
        ->pluck('position.name')
        ->filter()
        ->unique()
        ->count();

    $kandaCount = $items
        ->filter(fn ($item) => $item->kanda)
        ->count();

    $jumuiyaCount = $items
        ->filter(fn ($item) => $item->jumuiya)
        ->count();

    $groupCount = $items
        ->filter(fn ($item) => $item->apostolicGroup)
        ->count();

    // DECORATE EACH ITEM (VERY IMPORTANT FOR FRONTEND CLEANNESS)
    $items = $items->map(function ($item) {

        // Determine scope
        if ($item->apostolicGroup) {
            $item->scope = 'group';
            $item->scope_label = optional($item->apostolicGroup)->name;
        } elseif ($item->jumuiya) {
            $item->scope = 'jumuiya';
            $item->scope_label = optional($item->jumuiya)->name;
        } elseif ($item->kanda) {
            $item->scope = 'kanda';
            $item->scope_label = optional($item->kanda)->name;
        } else {
            $item->scope = 'parish';
            $item->scope_label = 'Parish';
        }

        // Member name (safe)
        $item->member_name = optional($item->member)->full_name
            ?? optional($item->member)->name
            ?? 'Leader';

        // Position
        $item->position_name = optional($item->position)->name ?? 'Leader';

        return $item;
    });

    return [
        'items' => $items,
        'featured' => $featured,
        'gridItems' => $gridItems,
        'totalCount' => $totalCount,
        'positionCount' => $positionCount,
        'kandaCount' => $kandaCount,
        'jumuiyaCount' => $jumuiyaCount,
        'groupCount' => $groupCount,
    ];
}
	
	public function massesPage(): array
{
    $items = $this->massSchedules(100)->values();

    $featured = $items->first();

    $todayItems = $items
        ->filter(function ($mass) {
            return optional($mass->scheduled_at)->isToday();
        })
        ->values();

    $weekItems = $items
        ->filter(function ($mass) {
            return optional($mass->scheduled_at)->isBetween(now()->startOfWeek(), now()->endOfWeek());
        })
        ->values();

    $upcomingItems = $featured
        ? $items->reject(fn ($mass) => $mass->id === $featured->id)->values()
        : $items;

    $typeCount = $items
        ->map(fn ($mass) => optional($mass->massType)->name)
        ->filter()
        ->unique()
        ->count();

    $thisWeekCount = $weekItems->count();

    $nextMassDateLabel = $featured && $featured->scheduled_at
        ? $featured->scheduled_at->format('M d, Y')
        : null;

    return [
        'items' => $items,
        'featured' => $featured,
        'todayItems' => $todayItems,
        'weekItems' => $weekItems,
        'upcomingItems' => $upcomingItems,
        'totalCount' => $items->count(),
        'typeCount' => $typeCount,
        'thisWeekCount' => $thisWeekCount,
        'nextMassDateLabel' => $nextMassDateLabel,
    ];
}
	
	public function kandasPage(): array
{
    $items = $this->kandas(50);

    $totalCount = $items->count();

    $totalJumuiyas = $items->sum('jumuiyas_count');

    $totalFamilies = $items->sum('familias_count');

    $totalMembers = $items->sum('members_count');

    $featured = $items->sortByDesc('members_count')->first();

    $gridItems = $items
        ->filter(fn ($item) => $featured ? $item->id !== $featured->id : true)
        ->values();

    return [
        'items' => $items,
        'featured' => $featured,
        'gridItems' => $gridItems,
        'totalCount' => $totalCount,
        'totalJumuiyas' => $totalJumuiyas,
        'totalFamilies' => $totalFamilies,
        'totalMembers' => $totalMembers,
    ];
}
	
	public function projectsPage(): array
{
    $items = $this->projects(100)->values();

    $featured = $items->first(function (Project $project) {
        return $project->status === 'active';
    });

    if (! $featured && $items->isNotEmpty()) {
        $featured = $items->first();
    }

    $gridItems = $featured
        ? $items->reject(fn (Project $project) => $project->id === $featured->id)->values()
        : $items;

    $activeCount = $items->where('status', 'active')->count();
    $completedCount = $items->where('status', 'completed')->count();

    $totalRaised = (float) $items->sum(function (Project $project) {
        return (float) ($project->progress_amount ?? 0);
    });

    $totalGoal = (float) $items->sum(function (Project $project) {
        return (float) ($project->goal_amount ?? 0);
    });

    return [
        'items' => $items,
        'featured' => $featured,
        'gridItems' => $gridItems,
        'totalCount' => $items->count(),
        'activeCount' => $activeCount,
        'completedCount' => $completedCount,
        'totalRaised' => $totalRaised,
        'totalGoal' => $totalGoal,
    ];
}

public function givingPage(): array
{
    $bankAccounts = Schema::hasTable('bank_accounts')
        ? BankAccount::query()
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get()
        : collect();

    $contributionTypes = Schema::hasTable('contribution_types')
        ? ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
        : collect();

    return [
        'bankAccounts' => $bankAccounts,
        'contributionTypes' => $contributionTypes,
        'bankCount' => $bankAccounts->count(),
        'typeCount' => $contributionTypes->count(),
        'installmentTypeCount' => $contributionTypes->where('has_installments', true)->count(),
    ];
}
    public function shared(): array
    {
        $settings = $this->settings();

        return [
            'church' => Schema::hasTable('centre_details')
                ? CentreDetail::query()->where('is_active', true)->first()
                : null,
            'locale' => app()->getLocale(),
            'settings' => $settings,
            'navItems' => $this->navItems($settings),
            'menuPages' => $this->menuPages(),
            'footerPages' => $this->footerPages(),
        ];
    }

    public function home(): array
    {
        return [
            'banners' => $this->heroBanners(),
            'sections' => $this->pageSections('home'),
            'announcements' => $this->announcements(15, true),
            'masses' => $this->massSchedules(4),
            'projects' => $this->projects(14),
            'ministries' => $this->ministries(6),
            'leadership' => $this->leadership(6),
            'kandas' => $this->kandas(6),
            'jumuiyas' => $this->jumuiyas(8),
            'galleries' => $this->galleries(6),
        ];
    }

    private function announcementBadge(Announcement $announcement): string
    {
        if ($announcement->is_featured) {
            return 'FEATURED';
        }

        if ($announcement->publish_from && $announcement->publish_from->greaterThanOrEqualTo(now()->subDays(7))) {
            return 'NEW';
        }

        return 'UPDATE';
    }

    public function pageBySlug(string $slug): ?Page
    {
        if (! Schema::hasTable('pages')) {
            return null;
        }

        return Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }

    public function announcements(int $limit = 12, bool $homepageOnly = false): Collection
    {
        if (! Schema::hasTable('announcements')) {
            return collect();
        }

        $now = now();

        $query = Announcement::query()
            ->where('is_published', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_from')->orWhere('publish_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_until')->orWhere('publish_until', '>=', $now);
            });

        if ($homepageOnly && Schema::hasColumn('announcements', 'show_on_homepage')) {
            $query->where('show_on_homepage', true);
        }

        return $query
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc('publish_from')
            ->limit($limit)
            ->get()
            ->map(function (Announcement $announcement) {
                return $this->decorateAnnouncement($announcement);
            });
    }

    public function announcementsPage(): array
    {
        $items = $this->announcements(100, false)->values();

        $featured = $items->first(fn (Announcement $announcement) => (bool) $announcement->is_featured);

        if (! $featured && $items->isNotEmpty()) {
            $featured = $items->first();
        }

        $gridItems = $featured
            ? $items->reject(fn (Announcement $announcement) => $announcement->id === $featured->id)->values()
            : $items;

        $recentItems = $items
            ->sortByDesc(function (Announcement $announcement) {
                return optional($announcement->publish_from)->timestamp ?? optional($announcement->created_at)->timestamp ?? 0;
            })
            ->take(4)
            ->values();

        return [
            'items' => $items,
            'featured' => $featured,
            'gridItems' => $gridItems,
            'recentItems' => $recentItems,
            'totalCount' => $items->count(),
            'featuredCount' => $items->where('is_featured', true)->count(),
        ];
    }

    public function announcementBySlug(string $slug): ?Announcement
    {
        if (! Schema::hasTable('announcements')) {
            return null;
        }

        $announcement = Announcement::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        return $announcement ? $this->decorateAnnouncement($announcement) : null;
    }

    public function relatedAnnouncements(?Announcement $current = null, int $limit = 3): Collection
    {
        if (! Schema::hasTable('announcements')) {
            return collect();
        }

        $now = now();

        $query = Announcement::query()
            ->where('is_published', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_from')->orWhere('publish_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_until')->orWhere('publish_until', '>=', $now);
            });

        if ($current) {
            $query->where('id', '!=', $current->id);
        }

        return $query
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc('publish_from')
            ->limit($limit)
            ->get()
            ->map(function (Announcement $announcement) {
                return $this->decorateAnnouncement($announcement);
            });
    }

    private function decorateAnnouncement(Announcement $announcement): Announcement
    {
        $announcement->image_url = $this->assetUrl($announcement->image);
        $announcement->card_badge = $this->announcementBadge($announcement);
        $announcement->card_date = optional($announcement->publish_from)->format('M d');
        $announcement->card_date_full = optional($announcement->publish_from)->format('M d, Y');
        $announcement->card_summary = $announcement->summary
            ?: \Illuminate\Support\Str::limit(strip_tags((string) $announcement->content), 120);
        $announcement->reading_title = $announcement->title;
        $announcement->hero_image_url = $announcement->image_url ?: asset('uploads/frontend/announcements/ann6.jpeg');

        return $announcement;
    }

    public function kandas(int $limit = 20): Collection
    {
        if (! Schema::hasTable('kandas')) {
            return collect();
        }

        return Kanda::query()
            ->where('is_active', true)
            ->with([
                'jumuiyas' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with([
                        'familias' => fn ($familiaQuery) => $familiaQuery
                            ->where('is_active', true)
                            ->withCount([
                                'members as active_members_count' => fn ($memberQuery) => $memberQuery->where('is_active', true),
                            ])
                            ->orderBy('name'),
                    ])
                    ->orderBy('name'),
            ])
            ->withCount([
                'jumuiyas' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (Kanda $kanda) {
                $familiesCount = $kanda->jumuiyas->sum(function ($jumuiya) {
                    return $jumuiya->familias->count();
                });

                $membersCount = $kanda->jumuiyas->sum(function ($jumuiya) {
                    return $jumuiya->familias->sum('active_members_count');
                });

                $firstJumuiya = $kanda->jumuiyas->first();

                $kanda->image_url = $this->assetUrl($kanda->image);
                $kanda->highlight_label = $firstJumuiya?->name ?: 'Community';
                $kanda->familias_count = $familiesCount;
                $kanda->members_count = $membersCount;

                $kanda->short_comment = $kanda->comment
                    ? \Illuminate\Support\Str::limit(strip_tags($kanda->comment), 130)
                    : 'A vibrant local faith community where worship, fellowship, and service grow together.';

                return $kanda;
            });
    }
public function jumuiyas(int $limit = 50): Collection
{
    if (! Schema::hasTable('jumuiyas')) {
        return collect();
    }

    return Jumuiya::query()
        ->where('is_active', true)
        ->with('kanda')
        ->with([
            'familias' => fn ($query) => $query
                ->where('is_active', true)
                ->withCount([
                    'members as active_members_count' => fn ($memberQuery) => $memberQuery->where('is_active', true),
                ])
        ])
        ->withCount([
            'familias' => fn ($query) => $query->where('is_active', true),
        ])
        ->orderBy('name')
        ->limit($limit)
        ->get()
        ->map(function ($jumuiya) {

            $membersCount = $jumuiya->familias->sum('active_members_count');

            $jumuiya->members_count = $membersCount;

            $jumuiya->kanda_name = optional($jumuiya->kanda)->name;

            $jumuiya->short_description = $jumuiya->description
                ? \Illuminate\Support\Str::limit(strip_tags($jumuiya->description), 120)
                : 'A local faith community where families grow together in prayer, fellowship, and service.';

            return $jumuiya;
        });
}
public function jumuiyasPage(): array
{
    $items = $this->jumuiyas(80);

    $totalCount = $items->count();

    $totalFamilies = $items->sum('familias_count');

    $totalMembers = $items->sum('members_count');

    $totalKandas = $items->pluck('kanda_name')->unique()->count();

    $featured = $items->sortByDesc('members_count')->first();

    $gridItems = $items
        ->filter(fn ($item) => $featured ? $item->id !== $featured->id : true)
        ->values();

    return [
        'items' => $items,
        'featured' => $featured,
        'gridItems' => $gridItems,
        'totalCount' => $totalCount,
        'totalFamilies' => $totalFamilies,
        'totalMembers' => $totalMembers,
        'totalKandas' => $totalKandas,
    ];
}

    public function massSchedules(int $limit = 20): Collection
    {
        if (! Schema::hasTable('mass_schedules')) {
            return collect();
        }

        return MassSchedule::query()
            ->with('massType')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now()->subDays(1))
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    public function projects(int $limit = 12): Collection
    {
        if (! Schema::hasTable('projects')) {
            return collect();
        }

        return Project::query()
            ->with([
                'category',
                'transactions' => fn ($query) => $query
                    ->where('status', 'approved')
                    ->orderByDesc('transaction_date'),
            ])
            ->where('is_active', true)
            ->whereIn('status', ['planned', 'active', 'on_hold', 'completed'])
            ->orderByRaw("
                case
                    when status = 'active' then 0
                    when status = 'planned' then 1
                    when status = 'on_hold' then 2
                    when status = 'completed' then 3
                    else 4
                end
            ")
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (Project $project) {
                $income = $project->transactions
                    ->where('transaction_type', 'income')
                    ->sum('amount');

                $expense = $project->transactions
                    ->where('transaction_type', 'expense')
                    ->sum('amount');

                $target = (float) ($project->target_amount ?: $project->budget_amount ?: 0);

                $project->cover_image_url = $this->assetUrl($project->cover_image);
                $project->category_name = optional($project->category)->name ?: 'Project';
                $project->progress_amount = (float) $income;
                $project->expense_amount = (float) $expense;
                $project->goal_amount = $target;
                $project->progress_percent = $target > 0
                    ? min(100, round(($income / $target) * 100))
                    : 0;

                $project->status_badge = match ($project->status) {
                    'active' => 'Active',
                    'planned' => 'Planned',
                    'on_hold' => 'On Hold',
                    'completed' => 'Completed',
                    default => 'Project',
                };

                $project->short_description = $project->description
                    ? \Illuminate\Support\Str::limit(strip_tags($project->description), 140)
                    : 'Support this church project and help us create lasting community impact.';

                return $project;
            });
    }

    public function ministries(int $limit = 12): Collection
    {
        if (! Schema::hasTable('apostolic_groups')) {
            return collect();
        }

        return ApostolicGroup::query()
            ->where('is_active', true)
            ->with(['leader', 'assistantLeader', 'patron'])
            ->withCount('groupMembers')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function leadership(int $limit = 12): Collection
    {
        if (! Schema::hasTable('leadership_assignments')) {
            return collect();
        }

        return LeadershipAssignment::query()
            ->with(['member', 'position', 'kanda', 'jumuiya', 'apostolicGroup'])
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ended_at')->orWhere('ended_at', '>=', now()->toDateString());
            })
            ->join('leadership_positions', 'leadership_positions.id', '=', 'leadership_assignments.leadership_position_id')
            ->select('leadership_assignments.*')
            ->orderBy('leadership_positions.display_order')
            ->orderByDesc('leadership_assignments.started_at')
            ->limit($limit)
            ->get();
    }

    public function galleries(int $limit = 9): Collection
    {
        if (! Schema::hasTable('galleries')) {
            return collect();
        }

        return Gallery::query()
            ->with([
                'images' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('display_order'),
            ])
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc('event_date')
            ->limit($limit)
            ->get()
            ->map(function (Gallery $gallery) {
                $firstImage = $gallery->images->first();

                $gallery->cover_image_url = $gallery->cover_image
                    ? $this->assetUrl($gallery->cover_image)
                    : ($firstImage ? $this->assetUrl($firstImage->image_path) : null);

                $gallery->image_alt = $firstImage?->alt_text ?: $gallery->title;
                $gallery->gallery_chip = $gallery->is_featured ? 'Featured' : 'Gallery';
                $gallery->event_label = $gallery->event_date
                    ? $gallery->event_date->format('M d, Y')
                    : 'Moments of Faith';

                $gallery->short_description = $gallery->description
                    ? \Illuminate\Support\Str::limit(strip_tags($gallery->description), 110)
                    : 'Captured moments of worship, fellowship, and grace in our church community.';

                $gallery->images_count = $gallery->images->count();

                return $gallery;
            });
    }

    public function giving(): array
    {
        return [
            'bankAccounts' => Schema::hasTable('bank_accounts')
                ? BankAccount::query()->where('is_active', true)->orderBy('bank_name')->get()
                : collect(),
            'contributionTypes' => Schema::hasTable('contribution_types')
                ? ContributionType::query()->where('is_active', true)->orderBy('name')->get()
                : collect(),
        ];
    }

    public function heroBanners(): Collection
    {
        if (! Schema::hasTable('hero_banners')) {
            return collect();
        }

        $now = now();

        return HeroBanner::query()
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('display_order')
            ->get()
            ->map(function (HeroBanner $banner) {
                $banner->background_url = $this->assetUrl($banner->background_value);
                $banner->poster_url = $this->assetUrl($banner->poster_image);
                $banner->is_video = ($banner->background_type ?? 'image') === 'video';
                $banner->is_image = ($banner->background_type ?? 'image') === 'image';

                return $banner;
            });
    }

    private function assetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    public function pageSections(string $pageKey): Collection
    {
        if (! Schema::hasTable('page_sections')) {
            return collect();
        }

        return PageSection::query()
            ->where('page_key', $pageKey)
            ->where('is_enabled', true)
            ->orderBy('display_order')
            ->get()
            ->keyBy('section_key');
    }

    public function settings(): array
    {
        $defaults = [
            'site.name' => 'ECCLESIA',
            'site.tagline' => 'Contemporary Worship Community',
            'site.logo' => '/uploads/frontend/logo/logo2.jpeg',
            'site.favicon' => '/uploads/frontend/logo/logo2.jpeg',
            'site.description' => 'A welcoming spiritual home for worship, fellowship, discipleship, and service.',
            'site.og_image' => '/uploads/frontend/logo/logo2.jpeg',

            'hero_badge' => 'PAROKIA YA EPIPHANIA',
            'hero.enable_video' => '1',
            'hero.video_url' => '/uploads/frontend/hero/hero-video.mp4',
            'hero.poster_url' => '/uploads/frontend/hero/hero-poster.jpg',
            'hero.image_url' => '/uploads/frontend/hero/hero-main.jpg',
            'hero.mobile_image_url' => '/uploads/frontend/hero/hero-main-mobile.jpg',
            'hero.overlay_opacity' => '0.42',
            'hero.cta_primary_text' => 'Ratiba ya Misa',
            'hero.cta_primary_link' => '/misa',
            'hero.cta_secondary_text' => 'Changia',
            'hero.cta_secondary_link' => '/changia',

            'homepage.show_announcements' => '1',
            'homepage.show_masses' => '1',
            'homepage.show_projects' => '1',
            'homepage.show_ministries' => '1',
            'homepage.show_leadership' => '1',
            'homepage.show_gallery' => '1',
            'homepage.show_kandas' => '1',
            'homepage.show_jumuiyas' => '1',
            'homepage.show_contact' => '1',

            'nav.show_home' => '1',
            'nav.show_kanda' => '1',
            'nav.show_jumuiya' => '1',
            'nav.show_masses' => '1',
            'nav.show_announcements' => '1',
            'nav.show_projects' => '1',
            'nav.show_ministries' => '1',
            'nav.show_leadership' => '1',
            'nav.show_gallery' => '1',
            'nav.show_history' => '1',
            'nav.show_giving' => '1',
            'nav.show_contact' => '1',
            'nav.show_login' => '1',

            'nav.contact_label' => 'Contact',
            'nav.give_label' => 'Give',
            'nav.login_label' => 'Login',
            'nav.mobile_give_label' => 'Donate',

            'theme.primary' => '#6d28d9',
            'theme.secondary' => '#0ea5e9',
            'theme.accent' => '#f59e0b',
            'theme.dark' => '#081225',

            'footer.tagline' => 'Karibu kwenye nyumba ya kidijitali ya parokia yetu.',
            'footer.description' => 'A welcoming spiritual home for worship, fellowship, discipleship, and service. Rooted in grace, reaching families, youth, and the wider community.',
            'footer.facebook_url' => '',
            'footer.instagram_url' => '',
            'footer.youtube_url' => '',
            'footer.tiktok_url' => '',
            'footer.newsletter_text' => 'Receive weekly encouragement, church updates, and upcoming events in your inbox.',
            'footer.newsletter_note' => 'No spam. Just inspiration and updates.',
            'footer.bottom_note' => 'Designed with <i class="bi bi-heart-fill"></i> for modern worship',
        ];

        if (! Schema::hasTable('site_settings')) {
            return $defaults;
        }

        $stored = SiteSetting::query()
            ->where('is_public', true)
            ->get()
            ->mapWithKeys(fn ($item) => [$item->setting_key => $item->setting_value])
            ->toArray();

        return array_merge($defaults, $stored);
    }

    private function menuPages(): Collection
    {
        if (! Schema::hasTable('pages')) {
            return collect();
        }

        return Page::query()
            ->where('is_published', true)
            ->where('show_in_menu', true)
            ->orderBy('title')
            ->get();
    }

    private function footerPages(): Collection
    {
        if (! Schema::hasTable('pages')) {
            return collect();
        }

        return Page::query()
            ->where('is_published', true)
            ->where('show_in_footer', true)
            ->orderBy('title')
            ->get();
    }

    private function navItems(array $settings): array
    {
        return [
            [
                'label' => db_trans('nav.home') ?: 'Home',
                'route' => 'frontend.home',
                'show' => ($settings['nav.show_home'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.kanda') ?: 'Kanda',
                'route' => 'frontend.kandas',
                'show' => ($settings['nav.show_kanda'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.jumuiya') ?: 'Jumuiya',
                'route' => 'frontend.jumuiyas',
                'show' => ($settings['nav.show_jumuiya'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.masses') ?: 'Mass Schedule',
                'route' => 'frontend.masses',
                'show' => ($settings['nav.show_masses'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.announcements') ?: 'Announcements',
                'route' => 'frontend.announcements',
                'show' => ($settings['nav.show_announcements'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.projects') ?: 'Projects',
                'route' => 'frontend.projects',
                'show' => ($settings['nav.show_projects'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.ministries') ?: 'Ministries',
                'route' => 'frontend.ministries',
                'show' => ($settings['nav.show_ministries'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.leadership') ?: 'Leadership',
                'route' => 'frontend.leadership',
                'show' => ($settings['nav.show_leadership'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.gallery') ?: 'Gallery',
                'route' => 'frontend.gallery',
                'show' => ($settings['nav.show_gallery'] ?? '1') === '1',
            ],
            [
                'label' => db_trans('nav.history') ?: 'History',
                'route' => 'frontend.history',
                'show' => ($settings['nav.show_history'] ?? '1') === '1',
            ],
        ];
    }
}