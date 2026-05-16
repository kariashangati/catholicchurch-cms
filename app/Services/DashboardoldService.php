<?php

namespace App\Services;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\CentreDetail;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\LeadershipAssignment;
use App\Models\MassSchedule;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\Tithe;
use App\Models\User;
use App\Models\VisitorLog;
use App\Services\Access\UserScopeResolver;
use App\Support\AdminScope;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
    ) {
    }

    public function getDataFor(User $user): array
    {
        $scope = $this->resolveScope($user);

        if ($scope['type'] === AdminScope::TYPE_INVALID) {
            throw new AuthorizationException($scope['reason'] ?? 'This account has an invalid dashboard scope.');
        }

        $cacheKey = sprintf(
            'dashboard.v3.%s.%s.%s',
            $user->id,
            $scope['type'],
            $scope['scope_id'] ?? 'global'
        );

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($scope, $user) {
            $memberGrowthChart = $this->getMemberGrowthChart($scope);
            $financeTrendChart = $this->getFinanceTrendChart($scope);
            $visitorTrendChart = $this->getVisitorTrendChart();
            $sacramentDistributionChart = $this->getSacramentDistributionChart($scope);
            $financeMixChart = $this->getFinanceMixChart($scope);

            return [
                'centre' => Schema::hasTable('centre_details') ? CentreDetail::query()->first() : null,
                'scope' => $scope,
                'page' => $this->getPageMeta($scope, $user),
                'hero' => $this->getHero($scope, $user),

                'summaryCards' => $this->getSummaryCards($scope, $user),
                'financeCards' => $this->getFinanceCards($scope, $user),
                'visitorCards' => $this->getVisitorCards(),
                'genderCards' => $this->getGenderCards($scope),

                'memberGrowthChart' => $memberGrowthChart,
                'financeTrendChart' => $financeTrendChart,
                'visitorTrendChart' => $visitorTrendChart,
                'sacramentDistributionChart' => $sacramentDistributionChart,
                'financeMixChart' => $financeMixChart,

                // aliases for easy blade usage
                'memberChart' => $memberGrowthChart,
                'financeChart' => $financeTrendChart,
                'visitorChart' => $visitorTrendChart,
                'sacramentChart' => $sacramentDistributionChart,
                'mixChart' => $financeMixChart,

                'recentMembers' => $this->getRecentMembers($scope),
                'upcomingSchedules' => $this->getUpcomingSchedules(),
                'liveFeed' => $this->getLiveFeed($scope),
                'alerts' => $this->getAlerts($scope, $user),
                'quickLinks' => $this->getQuickLinks($user),
                'kandaPerformance' => $this->getKandaPerformance($scope),
                'chartLinks' => $this->getChartLinks($scope, $user),
            ];
        });
    }

    protected function resolveScope(User $user): array
    {
        $resolved = $this->scopeResolver->resolve($user);

        return match ($resolved->type) {
            AdminScope::TYPE_GLOBAL => [
                'type' => AdminScope::TYPE_GLOBAL,
                'scope_id' => null,
                'label' => db_trans('parish_overview'),
                'reason' => null,
            ],

            AdminScope::TYPE_KANDA => [
                'type' => AdminScope::TYPE_KANDA,
                'scope_id' => $resolved->kandaId,
                'label' => $resolved->label ?: db_trans('kanda_overview'),
                'reason' => null,
            ],

            AdminScope::TYPE_JUMUIYA => [
                'type' => AdminScope::TYPE_JUMUIYA,
                'scope_id' => $resolved->jumuiyaId,
                'label' => $resolved->label ?: db_trans('jumuiya_overview'),
                'reason' => null,
            ],

            default => [
                'type' => AdminScope::TYPE_INVALID,
                'scope_id' => null,
                'label' => db_trans('invalid_scope'),
                'reason' => $resolved->reason,
            ],
        };
    }

    protected function getPageMeta(array $scope, User $user): array
    {
        return [
            'title' => db_trans('dashboard'),
            'subtitle' => db_trans('dashboard_answers_key_questions'),
            'scope_label' => $scope['label'],
            'user_name' => $user->name,
            'today' => now()->translatedFormat('l, d M Y'),
        ];
    }

    protected function getHero(array $scope, User $user): array
    {
        $pending = 0;

        if (Schema::hasTable('offerings')) {
            $pending += $this->scopedOfferingsQuery($scope)->where('status', Offering::STATUS_PENDING)->count();
        }

        if (Schema::hasTable('cash_contributions')) {
            $pending += $this->scopedCashContributionsQuery($scope)->where('status', CashContribution::STATUS_PENDING)->count();
        }

        if (Schema::hasTable('tithes')) {
            $pending += $this->scopedTitheQuery($scope)->where('status', Tithe::STATUS_PENDING)->count();
        }

        if (Schema::hasTable('bank_contributions') && defined(BankContribution::class . '::STATUS_PENDING')) {
            $pending += $this->scopedBankContributionsQuery($scope)->where('status', BankContribution::STATUS_PENDING)->count();
        }

        return [
            'eyebrow' => db_trans('admin_overview'),
            'title' => db_trans('welcome_back') . ', ' . $user->name,
            'subtitle' => db_trans('dashboard_hero_description'),
            'pending_items' => $pending,
            'scope_badge' => $scope['label'],
        ];
    }

    protected function getSummaryCards(array $scope, User $user): array
    {
        $membersQuery = $this->scopedMembersQuery($scope);
        $familiasQuery = $this->scopedFamiliasQuery($scope);
        $jumuiyaQuery = $this->scopedJumuiyaQuery($scope);

        return [
            $this->withLink([
                'title' => db_trans('members_in_scope'),
                'value' => number_format((clone $membersQuery)->count()),
                'meta' => db_trans('all_registered_members'),
                'icon' => 'fas fa-users',
                'tone' => 'primary',
            ], 'members.index', [], 'members.view', $user, db_trans('open_members')),

            $this->withLink([
                'title' => db_trans('new_members_this_month'),
                'value' => number_format(
                    (clone $membersQuery)
                        ->whereYear('members.created_at', now()->year)
                        ->whereMonth('members.created_at', now()->month)
                        ->count()
                ),
                'meta' => db_trans('fresh_growth_signal'),
                'icon' => 'fas fa-user-plus',
                'tone' => 'success',
            ], 'members.index', [], 'members.view', $user, db_trans('view_new_members')),

            $this->withLink([
                'title' => db_trans('familias'),
                'value' => number_format((clone $familiasQuery)->count()),
                'meta' => db_trans('households_under_care'),
                'icon' => 'fas fa-home',
                'tone' => 'info',
            ], 'familias.index', [], 'familias.view', $user, db_trans('open_familias')),

            $this->withLink([
                'title' => db_trans('jumuiyas'),
                'value' => number_format((clone $jumuiyaQuery)->count()),
                'meta' => db_trans('communities_in_view'),
                'icon' => 'fas fa-layer-group',
                'tone' => 'secondary',
            ], 'jumuiyas.index', [], 'jumuiyas.view', $user, db_trans('open_jumuiyas')),

            $this->withLink([
                'title' => db_trans('active_users'),
                'value' => number_format(
                    Schema::hasColumn('users', 'is_active')
                        ? User::query()->where('is_active', true)->count()
                        : User::query()->count()
                ),
                'meta' => db_trans('system_users_with_access'),
                'icon' => 'fas fa-user-shield',
                'tone' => 'warning',
            ], 'system-access.users.index', [], 'access.users.view', $user, db_trans('open_users')),

            $this->withLink([
                'title' => db_trans('kandas'),
                'value' => number_format($this->getScopedKandaCount($scope)),
                'meta' => db_trans('administrative_coverage'),
                'icon' => 'fas fa-map-marked-alt',
                'tone' => 'dark',
            ], 'kandas.index', [], 'kandas.view', $user, db_trans('open_kandas')),
        ];
    }

    protected function getGenderCards(array $scope): array
    {
        $membersQuery = $this->scopedMembersQuery($scope);

        return [
            [
                'title' => db_trans('male_members'),
                'value' => number_format((clone $membersQuery)->where('members.gender', 'Male')->count()),
                'icon' => 'fas fa-male',
                'tone' => 'info',
            ],
            [
                'title' => db_trans('female_members'),
                'value' => number_format((clone $membersQuery)->where('members.gender', 'Female')->count()),
                'icon' => 'fas fa-female',
                'tone' => 'success',
            ],
        ];
    }

    protected function getFinanceCards(array $scope, User $user): array
    {
        $month = now()->month;
        $year = now()->year;

        $offeringsBase = Schema::hasTable('offerings') ? $this->scopedOfferingsQuery($scope) : null;
        $tithesBase = Schema::hasTable('tithes') ? $this->scopedTitheQuery($scope) : null;
        $cashBase = Schema::hasTable('cash_contributions') ? $this->scopedCashContributionsQuery($scope) : null;
        $bankBase = Schema::hasTable('bank_contributions') ? $this->scopedBankContributionsQuery($scope) : null;

        $bankPendingStatus = defined(BankContribution::class . '::STATUS_PENDING')
            ? BankContribution::STATUS_PENDING
            : 'pending';

        return [
            $this->withLink([
                'title' => db_trans('offerings_this_month'),
                'value' => $this->money(
                    $offeringsBase
                        ? (clone $offeringsBase)->whereYear('collection_date', $year)->whereMonth('collection_date', $month)->sum('amount')
                        : 0
                ),
                'meta' => db_trans('approved_and_pending_offerings'),
                'icon' => 'fas fa-hand-holding-heart',
                'tone' => 'primary',
            ], 'finance.offerings.index', [], 'finance.offerings.view', $user, db_trans('open_offerings')),

            $this->withLink([
                'title' => db_trans('tithes_this_month'),
                'value' => $this->money(
                    $tithesBase
                        ? (clone $tithesBase)->whereYear('contribution_date', $year)->whereMonth('contribution_date', $month)->sum('amount')
                        : 0
                ),
                'meta' => db_trans('member_tithe_collections'),
                'icon' => 'fas fa-coins',
                'tone' => 'success',
            ], 'finance.tithes.index', [], 'finance.tithes.view', $user, db_trans('open_tithes')),

            $this->withLink([
                'title' => db_trans('cash_contributions_this_month'),
                'value' => $this->money(
                    $cashBase
                        ? (clone $cashBase)->whereYear('contribution_date', $year)->whereMonth('contribution_date', $month)->sum('amount')
                        : 0
                ),
                'meta' => db_trans('cash_collections_logged'),
                'icon' => 'fas fa-wallet',
                'tone' => 'warning',
            ], 'finance.cash-contributions.index', [], 'finance.cash-contributions.view', $user, db_trans('open_cash_contributions')),

            $this->withLink([
                'title' => db_trans('bank_contributions_this_month'),
                'value' => $this->money(
                    $bankBase
                        ? (clone $bankBase)->whereYear('contribution_date', $year)->whereMonth('contribution_date', $month)->sum('amount')
                        : 0
                ),
                'meta' => db_trans('banked_contributions_logged'),
                'icon' => 'fas fa-university',
                'tone' => 'info',
            ], 'finance.bank-contributions.index', [], 'finance.bank-contributions.view', $user, db_trans('open_bank_contributions')),

            $this->withLink([
                'title' => db_trans('project_income_this_year'),
                'value' => $this->money(
                    Schema::hasTable('project_transactions')
                        ? $this->scopedProjectTransactionsQuery($scope)
                            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
                            ->whereYear('transaction_date', $year)
                            ->sum('amount')
                        : 0
                ),
                'meta' => db_trans('active_project_support'),
                'icon' => 'fas fa-project-diagram',
                'tone' => 'dark',
            ], 'finance.projects.dashboard', [], 'finance.projects.view', $user, db_trans('open_projects')),

            $this->withLink([
                'title' => db_trans('pending_finance_actions'),
                'value' => number_format(
                    ($offeringsBase ? (clone $offeringsBase)->where('status', Offering::STATUS_PENDING)->count() : 0)
                    + ($tithesBase ? (clone $tithesBase)->where('status', Tithe::STATUS_PENDING)->count() : 0)
                    + ($cashBase ? (clone $cashBase)->where('status', CashContribution::STATUS_PENDING)->count() : 0)
                    + ($bankBase ? (clone $bankBase)->where('status', $bankPendingStatus)->count() : 0)
                ),
                'meta' => db_trans('records_needing_review'),
                'icon' => 'fas fa-bell',
                'tone' => 'danger',
            ], 'reports.index', [], 'reports.view', $user, db_trans('review_finance_actions')),
        ];
    }

    protected function getVisitorCards(): array
    {
        if (! Schema::hasTable('visitor_logs')) {
            return [
                'today' => 0,
                'yesterday' => 0,
                'week' => 0,
                'month' => 0,
                'year' => 0,
            ];
        }

        return [
            'today' => VisitorLog::query()->whereDate('visit_date', today())->count(),
            'yesterday' => VisitorLog::query()->whereDate('visit_date', today()->subDay())->count(),
            'week' => VisitorLog::query()->whereBetween('visit_date', [today()->startOfWeek(), today()->endOfWeek()])->count(),
            'month' => VisitorLog::query()->whereYear('visit_date', now()->year)->whereMonth('visit_date', now()->month)->count(),
            'year' => VisitorLog::query()->whereYear('visit_date', now()->year)->count(),
        ];
    }

    protected function getMemberGrowthChart(array $scope): array
    {
        $labels = [];
        $members = [];
        $familias = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->copy()->subMonths($i);

            $labels[] = $date->format('M Y');
            $members[] = (clone $this->scopedMembersQuery($scope))
                ->whereYear('members.created_at', $date->year)
                ->whereMonth('members.created_at', $date->month)
                ->count();

            $familias[] = (clone $this->scopedFamiliasQuery($scope))
                ->whereYear('familias.created_at', $date->year)
                ->whereMonth('familias.created_at', $date->month)
                ->count();
        }

        return compact('labels', 'members', 'familias');
    }

    protected function getFinanceTrendChart(array $scope): array
    {
        $labels = [];
        $offerings = [];
        $tithes = [];
        $contributions = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->copy()->subMonths($i);
            $labels[] = $date->format('M Y');

            $offerings[] = Schema::hasTable('offerings')
                ? (float) (clone $this->scopedOfferingsQuery($scope))
                    ->whereYear('collection_date', $date->year)
                    ->whereMonth('collection_date', $date->month)
                    ->sum('amount')
                : 0;

            $tithes[] = Schema::hasTable('tithes')
                ? (float) (clone $this->scopedTitheQuery($scope))
                    ->whereYear('contribution_date', $date->year)
                    ->whereMonth('contribution_date', $date->month)
                    ->sum('amount')
                : 0;

            $cash = Schema::hasTable('cash_contributions')
                ? (float) (clone $this->scopedCashContributionsQuery($scope))
                    ->whereYear('contribution_date', $date->year)
                    ->whereMonth('contribution_date', $date->month)
                    ->sum('amount')
                : 0;

            $bank = Schema::hasTable('bank_contributions')
                ? (float) (clone $this->scopedBankContributionsQuery($scope))
                    ->whereYear('contribution_date', $date->year)
                    ->whereMonth('contribution_date', $date->month)
                    ->sum('amount')
                : 0;

            $contributions[] = $cash + $bank;
        }

        return compact('labels', 'offerings', 'tithes', 'contributions');
    }

    protected function getVisitorTrendChart(): array
    {
        $labels = [];
        $values = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->copy()->subDays($i);
            $labels[] = $date->format('d M');

            $values[] = Schema::hasTable('visitor_logs')
                ? VisitorLog::query()->whereDate('visit_date', $date->toDateString())->count()
                : 0;
        }

        return compact('labels', 'values');
    }

    protected function getSacramentDistributionChart(array $scope): array
    {
        $query = $this->scopedMembersQuery($scope);

        return [
            'labels' => [
                db_trans('baptized'),
                db_trans('communion'),
                db_trans('confirmation'),
                db_trans('eucharist'),
                db_trans('married'),
            ],
            'values' => [
                (clone $query)->where('members.is_baptized', true)->count(),
                (clone $query)->where('members.has_communion', true)->count(),
                (clone $query)->where('members.has_confirmation', true)->count(),
                (clone $query)->where('members.receives_eucharist', true)->count(),
                (clone $query)->where('members.is_married', true)->count(),
            ],
        ];
    }

    protected function getFinanceMixChart(array $scope): array
    {
        $start = now()->startOfYear();
        $end = now()->endOfYear();

        return [
            'labels' => [
                db_trans('offerings'),
                db_trans('tithes'),
                db_trans('cash_contributions'),
                db_trans('bank_contributions'),
            ],
            'values' => [
                Schema::hasTable('offerings')
                    ? (float) $this->scopedOfferingsQuery($scope)->whereBetween('collection_date', [$start, $end])->sum('amount')
                    : 0,
                Schema::hasTable('tithes')
                    ? (float) $this->scopedTitheQuery($scope)->whereBetween('contribution_date', [$start, $end])->sum('amount')
                    : 0,
                Schema::hasTable('cash_contributions')
                    ? (float) $this->scopedCashContributionsQuery($scope)->whereBetween('contribution_date', [$start, $end])->sum('amount')
                    : 0,
                Schema::hasTable('bank_contributions')
                    ? (float) $this->scopedBankContributionsQuery($scope)->whereBetween('contribution_date', [$start, $end])->sum('amount')
                    : 0,
            ],
        ];
    }

    protected function getRecentMembers(array $scope): Collection
    {
        return $this->scopedMembersQuery($scope)
            ->with(['familia.jumuiya.kanda'])
            ->latest('members.created_at')
            ->limit(6)
            ->get();
    }

    protected function getUpcomingSchedules(): Collection
    {
        if (! Schema::hasTable('mass_schedules')) {
            return collect();
        }

        return MassSchedule::query()
            ->with('massType')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();
    }

    protected function getLiveFeed(array $scope): array
    {
        $feed = collect();

        (clone $this->scopedMembersQuery($scope))
            ->latest('members.created_at')
            ->limit(4)
            ->get(['members.id', 'members.first_name', 'members.middle_name', 'members.last_name', 'members.created_at'])
            ->each(function ($member) use ($feed) {
                $feed->push([
                    'icon' => 'fas fa-user-plus',
                    'tone' => 'primary',
                    'title' => db_trans('new_member_registered'),
                    'text' => trim(collect([$member->first_name, $member->middle_name, $member->last_name])->filter()->implode(' ')),
                    'time' => optional($member->created_at)->diffForHumans(),
                    'sort_key' => optional($member->created_at)?->timestamp ?? 0,
                ]);
            });

        if (Schema::hasTable('offerings')) {
            $this->scopedOfferingsQuery($scope)
                ->latest('created_at')
                ->limit(4)
                ->get(['id', 'amount', 'created_at'])
                ->each(function ($offering) use ($feed) {
                    $feed->push([
                        'icon' => 'fas fa-hand-holding-heart',
                        'tone' => 'success',
                        'title' => db_trans('offering_recorded'),
                        'text' => $this->money($offering->amount),
                        'time' => optional($offering->created_at)->diffForHumans(),
                        'sort_key' => optional($offering->created_at)?->timestamp ?? 0,
                    ]);
                });
        }

        if (Schema::hasTable('tithes')) {
            $this->scopedTitheQuery($scope)
                ->latest('created_at')
                ->limit(3)
                ->get(['id', 'amount', 'created_at'])
                ->each(function ($tithe) use ($feed) {
                    $feed->push([
                        'icon' => 'fas fa-coins',
                        'tone' => 'warning',
                        'title' => db_trans('tithe_recorded'),
                        'text' => $this->money($tithe->amount),
                        'time' => optional($tithe->created_at)->diffForHumans(),
                        'sort_key' => optional($tithe->created_at)?->timestamp ?? 0,
                    ]);
                });
        }

        return $feed
            ->sortByDesc('sort_key')
            ->take(8)
            ->values()
            ->map(function ($item) {
                unset($item['sort_key']);

                return $item;
            })
            ->all();
    }

    protected function getAlerts(array $scope, User $user): array
    {
        $missingSacraments = (clone $this->scopedMembersQuery($scope))
            ->where(function ($query) {
                $query->where('members.is_baptized', false)
                    ->orWhere('members.has_communion', false)
                    ->orWhere('members.has_confirmation', false);
            })
            ->count();

        $membersNoPhone = (clone $this->scopedMembersQuery($scope))
            ->where(function ($query) {
                $query->whereNull('members.phone')
                    ->orWhere('members.phone', '');
            })
            ->count();

        $activeProjects = Schema::hasTable('projects')
            ? Project::query()->where('status', Project::STATUS_ACTIVE)->count()
            : 0;

        $activeLeaders = Schema::hasTable('leadership_assignments')
            ? LeadershipAssignment::query()->where('status', 'active')->count()
            : 0;

        return [
            array_merge([
                'label' => db_trans('members_missing_sacrament_data'),
                'value' => $missingSacraments,
            ], $this->buildLink('members.index', [], 'members.view', $user)),

            array_merge([
                'label' => db_trans('members_without_phone'),
                'value' => $membersNoPhone,
            ], $this->buildLink('members.index', [], 'members.view', $user)),

            array_merge([
                'label' => db_trans('active_projects'),
                'value' => $activeProjects,
            ], $this->buildLink('finance.projects.dashboard', [], 'finance.projects.view', $user)),

            array_merge([
                'label' => db_trans('active_leadership_assignments'),
                'value' => $activeLeaders,
            ], $this->buildLink('leadership.assignments.index', [], 'leadership.assignments.view', $user)),
        ];
    }

    protected function getQuickLinks(User $user): array
    {
        $links = [];

        if ($user->can('members.create') && Route::has('members.create')) {
            $links[] = [
                'label' => db_trans('add_member'),
                'route' => route('members.create'),
                'icon' => 'fas fa-user-plus',
                'can_open' => true,
                'disabled_reason' => null,
            ];
        }

        if ($user->can('finance.create') && Route::has('finance.offerings.index')) {
            $links[] = [
                'label' => db_trans('record_offering'),
                'route' => route('finance.offerings.index'),
                'icon' => 'fas fa-hand-holding-heart',
                'can_open' => true,
                'disabled_reason' => null,
            ];
        }

        if ($user->can('finance.tithes.create') && Route::has('finance.tithes.index')) {
            $links[] = [
                'label' => db_trans('record_tithe'),
                'route' => route('finance.tithes.index'),
                'icon' => 'fas fa-coins',
                'can_open' => true,
                'disabled_reason' => null,
            ];
        }

        if ($user->can('liturgy.mass-schedules.view') && Route::has('liturgy.mass-schedules.index')) {
            $links[] = [
                'label' => db_trans('open_mass_schedule'),
                'route' => route('liturgy.mass-schedules.index'),
                'icon' => 'fas fa-calendar-alt',
                'can_open' => true,
                'disabled_reason' => null,
            ];
        }

        if ($user->can('reports.view') && Route::has('reports.index')) {
            $links[] = [
                'label' => db_trans('open_reports'),
                'route' => route('reports.index'),
                'icon' => 'fas fa-chart-pie',
                'can_open' => true,
                'disabled_reason' => null,
            ];
        }

        if (Route::has('profile.edit')) {
            $links[] = [
                'label' => db_trans('edit_profile'),
                'route' => route('profile.edit'),
                'icon' => 'fas fa-user-cog',
                'can_open' => true,
                'disabled_reason' => null,
            ];
        }

        return $links;
    }

    protected function getKandaPerformance(array $scope): Collection
    {
        $kandas = Kanda::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->whereKey($scope['scope_id']))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, function ($query) use ($scope) {
                $kandaId = Jumuiya::query()->whereKey($scope['scope_id'])->value('kanda_id');

                if ($kandaId) {
                    $query->whereKey($kandaId);
                }
            })
            ->with(['jumuiyas.familias.members'])
            ->orderBy('name')
            ->get();

        return $kandas->map(function (Kanda $kanda) {
            $jumuiyaRows = $kanda->jumuiyas->map(function (Jumuiya $jumuiya) {
                $members = $jumuiya->familias->flatMap->members;

                return [
                    'id' => $jumuiya->id,
                    'name' => $jumuiya->name,
                    'members_count' => $members->count(),
                    'familias_count' => $jumuiya->familias->count(),
                    'baptized_count' => $members->where('is_baptized', true)->count(),
                    'communion_count' => $members->where('has_communion', true)->count(),
                    'confirmation_count' => $members->where('has_confirmation', true)->count(),
                    'married_count' => $members->where('is_married', true)->count(),
                    'eucharist_count' => $members->where('receives_eucharist', true)->count(),
                    'members_url' => Route::has('jumuiyas.show') ? route('jumuiyas.show', $jumuiya) : '#',
                    'report_url' => Route::has('jumuiya-reports.show') ? route('jumuiya-reports.show', $jumuiya) : '#',
                ];
            })->values();

            return [
                'id' => $kanda->id,
                'name' => $kanda->name,
                'jumuiya_count' => $kanda->jumuiyas->count(),
                'members_count' => $jumuiyaRows->sum('members_count'),
                'baptized_count' => $jumuiyaRows->sum('baptized_count'),
                'communion_count' => $jumuiyaRows->sum('communion_count'),
                'confirmation_count' => $jumuiyaRows->sum('confirmation_count'),
                'married_count' => $jumuiyaRows->sum('married_count'),
                'eucharist_count' => $jumuiyaRows->sum('eucharist_count'),
                'details_url' => Route::has('kandas.show') ? route('kandas.show', $kanda) : '#',
                'report_url' => Route::has('kanda-reports.show') ? route('kanda-reports.show', $kanda) : '#',
                'jumuiyas' => $jumuiyaRows,
            ];
        });
    }

    protected function scopedMembersQuery(array $scope): Builder
    {
        return Member::query()
            ->select('members.*')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->where('jumuiyas.kanda_id', $scope['scope_id']))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->where('jumuiyas.id', $scope['scope_id']));
    }

    protected function scopedFamiliasQuery(array $scope): Builder
    {
        return Familia::query()
            ->select('familias.*')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->where('jumuiyas.kanda_id', $scope['scope_id']))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->where('familias.jumuiya_id', $scope['scope_id']));
    }

    protected function scopedJumuiyaQuery(array $scope): Builder
    {
        return Jumuiya::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->where('kanda_id', $scope['scope_id']))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->whereKey($scope['scope_id']));
    }

    protected function scopedOfferingsQuery(array $scope): Builder
    {
        return Offering::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, function ($query) use ($scope) {
                $query->where(function ($builder) use ($scope) {
                    $builder->where('kanda_id', $scope['scope_id'])
                        ->orWhereHas('jumuiya', fn ($sub) => $sub->where('kanda_id', $scope['scope_id']));
                });
            })
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->where('jumuiya_id', $scope['scope_id']));
    }

    protected function scopedTitheQuery(array $scope): Builder
    {
        return Tithe::query()
            ->when(
                $scope['type'] === AdminScope::TYPE_KANDA,
                fn ($query) => $query->whereHas('jumuiya', fn ($sub) => $sub->where('kanda_id', $scope['scope_id']))
            )
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->where('jumuiya_id', $scope['scope_id']));
    }

    protected function scopedCashContributionsQuery(array $scope): Builder
    {
        return CashContribution::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, function ($query) use ($scope) {
                $query->whereHas('member.familia.jumuiya', function ($sub) use ($scope) {
                    $sub->where('kanda_id', $scope['scope_id']);
                });
            })
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, function ($query) use ($scope) {
                $query->whereHas('member.familia', function ($sub) use ($scope) {
                    $sub->where('jumuiya_id', $scope['scope_id']);
                });
            });
    }

    protected function scopedBankContributionsQuery(array $scope): Builder
    {
        return BankContribution::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->where('kanda_id', $scope['scope_id']))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->where('jumuiya_id', $scope['scope_id']));
    }

