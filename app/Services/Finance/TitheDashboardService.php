<?php

namespace App\Services\Finance;

use App\Models\CentreDetail;
use App\Models\Jumuiya;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\TitheBatch;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use App\Services\SystemConfig\SiteSettingsService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TitheDashboardService
{
    public function __construct(
        protected TitheAccessService $access,
        protected UserScopeResolver $scopeResolver,
        protected SiteSettingsService $siteSettingsService,
    ) {
    }


    public function getTitheActivityDateExportRows(User $user, string $date, array $filters = []): Collection
{
    $filters = $this->normalizeFilters($user, $filters);

    return $this->titheActivityDateBaseQuery($user, $date, $filters)
        ->select('tithes.*')
        ->with([
            'member.familia.jumuiya.kanda',
            'jumuiya.kanda',
            'recorder',
        ])
        ->leftJoin('members', 'members.id', '=', 'tithes.member_id')
        ->orderBy('members.first_name')
        ->orderBy('members.middle_name')
        ->orderBy('members.last_name')
        ->get()
        ->map(function (Tithe $tithe, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member' => $tithe->member?->full_name ?? '—',
                'phone' => $tithe->member?->phone ?: '—',
                'kanda' => $tithe->member?->familia?->jumuiya?->kanda?->name
                    ?? $tithe->jumuiya?->kanda?->name
                    ?? '—',
                'jumuiya' => $tithe->jumuiya?->name
                    ?? $tithe->member?->familia?->jumuiya?->name
                    ?? '—',
                'recorded_by' => $tithe->recorder?->name ?? '—',
                'amount' => (float) $tithe->amount,
            ];
        })
        ->values();
}

