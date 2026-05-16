<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\CentreDetail;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Offering;
use App\Models\ProjectTransaction;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use App\Support\AdminScope;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    public function __construct(protected UserScopeResolver $scopeResolver)
    {
    }

    public function getDataFor(User $user, ?string $date = null): array
    {
        $scope = $this->resolveScope($user);

        if ($scope['type'] === AdminScope::TYPE_INVALID) {
            throw new AuthorizationException($scope['reason'] ?? 'This account has an invalid dashboard scope.');
        }

        $selectedDate = $date ? Carbon::parse($date)->startOfDay() : null;

        return [
            'centre' => Schema::hasTable('centre_details') ? CentreDetail::query()->first() : null,
            'scope' => $scope,
            'selectedDate' => $selectedDate,
            'page' => $this->getPageMeta($scope, $user, $selectedDate),
            'hero' => $this->getHero($scope, $user, $selectedDate),
            'summaryCards' => $this->getSummaryCards($scope, $user, $selectedDate),
            'financeTrendChart' => $this->getFinanceTrendChart($scope, $selectedDate),
            'financeChart' => $this->getFinanceTrendChart($scope, $selectedDate),
            'kandaDistributionChart' => $this->getKandaDistributionChart($scope),
            'recentActivities' => $this->getRecentActivities($selectedDate),
            'chartLinks' => $this->getChartLinks($user),
        ];
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

    protected function getPageMeta(array $scope, User $user, ?Carbon $selectedDate): array
    {
        return [
            'title' => db_trans('dashboard'),
            'subtitle' => $selectedDate
                ? 'Showing dashboard data for ' . $selectedDate->format('d M Y')
                : 'Simplified command view for finance, members, users, and activity.',
            'scope_label' => $scope['label'],
            'user_name' => $user->name,
            'today' => now()->translatedFormat('l, d M Y'),
        ];
    }

    protected function getHero(array $scope, User $user, ?Carbon $selectedDate): array
    {
        return [
            'eyebrow' => db_trans('admin_overview'),
            'title' => db_trans('welcome_back') . ', ' . $user->name,
            'subtitle' => $selectedDate
                ? 'Filtered date: ' . $selectedDate->format('d M Y')
                : 'Use the date picker to focus the dashboard on one day.',
            'scope_badge' => $scope['label'],
        ];
    }

    protected function getSummaryCards(array $scope, User $user, ?Carbon $selectedDate): array
    {
        $collectionTotal = $this->getCollectionTotal($scope, $selectedDate);

        $membersQuery = $this->scopedMembersQuery($scope);
        if ($selectedDate) {
            $membersQuery->whereDate('members.created_at', $selectedDate->toDateString());
        }

        $usersQuery = User::query();
        if ($selectedDate && Schema::hasColumn('users', 'created_at')) {
            $usersQuery->whereDate('created_at', $selectedDate->toDateString());
        }

        $activityQuery = Schema::hasTable('audit_logs') ? AuditLog::query() : null;
        if ($activityQuery && $selectedDate) {
            $activityQuery->whereDate('occurred_at', $selectedDate->toDateString());
        }

        return [
            $this->withLinkPath([
                'title' => db_trans('collections'),
                'value' => $this->money($collectionTotal),
                'meta' => '',
                'icon' => 'fas fa-coins',
                'tone' => 'primary',
            ], '/finance', 'finance.dashboard', 'finance.view', $user, db_trans('open_finance')),

            $this->withLinkPath([
                'title' => db_trans('members'),
                'value' => number_format((clone $membersQuery)->count()),
                'meta' => '',
                'icon' => 'fas fa-users',
                'tone' => 'success',
            ], '/members', 'members.index', 'members.view', $user, db_trans('open_members')),

            $this->withLinkPath([
                'title' => db_trans('users'),
                'value' => number_format((clone $usersQuery)->count()),
                'meta' => '',
                'icon' => 'fas fa-user-shield',
                'tone' => 'warning',
            ], '/system-access/users', 'system-access.users.index', 'access.users.view', $user, db_trans('open_users')),

            $this->withLinkPath([
                'title' => db_trans('system_activities'),
                'value' => number_format($activityQuery ? (clone $activityQuery)->count() : 0),
                'meta' => '',
                'icon' => 'fas fa-list-check',
                'tone' => 'dark',
            ], '/system/audit', 'system.audit.index', 'audit.view', $user, db_trans('open_audit')),
        ];
    }

    protected function getCollectionTotal(array $scope, ?Carbon $selectedDate): float
    {
        $total = 0;
        $date = $selectedDate?->toDateString();

        if (Schema::hasTable('offerings')) {
            $query = $this->scopedOfferingsQuery($scope);
            $this->whereOptionalDate($query, 'collection_date', $date);
            $total += (float) $query->sum('amount');
        }

        if (Schema::hasTable('tithes')) {
            $query = $this->scopedTitheQuery($scope);
            $this->whereOptionalDate($query, 'contribution_date', $date);
            $total += (float) $query->sum('amount');
        }

        if (Schema::hasTable('cash_contributions')) {
            $query = $this->scopedCashContributionsQuery($scope);
            $this->whereOptionalDate($query, 'contribution_date', $date);
            $total += (float) $query->sum('amount');
        }

        if (Schema::hasTable('bank_contributions')) {
            $query = $this->scopedBankContributionsQuery($scope);
            $this->whereOptionalDate($query, 'contribution_date', $date);
            $total += (float) $query->sum('amount');
        }

        if (Schema::hasTable('project_transactions')) {
            $query = $this->scopedProjectTransactionsQuery($scope)
                ->where('transaction_type', ProjectTransaction::TYPE_INCOME);
            $this->whereOptionalDate($query, 'transaction_date', $date);
            $total += (float) $query->sum('amount');
        }

        return $total;
    }

    protected function getFinanceTrendChart(array $scope, ?Carbon $selectedDate): array
    {
        $labels = [];
        $offerings = [];
        $tithes = [];
        $contributions = [];
        $projects = [];

        $months = $selectedDate ? 1 : 12;

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = ($selectedDate ?: now())->copy()->subMonths($i);
            $labels[] = $selectedDate ? $date->format('d M Y') : $date->format('M Y');

            $offerings[] = Schema::hasTable('offerings')
                ? (float) $this->applyChartDate($this->scopedOfferingsQuery($scope), 'collection_date', $date, $selectedDate)->sum('amount')
                : 0;

            $tithes[] = Schema::hasTable('tithes')
                ? (float) $this->applyChartDate($this->scopedTitheQuery($scope), 'contribution_date', $date, $selectedDate)->sum('amount')
                : 0;

            $cash = Schema::hasTable('cash_contributions')
                ? (float) $this->applyChartDate($this->scopedCashContributionsQuery($scope), 'contribution_date', $date, $selectedDate)->sum('amount')
                : 0;

            $bank = Schema::hasTable('bank_contributions')
                ? (float) $this->applyChartDate($this->scopedBankContributionsQuery($scope), 'contribution_date', $date, $selectedDate)->sum('amount')
                : 0;

            $project = Schema::hasTable('project_transactions')
                ? (float) $this->applyChartDate(
                    $this->scopedProjectTransactionsQuery($scope)->where('transaction_type', ProjectTransaction::TYPE_INCOME),
                    'transaction_date',
                    $date,
                    $selectedDate
                )->sum('amount')
                : 0;

            $contributions[] = $cash + $bank;
            $projects[] = $project;
        }

        return compact('labels', 'offerings', 'tithes', 'contributions', 'projects');
    }

    protected function getKandaDistributionChart(array $scope): array
    {
        $kandas = Kanda::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->whereKey($scope['scope_id']))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, function ($query) use ($scope) {
                $kandaId = Jumuiya::query()->whereKey($scope['scope_id'])->value('kanda_id');
                $query->when($kandaId, fn ($inner) => $inner->whereKey($kandaId));
            })
            ->with(['jumuiyas.familias.members'])
            ->orderBy('name')
            ->get();

        $rows = $kandas->map(function (Kanda $kanda) {
            $familias = $kanda->jumuiyas->flatMap->familias;
            $members = $familias->flatMap->members;

            return [
                'name' => $kanda->name,
                'members' => $members->count(),
                'familias' => $familias->count(),
                'jumuiyas' => $kanda->jumuiyas->count(),
            ];
        })->values();

        return [
            'labels' => $rows->pluck('name')->all(),
            'values' => $rows->pluck('members')->all(),
            'rows' => $rows->all(),
        ];
    }

    protected function getRecentActivities(?Carbon $selectedDate): array
    {
        if (! Schema::hasTable('audit_logs')) {
            return [];
        }

        return AuditLog::query()
            ->with('user')
            ->when($selectedDate, fn ($query) => $query->whereDate('occurred_at', $selectedDate->toDateString()))
            ->latest('occurred_at')
            ->limit(5)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'type' => ucfirst(str_replace('_', ' ', (string) ($log->event ?: $log->module ?: db_trans('activity')))),
                'task' => $log->action ?: $log->description ?: $log->subject_label ?: db_trans('system_activity'),
                'actor' => optional($log->user)->name ?: db_trans('system'),
                'time' => optional($log->occurred_at)->format('d M Y H:i'),
                'module' => $log->module ?: '—',
                'subject' => $log->subject_label ?: '—',
                'description' => $log->description ?: '—',
                'risk_level' => ucfirst((string) ($log->risk_level ?: 'low')),
                'ip_address' => $log->ip_address ?: '—',
                'method' => $log->method ?: '—',
                'route_name' => $log->route_name ?: '—',
                'url' => $log->url ?: '—',
            ])
            ->all();
    }

    protected function getChartLinks(User $user): array
    {
        return [
            'financeChart' => $this->buildLinkPath('/finance', 'finance.dashboard', 'finance.view', $user),
            'kandaDistributionChart' => $this->buildLinkPath('/kandas', 'kandas.index', 'kandas.view', $user),
        ];
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
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->whereHas('jumuiya', fn ($sub) => $sub->where('kanda_id', $scope['scope_id'])))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->where('jumuiya_id', $scope['scope_id']));
    }

    protected function scopedCashContributionsQuery(array $scope): Builder
    {
        return CashContribution::query()
            ->when($scope['type'] === AdminScope::TYPE_KANDA, fn ($query) => $query->whereHas('member.familia.jumuiya', fn ($sub) => $sub->where('kanda_id', $scope['scope_id'])))
            ->when($scope['type'] === AdminScope::TYPE_JUMUIYA, fn ($query) => $query->whereHas('member.familia', fn ($sub) => $sub->where('jumuiya_id', $scope['scope_id'])));
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

    protected function applyChartDate(Builder $query, string $column, Carbon $date, ?Carbon $selectedDate): Builder
    {
        if ($selectedDate) {
            return $query->whereDate($column, $date->toDateString());
        }

        return $query->whereYear($column, $date->year)->whereMonth($column, $date->month);
    }

    protected function whereOptionalDate(Builder $query, string $column, ?string $date): void
    {
        if ($date) {
            $query->whereDate($column, $date);
        }
    }

    protected function buildLinkPath(string $path, ?string $routeName, ?string $permission, User $user): array
    {
        $url = ($routeName && Route::has($routeName)) ? route($routeName) : url($path);
        $canOpen = ! $permission || $user->can($permission);

        return [
            'url' => $url,
            'can_open' => $canOpen,
            'disabled_reason' => $canOpen ? null : db_trans('you_do_not_have_permission_to_view_this_section'),
        ];
    }

    protected function withLinkPath(array $payload, string $path, ?string $routeName, ?string $permission, User $user, ?string $linkLabel = null): array
    {
        return array_merge($payload, $this->buildLinkPath($path, $routeName, $permission, $user), [
            'link_label' => $linkLabel ?: db_trans('open'),
        ]);
    }

    protected function money(float|int|string|null $value): string
    {
        return 'TZS ' . number_format((float) $value, 2);
    }
}