protected function scopedProjectTransactionsQuery(array $scope): Builder
{
    return ProjectTransaction::query();
}

    protected function getScopedKandaCount(array $scope): int
    {
        if ($scope['type'] === AdminScope::TYPE_GLOBAL) {
            return Kanda::query()->count();
        }

        if ($scope['type'] === AdminScope::TYPE_KANDA) {
            return 1;
        }

        if ($scope['type'] === AdminScope::TYPE_JUMUIYA) {
            $kandaId = Jumuiya::query()->whereKey($scope['scope_id'])->value('kanda_id');

            return $kandaId ? 1 : 0;
        }

        return 0;
    }

    protected function getChartLinks(array $scope, User $user): array
    {
        return [
            'mixChart' => [
                $this->withLink([
                    'label' => db_trans('offerings'),
                ], 'finance.offerings.index', [], 'finance.offerings.view', $user, db_trans('open_offerings')),

                $this->withLink([
                    'label' => db_trans('tithes'),
                ], 'finance.tithes.index', [], 'finance.tithes.view', $user, db_trans('open_tithes')),

                $this->withLink([
                    'label' => db_trans('cash_contributions'),
                ], 'finance.cash-contributions.index', [], 'finance.cash-contributions.view', $user, db_trans('open_cash_contributions')),

                $this->withLink([
                    'label' => db_trans('bank_contributions'),
                ], 'finance.bank-contributions.index', [], 'finance.bank-contributions.view', $user, db_trans('open_bank_contributions')),
            ],
        ];
    }

    // --- Helper methods ---

    protected function buildLink(?string $routeName, array $parameters = [], ?string $permission = null, ?User $user = null): array
    {
        $routeExists = $routeName && Route::has($routeName);
        $canOpen = $routeExists && (! $permission || ($user && $user->can($permission)));

        return [
            'url' => $routeExists ? route($routeName, $parameters) : null,
            'can_open' => $canOpen,
            'disabled_reason' => $canOpen
                ? null
                : db_trans('you_do_not_have_permission_to_view_this_section'),
        ];
    }

    protected function withLink(array $payload, ?string $routeName, array $parameters = [], ?string $permission = null, ?User $user = null, ?string $linkLabel = null): array
    {
        $link = $this->buildLink($routeName, $parameters, $permission, $user);

        return array_merge($payload, $link, [
            'link_label' => $linkLabel ?: db_trans('open'),
        ]);
    }

    protected function scopeQueryParams(array $scope): array
    {
        return match ($scope['type']) {
            AdminScope::TYPE_KANDA => ['scope_type' => 'kanda', 'kanda_id' => $scope['scope_id']],
            AdminScope::TYPE_JUMUIYA => ['scope_type' => 'jumuiya', 'jumuiya_id' => $scope['scope_id']],
            default => [],
        };
    }

    protected function money(float|int|string|null $value): string
    {
        return number_format((float) $value, 2);
    }
}