public function getTitheActivityDateExportPdfData(User $user, string $date, array $filters = []): array
{
    $filters = $this->normalizeFilters($user, $filters);
    $rows = $this->getTitheActivityDateExportRows($user, $date, $filters);
    $activityDate = Carbon::parse($date)->format('Y-m-d');

    $recorderRows = (clone $this->titheActivityDateBaseQuery($user, $date, $filters))
        ->leftJoin('users', 'users.id', '=', 'tithes.recorded_by')
        ->selectRaw('tithes.recorded_by')
        ->selectRaw('COALESCE(users.name, ?) as recorder_name', [db_trans('unknown')])
        ->selectRaw('COUNT(tithes.id) as records_count')
        ->selectRaw('SUM(tithes.amount) as total_amount')
        ->groupBy('tithes.recorded_by', 'users.name')
        ->orderBy('recorder_name')
        ->get();

    return [
        'pageTitle' => db_trans('tithe_activity_date'),
        'reportTitle' => db_trans('tithe_activity_date_report_title_for') . ' ' . $activityDate,
        'metaItems' => [
            [
                'label' => db_trans('activity_date'),
                'value' => $activityDate,
            ],
            [
                'label' => db_trans('records'),
                'value' => number_format($rows->count()),
            ],
            [
                'label' => db_trans('recorders'),
                'value' => number_format($recorderRows->count()),
            ],
            [
                'label' => db_trans('grand_total'),
                'value' => number_format((float) $rows->sum('amount'), 2),
            ],
        ],
        'rows' => $rows,
        'recorderRows' => $recorderRows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

protected function titheActivityDateBaseQuery(User $user, string $date, array $filters = []): Builder
{
    return $this->query($user, array_filter([
        'kanda_id' => $filters['kanda_id'] ?? null,
        'jumuiya_id' => $filters['jumuiya_id'] ?? null,
        'status' => $filters['status'] ?? null,
        'payment_method' => $filters['payment_method'] ?? null,
        'member_id' => $filters['member_id'] ?? null,
    ]), false)->whereDate('tithes.contribution_date', $date);
}

    public function dashboardData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $base = $this->query($user, ['year' => $year]);

        $filtered = $this->query($user, array_filter([
            'year' => $year,
            'month' => $month,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]));

        $monthlyTrend = collect(range(1, 12))->map(function (int $m) use ($user, $year, $filters) {
            $query = $this->query($user, array_filter([
                'year' => $year,
                'month' => $m,
                'kanda_id' => $filters['kanda_id'] ?? null,
                'jumuiya_id' => $filters['jumuiya_id'] ?? null,
                'status' => $filters['status'] ?? null,
                'payment_method' => $filters['payment_method'] ?? null,
                'member_id' => $filters['member_id'] ?? null,
            ]));

            return [
                'label' => Carbon::create()->month($m)->format('M'),
                'amount' => (float) (clone $query)->sum('tithes.amount'),
                'members' => (int) (clone $query)->distinct('tithes.member_id')->count('tithes.member_id'),
            ];
        });

        $topJumuiyasYear = $this->topJumuiyas($user, $year, null, 5, $filters);
        $topJumuiyasMonth = $this->topJumuiyas($user, $year, $month ?: now()->month, 5, $filters);
        $topKandas = $this->topKandas($user, $year, $month, 5, $filters);

        $recent = (clone $filtered)
            ->with(['member.familia.jumuiya.kanda', 'jumuiya', 'recorder', 'approver'])
            ->latest('tithes.contribution_date')
            ->latest('tithes.id')
            ->limit(8)
            ->get();

        $monthTotal = (float) (clone $filtered)->sum('tithes.amount');
        $yearTotal = (float) (clone $base)->sum('tithes.amount');
        $approvedTotal = (float) (clone $base)->where('tithes.status', Tithe::STATUS_APPROVED)->sum('tithes.amount');
        $pendingTotal = (float) (clone $base)->where('tithes.status', Tithe::STATUS_PENDING)->sum('tithes.amount');
        $recordsCount = (int) (clone $base)->count('tithes.id');
        $membersPaidCount = (int) (clone $base)->distinct('tithes.member_id')->count('tithes.member_id');

        $duplicateMonthlyMembers = (int) $this->duplicateMonthlyGroups($user, [
            'year' => $year,
            'month' => $month,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
        ])->count();

        return [
            'pageTitle' => db_trans('tithe_dashboard'),
            'filters' => $filters,
            'hero' => [
                'eyebrow' => db_trans('finance'),
                'title' => db_trans('tithe_dashboard'),
                'subtitle' => db_trans('manage_financial_records'),
                'scope_badge' => $this->scopeLabel($user),
                'pending_items' => (int) (clone $base)->where('tithes.status', Tithe::STATUS_PENDING)->count('tithes.id'),
            ],
            'stats' => [
                'month_total' => $monthTotal,
                'year_total' => $yearTotal,
                'approved_total' => $approvedTotal,
                'pending_total' => $pendingTotal,
                'records_count' => $recordsCount,
                'members_paid_count' => $membersPaidCount,
                'average_per_member' => $membersPaidCount > 0 ? round($yearTotal / $membersPaidCount, 2) : 0,
                'duplicate_monthly_members' => $duplicateMonthlyMembers,
            ],
            'summaryCards' => [
                ['title' => db_trans('month'), 'value' => number_format($monthTotal, 2), 'meta' => db_trans('current_year_breakdown'), 'icon' => 'fas fa-calendar-day', 'tone' => 'primary'],
                ['title' => db_trans('year'), 'value' => number_format($yearTotal, 2), 'meta' => db_trans('financial_summary'), 'icon' => 'fas fa-calendar', 'tone' => 'success'],
                ['title' => db_trans('approved_total'), 'value' => number_format($approvedTotal, 2), 'meta' => db_trans('status_breakdown'), 'icon' => 'fas fa-circle-check', 'tone' => 'info'],
                ['title' => db_trans('pending_total'), 'value' => number_format($pendingTotal, 2), 'meta' => db_trans('pending_finance_records'), 'icon' => 'fas fa-clock', 'tone' => 'warning'],
                ['title' => db_trans('members_paid'), 'value' => number_format($membersPaidCount), 'meta' => db_trans('records'), 'icon' => 'fas fa-users', 'tone' => 'dark'],
                ['title' => db_trans('records'), 'value' => number_format($recordsCount), 'meta' => db_trans('recent_tithes'), 'icon' => 'fas fa-receipt', 'tone' => 'danger'],
            ],
            'monthlyChart' => [
                'labels' => $monthlyTrend->pluck('label')->values(),
                'amounts' => $monthlyTrend->pluck('amount')->values(),
                'members' => $monthlyTrend->pluck('members')->values(),
            ],
            'statusChart' => [
                'labels' => collect(Tithe::availableStatuses())->map(fn ($status) => db_trans($status))->values(),
                'amounts' => collect(Tithe::availableStatuses())->map(fn ($status) => (float) (clone $base)->where('tithes.status', $status)->sum('tithes.amount'))->values(),
            ],
            'topJumuiyasYear' => $topJumuiyasYear,
            'topJumuiyasMonth' => $topJumuiyasMonth,
            'topKandas' => $topKandas,
            'recentTithes' => $recent,
            'quickLinks' => [
                ['label' => db_trans('record_tithe'), 'icon' => 'fas fa-plus-circle', 'route' => route('finance.tithes.index')],
                ['label' => db_trans('bulk_tithe_entry'), 'icon' => 'fas fa-layer-group', 'route' => route('finance.tithes.bulk.entry')],
                ['label' => db_trans('monthly_matrix'), 'icon' => 'fas fa-table', 'route' => route('finance.tithes.index')],
                ['label' => db_trans('finance_dashboard'), 'icon' => 'fas fa-chart-pie', 'route' => route('finance.dashboard')],
            ],
        ];
    }

    public function indexData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);

        $query = $this->query($user, $filters)
            ->with(['member.familia.jumuiya.kanda', 'jumuiya', 'recorder', 'approver'])
            ->latest('tithes.contribution_date')
            ->latest('tithes.id');

        $monthlyRows = collect(range(1, 12))->map(function (int $monthNumber) use ($user, $filters, $year) {
            $chartFilters = $filters;
            unset($chartFilters['month']);

            $chartFilters['year'] = $year;
            $chartFilters['month'] = $monthNumber;

            return [
                'label' => Carbon::create(null, $monthNumber, 1)->translatedFormat('M'),
                'amount' => (float) $this->query($user, array_filter($chartFilters), false)
                    ->sum('tithes.amount'),
            ];
        });

        return [
            'pageTitle' => db_trans('tithes'),
            'filters' => $filters,
            'items' => $query->paginate(100)->withQueryString(),
            'total' => (float) $this->query($user, $filters)->sum('tithes.amount'),
            'defaultBulkDate' => now()->subMonthNoOverflow()->toDateString(),
            'monthlyChart' => [
                'labels' => $monthlyRows->pluck('label')->values(),
                'amounts' => $monthlyRows->pluck('amount')->values(),
            ],
        ];
    }

    public function getTithesExportRows(User $user, array $filters = []): Collection
    {
        $filters = $this->normalizeFilters($user, $filters);

        return $this->query($user, $filters)
            ->with(['member.familia.jumuiya.kanda', 'jumuiya'])
            ->latest('tithes.contribution_date')
            ->latest('tithes.id')
            ->get()
            ->map(function (Tithe $tithe) {
                return (object) [
                    'date' => optional($tithe->contribution_date)->format('d/m/Y') ?: '—',
                    'member' => $tithe->member?->full_name ?? '—',
                    'phone' => $tithe->member?->phone ?: '—',
                    'jumuiya' => $tithe->jumuiya?->name ?: '—',
                    'amount' => (float) $tithe->amount,
                    'payment_method' => $tithe->payment_method_label ?? $this->tithePaymentMethodLabel($tithe->payment_method),
                    'status' => $tithe->status_label ?? $this->titheStatusLabel($tithe->status),
                ];
            })
            ->values();
    }

    public function getTithesExportPdfData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);
        $rows = $this->getTithesExportRows($user, $filters);
        $filterLabel = $this->tithesFilterLabel($filters);

        return [
            'pageTitle' => db_trans('tithes'),
            'reportTitle' => db_trans('tithes_report_title_for') . ' ' . $filterLabel,
            'metaItems' => [
                [
                    'label' => db_trans('filters'),
                    'value' => $filterLabel,
                ],
                [
                    'label' => db_trans('records'),
                    'value' => number_format($rows->count()),
                ],
                [
                    'label' => db_trans('grand_total'),
                    'value' => number_format((float) $rows->sum('amount'), 2),
                ],
                [
                    'label' => db_trans('generated_on'),
                    'value' => now()->translatedFormat('d F Y'),
                ],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    protected function tithesFilterLabel(array $filters = []): string
    {
        $parts = [];

        if (!empty($filters['year'])) {
            if (!empty($filters['month'])) {
                $parts[] = Carbon::create((int) $filters['year'], (int) $filters['month'], 1)->translatedFormat('F Y');
            } else {
                $parts[] = (string) $filters['year'];
            }
        } else {
            $parts[] = db_trans('all_time');
        }

        if (!empty($filters['status'])) {
            $parts[] = db_trans('status') . ': ' . $this->titheStatusLabel($filters['status']);
        } else {
            $parts[] = db_trans('status') . ': ' . db_trans('all_statuses');
        }

        if (!empty($filters['kanda_id'])) {
            $kanda = \App\Models\Kanda::query()->find((int) $filters['kanda_id']);
            $parts[] = db_trans('kanda') . ': ' . ($kanda?->name ?? '—');
        } else {
            $parts[] = db_trans('kanda') . ': ' . db_trans('all_kandas');
        }

        if (!empty($filters['jumuiya_id'])) {
            $jumuiya = Jumuiya::query()->find((int) $filters['jumuiya_id']);
            $parts[] = db_trans('jumuiya') . ': ' . ($jumuiya?->name ?? '—');
        } else {
            $parts[] = db_trans('jumuiya') . ': ' . db_trans('all_jumuiyas');
        }

        if (!empty($filters['payment_method'])) {
            $parts[] = db_trans('payment_method') . ': ' . $this->tithePaymentMethodLabel($filters['payment_method']);
        }

        return implode(' | ', $parts);
    }

    protected function titheStatusLabel(?string $status): string
    {
        return match ($status) {
            Tithe::STATUS_APPROVED, 'approved', 'imeidhinishwa' => db_trans('approved'),
            Tithe::STATUS_PENDING, 'pending', 'inasubiri' => db_trans('pending'),
            'rejected', 'imekataliwa' => db_trans('rejected'),
            null, '' => '—',
            default => db_trans($status) ?: ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }

    protected function tithePaymentMethodLabel(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cash', 'taslimu' => db_trans('cash'),
            'bank', 'benki' => db_trans('bank'),
            'mobile_money', 'simu' => db_trans('mobile_money'),
            'other', 'nyingine' => db_trans('other'),
            null, '' => '—',
            default => db_trans($paymentMethod) ?: ucfirst(str_replace('_', ' ', (string) $paymentMethod)),
        };
    }

    public function bulkEntryData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $selectedDate = ! empty($filters['date']) ? Carbon::parse($filters['date']) : now()->subMonthNoOverflow();
        $selectedJumuiyaId = (int) ($filters['jumuiya_id'] ?? 0);
        $selectedKandaId = (int) ($filters['kanda_id'] ?? 0);

        $rows = collect();
        $existingMembers = collect();

        if ($selectedJumuiyaId > 0) {
            $rows = Member::query()
                ->whereHas('familia', fn (Builder $q) => $q->where('jumuiya_id', $selectedJumuiyaId))
                ->with('familia')
                ->orderBy('first_name')
                ->get();

            $existingMembers = $this->query($user, [
                'year' => (int) $selectedDate->format('Y'),
                'month' => (int) $selectedDate->format('n'),
                'jumuiya_id' => $selectedJumuiyaId,
            ], false)
                ->with(['member:id,first_name,middle_name,last_name,member_code', 'recorder:id,name'])
                ->get()
                ->groupBy('member_id');
        }

        return [
            'pageTitle' => db_trans('bulk_tithe_entry'),
            'selectedDate' => $selectedDate->toDateString(),
            'selectedMonthLabel' => $selectedDate->translatedFormat('F Y'),
            'selectedJumuiyaId' => $selectedJumuiyaId,
            'selectedKandaId' => $selectedKandaId,
            'rows' => $rows,
            'existingMembers' => $existingMembers,
            'denominationOptions' => [10000, 5000, 2000, 1000, 500, 200, 100],
        ];
    }

    public function duplicateReviewData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $groups = $this->duplicateMonthlyGroups($user, $filters)
            ->paginate(20)
            ->withQueryString();

        return [
            'pageTitle' => db_trans('duplicate_monthly_tithes_review'),
            'filters' => $filters,
            'groups' => $groups,
        ];
    }

    public function jumuiyaDetail(User $user, Jumuiya $jumuiya, array $filters = []): array
    {
        $this->ensureCanSeeJumuiya($user, $jumuiya);

        $year = (int) ($filters['year'] ?? now()->year);

        $base = $this->query($user, [
            'year' => $year,
            'jumuiya_id' => $jumuiya->id,
        ], false)
            ->leftJoin('members', 'members.id', '=', 'tithes.member_id')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id');

        $members = $base
            ->selectRaw('members.id as member_id')
            ->selectRaw("TRIM(CONCAT(COALESCE(members.first_name, ''), ' ', COALESCE(members.middle_name, ''), ' ', COALESCE(members.last_name, ''))) as member_name")
            ->selectRaw('members.member_code')
            ->selectRaw('members.bahasha')
            ->selectRaw('familias.name as familia_name')
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy(
                'members.id',
                'members.first_name',
                'members.middle_name',
                'members.last_name',
                'members.member_code',
                'members.bahasha',
                'familias.name'
            )
            ->orderByDesc(DB::raw('SUM(tithes.amount)'))
            ->get();

        return [
            'pageTitle' => db_trans('jumuiya_tithe_detail'),
            'jumuiya' => $jumuiya->load('kanda'),
            'filters' => $filters,
            'items' => $members,
            'total' => (float) $members->sum('total_amount'),
        ];
    }

    public function monthlyBreakdown(User $user, int $month, int $year): array
    {
        $items = $this->query($user, ['year' => $year, 'month' => $month], false)
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'tithes.jumuiya_id')
            ->leftJoin('kandas', 'kandas.id', '=', 'jumuiyas.kanda_id')
            ->selectRaw('jumuiyas.id as jumuiya_id')
            ->selectRaw('COALESCE(jumuiyas.name, ?) as jumuiya_name', [db_trans('unknown')])
            ->selectRaw('COALESCE(kandas.name, ?) as kanda_name', [db_trans('unknown')])
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy('jumuiyas.id', 'jumuiyas.name', 'kandas.name')
            ->orderByDesc(DB::raw('SUM(tithes.amount)'))
            ->get();

        return [
            'pageTitle' => db_trans('monthly_tithe_breakdown'),
            'month' => $month,
            'year' => $year,
            'items' => $items,
            'total' => (float) $items->sum('total_amount'),
        ];
    }

    public function memberMatrix(User $user, Jumuiya $jumuiya, int $year): array
    {
        $this->ensureCanSeeJumuiya($user, $jumuiya);

        $members = Member::query()
            ->whereHas('familia', fn (Builder $q) => $q->where('jumuiya_id', $jumuiya->id))
            ->with('familia')
            ->orderBy('first_name')
            ->get();

        $payments = $this->query($user, ['year' => $year, 'jumuiya_id' => $jumuiya->id], false)
            ->selectRaw('tithes.member_id as member_id')
            ->selectRaw('MONTH(tithes.contribution_date) as month_no')
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy('tithes.member_id', DB::raw('MONTH(tithes.contribution_date)'))
            ->get()
            ->groupBy('member_id');

        $rows = $members->map(function (Member $member) use ($payments) {
            $memberPayments = collect($payments->get($member->id, []))->keyBy('month_no');

            $months = collect(range(1, 12))->mapWithKeys(function (int $month) use ($memberPayments) {
                return [$month => (float) ($memberPayments->get($month)->total_amount ?? 0)];
            });

            return [
                'member' => $member,
                'months' => $months,
                'total' => $months->sum(),
            ];
        });

        return [
            'pageTitle' => db_trans('member_monthly_tithe_matrix'),
            'jumuiya' => $jumuiya->load('kanda'),
            'year' => $year,
            'rows' => $rows,
        ];
    }

    public function activityLogData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $filtered = $this->query($user, array_filter([
            'year' => $year,
            'month' => $month,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]), false);

        $monthFiltered = $this->query($user, array_filter([
            'year' => $year,
            'month' => $month ?: now()->month,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]), false);

        $yearFiltered = $this->query($user, array_filter([
            'year' => $year,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]), false);

        $monthlyByRecorderRows = $this->query($user, array_filter([
            'year' => $year,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]), false)
            ->leftJoin('users', 'users.id', '=', 'tithes.recorded_by')
            ->selectRaw('MONTH(tithes.contribution_date) as month_no')
            ->selectRaw('COALESCE(users.name, ?) as recorder_name', [db_trans('unknown')])
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy(DB::raw('MONTH(tithes.contribution_date)'), 'users.name')
            ->orderBy('month_no')
            ->get();

        $months = collect(range(1, 12))
            ->map(fn (int $monthNumber) => Carbon::create(null, $monthNumber, 1)->translatedFormat('M'))
            ->values();

        $recorderNames = $monthlyByRecorderRows
            ->pluck('recorder_name')
            ->unique()
            ->values();

        $datasets = $recorderNames->map(function ($recorderName) use ($monthlyByRecorderRows) {
            return [
                'label' => $recorderName,
                'data' => collect(range(1, 12))->map(function (int $monthNumber) use ($monthlyByRecorderRows, $recorderName) {
                    $row = $monthlyByRecorderRows
                        ->where('month_no', $monthNumber)
                        ->where('recorder_name', $recorderName)
                        ->first();

                    return (float) ($row->total_amount ?? 0);
                })->values(),
                'borderWidth' => 2,
                'tension' => 0.35,
            ];
        })->values();

        $activityDates = $this->titheActivityLogRowsQuery($user, [
            'year' => $year,
            'month' => $month,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ])->get();

        return [
            'pageTitle' => db_trans('tithe_activity_log'),
            'filters' => $filters,
            'parishName' => $this->parishName(),
            'stats' => [
                'month_total' => (float) (clone $monthFiltered)->sum('tithes.amount'),
                'year_total' => (float) (clone $yearFiltered)->sum('tithes.amount'),
                'records_count' => (int) (clone $filtered)->count('tithes.id'),
                'recorders_count' => (int) (clone $filtered)->distinct('tithes.recorded_by')->count('tithes.recorded_by'),
            ],
            'monthlyRecorderChart' => [
                'labels' => $months,
                'datasets' => $datasets,
            ],
            'activityDates' => $activityDates,
        ];
    }

    public function getTitheActivityRecorderExportRows(User $user, string $date, User $recorder, array $filters = []): Collection
{
    $filters = $this->normalizeFilters($user, $filters);

    return $this->titheActivityRecorderBaseQuery($user, $date, $recorder, $filters)
        ->select('tithes.*')
        ->with([
            'member.familia.jumuiya.kanda',
            'jumuiya.kanda',
            'recorder',
        ])
        ->leftJoin('members', 'members.id', '=', 'tithes.member_id')
        ->orderBy('members.first_name')
        ->orderBy('members.middle_name')
        ->orderBy('members.last_name')
        ->get()
        ->map(function (Tithe $tithe, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member' => $tithe->member?->full_name ?? '—',
                'phone' => $tithe->member?->phone ?: '—',
                'kanda' => $tithe->member?->familia?->jumuiya?->kanda?->name
                    ?? $tithe->jumuiya?->kanda?->name
                    ?? '—',
                'jumuiya' => $tithe->jumuiya?->name
                    ?? $tithe->member?->familia?->jumuiya?->name
                    ?? '—',
                'payment_method' => $tithe->payment_method_label ?? $this->tithePaymentMethodLabel($tithe->payment_method),
                'status' => $tithe->status_label ?? $this->titheStatusLabel($tithe->status),
                'amount' => (float) $tithe->amount,
            ];
        })
        ->values();
}

public function getTitheActivityRecorderExportPdfData(User $user, string $date, User $recorder, array $filters = []): array
{
    $filters = $this->normalizeFilters($user, $filters);
    $rows = $this->getTitheActivityRecorderExportRows($user, $date, $recorder, $filters);
    $activityDate = Carbon::parse($date)->format('Y-m-d');

    return [
        'pageTitle' => db_trans('tithe_recorder_detail'),
        'reportTitle' => db_trans('tithe_activity_recorder_report_title_for') . ' ' . $recorder->name . ' ' . db_trans('date') . ' ' . $activityDate,
        'metaItems' => [
            [
                'label' => db_trans('recorded_by'),
                'value' => $recorder->name,
            ],
            [
                'label' => db_trans('activity_date'),
                'value' => $activityDate,
            ],
            [
                'label' => db_trans('records'),
                'value' => number_format($rows->count()),
            ],
            [
                'label' => db_trans('grand_total'),
                'value' => number_format((float) $rows->sum('amount'), 2),
            ],
        ],
        'rows' => $rows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

protected function titheActivityRecorderBaseQuery(User $user, string $date, User $recorder, array $filters = []): Builder
{
    return $this->query($user, array_filter([
        'kanda_id' => $filters['kanda_id'] ?? null,
        'jumuiya_id' => $filters['jumuiya_id'] ?? null,
        'status' => $filters['status'] ?? null,
        'payment_method' => $filters['payment_method'] ?? null,
        'member_id' => $filters['member_id'] ?? null,
    ]), false)
        ->whereDate('tithes.contribution_date', $date)
        ->where('tithes.recorded_by', $recorder->id);
}

    public function activityDateData(User $user, string $date, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $base = $this->query($user, array_filter([
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]), false)->whereDate('tithes.contribution_date', $date);

      $items = (clone $base)
    ->select('tithes.*')
    ->with([
        'member.familia.jumuiya.kanda',
        'jumuiya.kanda',
        'recorder',
        'approver',
        'batch.denominations',
    ])
    ->leftJoin('members', 'members.id', '=', 'tithes.member_id')
    ->orderBy('members.first_name')
    ->orderBy('members.middle_name')
    ->orderBy('members.last_name')
    ->get();

        $recorderRows = (clone $base)
            ->leftJoin('users', 'users.id', '=', 'tithes.recorded_by')
            ->selectRaw('tithes.recorded_by')
            ->selectRaw('COALESCE(users.name, ?) as recorder_name', [db_trans('unknown')])
            ->selectRaw('COUNT(tithes.id) as records_count')
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy('tithes.recorded_by', 'users.name')
            ->orderBy('recorder_name')
            ->get();

        $batchIds = (clone $base)
            ->whereNotNull('tithes.tithe_batch_id')
            ->distinct()
            ->pluck('tithes.tithe_batch_id')
            ->filter()
            ->values();

        $batches = TitheBatch::query()
            ->with(['denominations', 'recorder', 'jumuiya.kanda'])
            ->whereIn('id', $batchIds)
            ->get();

        return [
            'pageTitle' => db_trans('tithe_activity_date'),
            'filters' => $filters,
            'parishName' => $this->parishName(),
            'activityDate' => Carbon::parse($date),
            'items' => $items,
            'recorderRows' => $recorderRows,
            'batches' => $batches,
            'total' => (float) (clone $base)->sum('tithes.amount'),
            'recordsCount' => (int) (clone $base)->count('tithes.id'),
        ];
    }

    public function activityRecorderData(User $user, string $date, User $recorder, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);

        $base = $this->query($user, array_filter([
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ]), false)
            ->whereDate('tithes.contribution_date', $date)
            ->where('tithes.recorded_by', $recorder->id);

     $items = (clone $base)
    ->select('tithes.*')
    ->with([
        'member.familia.jumuiya.kanda',
        'jumuiya.kanda',
        'recorder',
        'approver',
        'batch.denominations',
    ])
    ->leftJoin('members', 'members.id', '=', 'tithes.member_id')
    ->orderBy('members.first_name')
    ->orderBy('members.middle_name')
    ->orderBy('members.last_name')
    ->get();

        return [
            'pageTitle' => db_trans('tithe_recorder_detail'),
            'filters' => $filters,
            'parishName' => $this->parishName(),
            'activityDate' => Carbon::parse($date),
            'recorder' => $recorder,
            'items' => $items,
            'total' => (float) (clone $base)->sum('tithes.amount'),
            'recordsCount' => (int) (clone $base)->count('tithes.id'),
        ];
    }

    public function getTitheActivityLogExportRows(User $user, array $filters = []): Collection
    {
        $filters = $this->normalizeFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        return $this->titheActivityLogRowsQuery($user, [
            'year' => $year,
            'month' => $month,
            'kanda_id' => $filters['kanda_id'] ?? null,
            'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
            'member_id' => $filters['member_id'] ?? null,
        ])
            ->get()
            ->map(function ($row, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'activity_date' => $row->activity_date
                        ? Carbon::parse($row->activity_date)->format('Y-m-d')
                        : '—',
                    'records_count' => (int) ($row->records_count ?? 0),
                    'recorders_count' => (int) ($row->recorders_count ?? 0),
                    'batches_count' => (int) ($row->batches_count ?? 0),
                    'amount' => (float) ($row->total_amount ?? 0),
                ];
            })
            ->values();
    }

    public function getTitheActivityLogExportPdfData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeFilters($user, $filters);
        $rows = $this->getTitheActivityLogExportRows($user, $filters);
        $filterLabel = $this->titheActivityLogFilterLabel($filters);

        return [
            'pageTitle' => db_trans('tithe_activity_log'),
            'reportTitle' => db_trans('tithe_activity_log_report_title_for') . ' ' . $filterLabel,
            'metaItems' => [
                [
                    'label' => db_trans('filters'),
                    'value' => $filterLabel,
                ],
                [
                    'label' => db_trans('records'),
                    'value' => number_format((int) $rows->sum('records_count')),
                ],
                [
                    'label' => db_trans('bulk_batches'),
                    'value' => number_format((int) $rows->sum('batches_count')),
                ],
                [
                    'label' => db_trans('grand_total'),
                    'value' => number_format((float) $rows->sum('amount'), 2),
                ],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    protected function titheActivityLogRowsQuery(User $user, array $filters = []): Builder
    {
        return $this->query($user, array_filter($filters), false)
            ->leftJoin('tithe_batches', 'tithe_batches.id', '=', 'tithes.tithe_batch_id')
            ->selectRaw('DATE(tithes.contribution_date) as activity_date')
            ->selectRaw('COUNT(tithes.id) as records_count')
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->selectRaw('COUNT(DISTINCT tithes.recorded_by) as recorders_count')
            ->selectRaw('COUNT(DISTINCT tithes.tithe_batch_id) as batches_count')
            ->groupBy(DB::raw('DATE(tithes.contribution_date)'))
            ->orderByDesc('activity_date');
    }

    protected function titheActivityLogFilterLabel(array $filters = []): string
    {
        $parts = [];

        if (! empty($filters['year'])) {
            if (! empty($filters['month'])) {
                $parts[] = Carbon::create((int) $filters['year'], (int) $filters['month'], 1)->translatedFormat('F Y');
            } else {
                $parts[] = (string) $filters['year'];
            }
        } else {
            $parts[] = db_trans('all_time');
        }

        if (! empty($filters['kanda_id'])) {
            $kanda = \App\Models\Kanda::query()->find((int) $filters['kanda_id']);
            $parts[] = db_trans('kanda') . ': ' . ($kanda?->name ?? '—');
        } else {
            $parts[] = db_trans('kanda') . ': ' . db_trans('all_kandas');
        }

        if (! empty($filters['jumuiya_id'])) {
            $jumuiya = Jumuiya::query()->find((int) $filters['jumuiya_id']);
            $parts[] = db_trans('jumuiya') . ': ' . ($jumuiya?->name ?? '—');
        } else {
            $parts[] = db_trans('jumuiya') . ': ' . db_trans('all_jumuiyas');
        }

        if (! empty($filters['status'])) {
            $parts[] = db_trans('status') . ': ' . (db_trans($filters['status']) ?: ucfirst((string) $filters['status']));
        }

        if (! empty($filters['payment_method'])) {
            $parts[] = db_trans('payment_method') . ': ' . (db_trans($filters['payment_method']) ?: ucfirst(str_replace('_', ' ', (string) $filters['payment_method'])));
        }

        return implode(' | ', $parts);
    }

    protected function parishName(): string
    {
        return $this->siteSettingsService->get('site.name')
            ?: $this->siteSettingsService->get('church.name')
            ?: config('app.name', 'Parokia');
    }

    protected function topJumuiyas(User $user, int $year, ?int $month, int $limit = 5, array $filters = []): Collection
    {
        $queryFilters = array_filter([
            'year' => $year,
            'month' => $month,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
        ]);

        return $this->query($user, $queryFilters, false)
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'tithes.jumuiya_id')
            ->selectRaw('COALESCE(jumuiyas.name, ?) as name', [db_trans('unknown')])
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy('jumuiyas.name')
            ->orderByDesc(DB::raw('SUM(tithes.amount)'))
            ->limit($limit)
            ->get();
    }

    protected function topKandas(User $user, int $year, ?int $month, int $limit = 5, array $filters = []): Collection
    {
        return $this->query($user, array_filter([
            'year' => $year,
            'month' => $month,
            'status' => $filters['status'] ?? null,
            'payment_method' => $filters['payment_method'] ?? null,
        ]), false)
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'tithes.jumuiya_id')
            ->leftJoin('kandas', 'kandas.id', '=', 'jumuiyas.kanda_id')
            ->selectRaw('COALESCE(kandas.name, ?) as name', [db_trans('unknown')])
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy('kandas.name')
            ->orderByDesc(DB::raw('SUM(tithes.amount)'))
            ->limit($limit)
            ->get();
    }

    protected function duplicateMonthlyGroups(User $user, array $filters = [])
    {
        $filters = $this->normalizeFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        return $this->query(
            $user,
            array_filter([
                'year' => $year,
                'month' => $month,
                'jumuiya_id' => $filters['jumuiya_id'] ?? null,
            ]),
            false
        )
            ->leftJoin('members', 'members.id', '=', 'tithes.member_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'tithes.jumuiya_id')
            ->selectRaw('tithes.member_id')
            ->selectRaw('YEAR(tithes.contribution_date) as tithe_year')
            ->selectRaw('MONTH(tithes.contribution_date) as tithe_month')
            ->selectRaw("TRIM(CONCAT(COALESCE(members.first_name, ''), ' ', COALESCE(members.middle_name, ''), ' ', COALESCE(members.last_name, ''))) as member_name")
            ->selectRaw('members.member_code as member_code')
            ->selectRaw('COALESCE(jumuiyas.name, ?) as jumuiya_name', [db_trans('unknown')])
            ->selectRaw('COUNT(tithes.id) as entries_count')
            ->selectRaw('SUM(tithes.amount) as total_amount')
            ->groupBy(
                'tithes.member_id',
                DB::raw('YEAR(tithes.contribution_date)'),
                DB::raw('MONTH(tithes.contribution_date)'),
                'members.first_name',
                'members.middle_name',
                'members.last_name',
                'members.member_code',
                'jumuiyas.name'
            )
            ->havingRaw('COUNT(tithes.id) > 1')
            ->orderByDesc(DB::raw('COUNT(tithes.id)'))
            ->orderByDesc(DB::raw('SUM(tithes.amount)'));
    }

    protected function scopeLabel(User $user): string
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return db_trans('unknown');
        }

        return match (true) {
            $scope->isKanda() => db_trans('kanda'),
            $scope->isJumuiya() => db_trans('jumuiya'),
            default => db_trans('parish'),
        };
    }

    protected function query(User $user, array $filters = [], bool $selectAll = true): Builder
    {
        $scope = $this->access->scopeForUser($user);
        $query = Tithe::query();

        if ($selectAll) {
            $query->select('tithes.*');
        }

        $query = $this->access->applyScope($query, $scope);

        if (! empty($filters['year'])) {
            $query->whereYear('tithes.contribution_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('tithes.contribution_date', (int) $filters['month']);
        }

        if (! empty($filters['status'])) {
            $query->where('tithes.status', $filters['status']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('tithes.payment_method', $filters['payment_method']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('tithes.jumuiya_id', (int) $filters['jumuiya_id']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->whereHas('jumuiya', function (Builder $builder) use ($filters): void {
                $builder->where('kanda_id', (int) $filters['kanda_id']);
            });
        }

        if (! empty($filters['member_id'])) {
            $query->where('tithes.member_id', (int) $filters['member_id']);
        }

        if (! empty($filters['recorded_by'])) {
            $query->where('tithes.recorded_by', (int) $filters['recorded_by']);
        }

        return $query;
    }

    protected function ensureCanSeeJumuiya(User $user, Jumuiya $jumuiya): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            abort(403);
        }

        if ($scope->isGlobal()) {
            return;
        }

        if ($scope->isKanda() && (int) $jumuiya->kanda_id === (int) $scope->kandaId) {
            return;
        }

        if ($scope->isJumuiya() && (int) $jumuiya->id === (int) $scope->jumuiyaId) {
            return;
        }

        abort(403);
    }

    protected function normalizeFilters(User $user, array $filters): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        $filters['jumuiya_id'] = ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null;
        $filters['member_id'] = ! empty($filters['member_id']) ? (int) $filters['member_id'] : null;
        $filters['kanda_id'] = ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null;
        $filters['recorded_by'] = ! empty($filters['recorded_by']) ? (int) $filters['recorded_by'] : null;

        if ($scope->isGlobal()) {
            return $filters;
        }

        if ($scope->isKanda()) {
            if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda tithe scope.');
            }

            $filters['kanda_id'] = (int) $scope->kandaId;

            if ($filters['jumuiya_id']) {
                $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);

                if ((int) $jumuiya->kanda_id !== (int) $scope->kandaId) {
                    throw new AuthorizationException('You cannot access another jumuiya tithe scope.');
                }
            }

            return $filters;
        }

        if ($scope->isJumuiya()) {
            if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda tithe scope.');
            }

            $filters['kanda_id'] = (int) $scope->kandaId;

            if ($filters['jumuiya_id'] && (int) $filters['jumuiya_id'] !== (int) $scope->jumuiyaId) {
                throw new AuthorizationException('You cannot access another jumuiya tithe scope.');
            }

            $filters['jumuiya_id'] = (int) $scope->jumuiyaId;

            return $filters;
        }

        return $filters;
    }
}