<?php

namespace App\Services\Kanda;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use App\Services\SystemConfig\SiteSettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\Process;

class KandaPdfExportService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
        protected SiteSettingsService $siteSettingsService,
    ) {
    }

    public function exportGlobalReport(User $user, array $filters = []): string
    {
        abort_unless($user->can('kanda-reports.view'), 403);

        $data = $this->buildGlobalReportData($user, $filters);

        return $this->renderPdf(
            viewName: 'admin.kandas.reports.pdf',
            data: $data,
            filename: $this->makeFilename('kanda-global-report')
        );
    }

    public function exportKandaReport(Kanda $kanda, User $user, array $filters = []): string
    {
        abort_unless($user->can('kanda-reports.view'), 403);
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        $data = $this->buildKandaReportData($kanda, $user, $filters);

        return $this->renderPdf(
            viewName: 'admin.kandas.reports.pdf',
            data: $data,
            filename: $this->makeFilename('kanda-' . $kanda->id . '-report')
        );
    }

    public function exportJumuiyaReport(Jumuiya $jumuiya, User $user, array $filters = []): string
    {
        abort_unless($user->can('jumuiya-reports.view'), 403);

        if ($jumuiya->kanda) {
            $this->scopeAccessGate->authorizeKanda($user, $jumuiya->kanda);
        }

        $data = $this->buildJumuiyaReportData($jumuiya, $user, $filters);

        return $this->renderPdf(
            viewName: 'admin.kandas.reports.pdf',
            data: $data,
            filename: $this->makeFilename('jumuiya-' . $jumuiya->id . '-report')
        );
    }

public function exportBreakdownReport(array $data): string
{
    $locale = app()->getLocale();

    if (! empty($data['locale'])) {
        $locale = $data['locale'];
    } elseif (auth()->check() && ! empty(auth()->user()->locale)) {
        $locale = auth()->user()->locale;
    } elseif (session('locale')) {
        $locale = session('locale');
    }

    App::setLocale($locale);

    $payload = array_merge($data, [
        'branding' => $this->getPdfBranding(),
        'locale' => $locale,
    ]);

    return $this->renderWkhtmlPdf(
        viewName: 'pdf.reports.kanda-breakdown',
        data: $payload,
        filename: $this->makeFilename('kanda-breakdown')
    );
}

public function exportKandaBreakdownReport(array $data): string
{
    $locale = app()->getLocale();

    if (! empty($data['locale'])) {
        $locale = $data['locale'];
    } elseif (auth()->check() && ! empty(auth()->user()->locale)) {
        $locale = auth()->user()->locale;
    } elseif (session('locale')) {
        $locale = session('locale');
    }

    App::setLocale($locale);

    $payload = array_merge($data, [
        'branding' => $this->getPdfBranding(),
        'locale' => $locale,
    ]);

    return $this->renderWkhtmlPdf(
        viewName: 'pdf.reports.kanda-breakdown',
        data: $payload,
        filename: $this->makeFilename('kanda-' . ($data['kanda']->id ?? 'show') . '-breakdown')
    );
}

    protected function renderPdf(string $viewName, array $data, string $filename): string
    {
        $html = View::make($viewName, $data)->render();

        $directory = storage_path('app/reports/kanda');

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $path = $directory . '/' . $filename;

        Browsershot::html($html)
            ->showBackground()
            ->format('A4')
            ->margins(10, 10, 12, 10)
            ->waitUntilNetworkIdle()
            ->timeout(120)
            ->pdf($path);

        return $path;
    }

    protected function renderWkhtmlPdf(string $viewName, array $data, string $filename): string
    {
        $html = View::make($viewName, $data)->render();

        $directory = storage_path('app/reports/kanda');

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $htmlPath = $directory . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.html';
        $pdfPath = $directory . '/' . $filename;

        File::put($htmlPath, $html);

        $binary = $this->resolveWkhtmltopdfBinary();

        $arguments = [
            '--enable-local-file-access',
            '--encoding', 'utf-8',
            '--page-size', 'A4',
            '--margin-top', '10mm',
            '--margin-right', '10mm',
            '--margin-bottom', '12mm',
            '--margin-left', '10mm',
            '--print-media-type',
            $htmlPath,
            $pdfPath,
        ];

        if ($this->isBatchFile($binary)) {
            $command = array_merge(['cmd', '/c', $binary], $arguments);
        } else {
            $command = array_merge([$binary], $arguments);
        }

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                "wkhtmltopdf failed.\nCommand: " . implode(' ', $command) .
                "\nError Output: " . $process->getErrorOutput() .
                "\nStandard Output: " . $process->getOutput()
            );
        }

        return $pdfPath;
    }

    protected function resolveWkhtmltopdfBinary(): string
    {
        $configured = config('services.wkhtmltopdf.binary');

        if (! empty($configured)) {
            return str_replace('\\', '/', trim($configured));
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'C:/wkhtmltopdf/wkhtmltopdf.bat';
        }

        return '/usr/bin/wkhtmltopdf';
    }

    protected function isBatchFile(string $path): bool
    {
        $path = strtolower($path);

        return str_ends_with($path, '.bat') || str_ends_with($path, '.cmd');
    }

    protected function buildGlobalReportData(User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $kandas = $this->scopedKandasQuery($user)
            ->orderBy('name')
            ->get();

        $hierarchy = $this->preloadHierarchy($kandas);
        $jumuiyas = $hierarchy['jumuiyas'];
        $familias = $hierarchy['familias'];
        $members = $hierarchy['members'];

        $kandaRows = $kandas->map(function (Kanda $kanda) use ($jumuiyas, $familias, $members, $year, $month, $user) {
            return $this->buildKandaRowForPdf($kanda, $jumuiyas, $familias, $members, $year, $month, $user);
        });

        $stats = [
            'total_kandas' => $kandaRows->count(),
            'total_jumuiyas' => (int) $kandaRows->sum('jumuiyas_count'),
            'total_familias' => (int) $kandaRows->sum('familias_count'),
            'total_members' => (int) $kandaRows->sum('members_count'),
            'total_tithes' => (float) $kandaRows->sum('tithe_total'),
            'total_offerings' => (float) $kandaRows->sum('offering_total'),
            'total_cash' => (float) $kandaRows->sum('cash_total'),
            'total_bank' => (float) $kandaRows->sum('bank_total'),
            'total_mavuno' => (float) $kandaRows->sum('mavuno_total'),
            'grand_total' => (float) $kandaRows->sum('grand_total'),
        ];

        $monthly = $this->getMonthlyFinanceChartForVisibleKandas($kandas, $year, $month);
        $contributionTypeChart = $this->getContributionTypeChartForVisibleMembers($members, $year, $month);

        return $this->basePdfPayload(
            user: $user,
            reportTitle: db_trans('kanda_financial_reports'),
            reportSubtitle: db_trans('kanda_report_command_centre'),
            scopeType: 'global',
            scopeName: $this->resolveScopeLabel($user),
            year: $year,
            month: $month,
            stats: $stats,
            monthlyChart: $monthly,
            methodChart: [
                'labels' => $kandaRows->pluck('name')->values(),
                'tithes' => $kandaRows->pluck('tithe_total')->map(fn ($v) => (float) $v)->values(),
                'offerings' => $kandaRows->pluck('offering_total')->map(fn ($v) => (float) $v)->values(),
                'cash' => $kandaRows->pluck('cash_total')->map(fn ($v) => (float) $v)->values(),
                'bank' => $kandaRows->pluck('bank_total')->map(fn ($v) => (float) $v)->values(),
            ],
            contributionTypeChart: $contributionTypeChart,
            compositionChart: [
                'labels' => [
                    db_trans('tithes'),
                    db_trans('offerings'),
                    db_trans('cash_contributions'),
                    db_trans('bank_contributions'),
                ],
                'values' => [
                    (float) $stats['total_tithes'],
                    (float) $stats['total_offerings'],
                    (float) $stats['total_cash'],
                    (float) $stats['total_bank'],
                ],
            ],
            summaryTableTitle: db_trans('kanda_and_jumuiya_breakdown'),
            summaryRows: $kandaRows,
            detailSections: $kandaRows->map(function (array $row) {
                return [
                    'title' => $row['name'],
                    'subtitle' => $row['jumuiyas_count'] . ' ' . db_trans('jumuiyas') . ' · ' .
                        $row['familias_count'] . ' ' . db_trans('familias') . ' · ' .
                        $row['members_count'] . ' ' . db_trans('members'),
                    'rows' => $row['jumuiyas'],
                ];
            })->values()
        );
    }

    protected function buildKandaReportData(Kanda $kanda, User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $jumuiyas = Jumuiya::where('kanda_id', $kanda->id)->orderBy('name')->get();
        $familias = Familia::whereIn('jumuiya_id', $jumuiyas->pluck('id'))->get();
        $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();

        $summary = $this->buildKandaSummaryForPeriod($kanda, $jumuiyas, $familias, $members, $year, $month);
        $jumuiyaRows = $this->buildJumuiyaBreakdownForPdf($kanda, $jumuiyas, $familias, $members, $year, $month, $user);
        $monthly = $this->getMonthlyFinanceChartForKanda($kanda, $year, $month);
        $contributionTypeChart = $this->getContributionTypeChartForVisibleMembers($members, $year, $month);

        return $this->basePdfPayload(
            user: $user,
            reportTitle: db_trans('kanda_financial_report'),
            reportSubtitle: $kanda->name,
            scopeType: 'kanda',
            scopeName: $kanda->name,
            year: $year,
            month: $month,
            stats: [
                'total_kandas' => 1,
                'total_jumuiyas' => (int) $summary['jumuiyas_count'],
                'total_familias' => (int) $summary['familias_count'],
                'total_members' => (int) $summary['members_count'],
                'total_tithes' => (float) $summary['tithe_total'],
                'total_offerings' => (float) $summary['offering_total'],
                'total_cash' => (float) $summary['cash_total'],
                'total_bank' => (float) $summary['bank_total'],
                'total_mavuno' => (float) $summary['mavuno_total'],
                'grand_total' => (float) $summary['grand_total'],
            ],
            monthlyChart: $monthly,
            methodChart: [
                'labels' => $jumuiyaRows->pluck('name')->values(),
                'tithes' => $jumuiyaRows->pluck('tithe_total')->map(fn ($v) => (float) $v)->values(),
                'offerings' => $jumuiyaRows->pluck('offering_total')->map(fn ($v) => (float) $v)->values(),
                'cash' => $jumuiyaRows->pluck('cash_total')->map(fn ($v) => (float) $v)->values(),
                'bank' => $jumuiyaRows->pluck('bank_total')->map(fn ($v) => (float) $v)->values(),
            ],
            contributionTypeChart: $contributionTypeChart,
            compositionChart: [
                'labels' => [
                    db_trans('tithes'),
                    db_trans('offerings'),
                    db_trans('cash_contributions'),
                    db_trans('bank_contributions'),
                ],
                'values' => [
                    (float) $summary['tithe_total'],
                    (float) $summary['offering_total'],
                    (float) $summary['cash_total'],
                    (float) $summary['bank_total'],
                ],
            ],
            summaryTableTitle: db_trans('jumuiyas'),
            summaryRows: $jumuiyaRows,
            detailSections: collect()
        );
    }

    protected function buildJumuiyaReportData(Jumuiya $jumuiya, User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $familias = Familia::where('jumuiya_id', $jumuiya->id)->orderBy('name')->get();
        $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();

        $memberIds = $members->pluck('id');

        $titheTotal = Schema::hasTable('tithes')
            ? (float) $this->applyPeriodFilter(
                Tithe::query()->where('jumuiya_id', $jumuiya->id),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $offeringTotal = Schema::hasTable('offerings')
            ? (float) $this->applyPeriodFilter(
                Offering::query()->where('jumuiya_id', $jumuiya->id),
                'collection_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $cashTotal = Schema::hasTable('cash_contributions')
            ? (float) $this->applyPeriodFilter(
                CashContribution::query()->whereIn('member_id', $memberIds),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $bankTotal = Schema::hasTable('bank_contributions')
            ? (float) $this->applyPeriodFilter(
                BankContribution::query()->whereIn('member_id', $memberIds),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $mavunoTotal = Schema::hasTable('cash_contributions')
            ? (float) $this->applyPeriodFilter(
                CashContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->whereHas('contributionType', fn ($q) => $q->where('slug', 'mavuno')),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $familiaRows = $familias->map(function (Familia $familia) use ($members) {
            $familiaMembers = $members->where('familia_id', $familia->id);

            return [
                'name' => $familia->name,
                'familias_count' => 1,
                'members_count' => $familiaMembers->count(),
                'jumuiyas_count' => 1,
                'tithe_total' => 0,
                'offering_total' => 0,
                'cash_total' => 0,
                'bank_total' => 0,
                'grand_total' => 0,
                'details_url' => Route::has('familias.show') ? route('familias.show', $familia) : '#',
                'report_url' => '#',
            ];
        })->values();

        $monthly = $this->getMonthlyFinanceChartForJumuiya($jumuiya, $year, $month);
        $contributionTypeChart = $this->getContributionTypeChartForVisibleMembers($members, $year, $month);

        return $this->basePdfPayload(
            user: $user,
            reportTitle: db_trans('jumuiya_financial_report') ?: db_trans('financial_reports'),
            reportSubtitle: $jumuiya->name,
            scopeType: 'jumuiya',
            scopeName: $jumuiya->name,
            year: $year,
            month: $month,
            stats: [
                'total_kandas' => $jumuiya->kanda_id ? 1 : 0,
                'total_jumuiyas' => 1,
                'total_familias' => $familias->count(),
                'total_members' => $members->count(),
                'total_tithes' => $titheTotal,
                'total_offerings' => $offeringTotal,
                'total_cash' => $cashTotal,
                'total_bank' => $bankTotal,
                'total_mavuno' => $mavunoTotal,
                'grand_total' => $titheTotal + $offeringTotal + $cashTotal + $bankTotal,
            ],
            monthlyChart: $monthly,
            methodChart: [
                'labels' => collect([$jumuiya->name]),
                'tithes' => collect([$titheTotal]),
                'offerings' => collect([$offeringTotal]),
                'cash' => collect([$cashTotal]),
                'bank' => collect([$bankTotal]),
            ],
            contributionTypeChart: $contributionTypeChart,
            compositionChart: [
                'labels' => [
                    db_trans('tithes'),
                    db_trans('offerings'),
                    db_trans('cash_contributions'),
                    db_trans('bank_contributions'),
                ],
                'values' => [
                    $titheTotal,
                    $offeringTotal,
                    $cashTotal,
                    $bankTotal,
                ],
            ],
            summaryTableTitle: db_trans('familias'),
            summaryRows: $familiaRows,
            detailSections: collect()
        );
    }

    protected function basePdfPayload(
        User $user,
        string $reportTitle,
        string $reportSubtitle,
        string $scopeType,
        string $scopeName,
        int $year,
        ?int $month,
        array $stats,
        array $monthlyChart,
        array $methodChart,
        array $contributionTypeChart,
        array $compositionChart,
        string $summaryTableTitle,
        Collection $summaryRows,
        Collection $detailSections
    ): array {
        $locale = $this->resolveLocale($user);
        App::setLocale($locale);

        $site = $this->getSiteIdentity();

        return [
            'locale' => $locale,
            'site' => $site,
            'report' => [
                'title' => $reportTitle,
                'subtitle' => $reportSubtitle,
                'scope_type' => $scopeType,
                'scope_name' => $scopeName,
                'period_label' => $this->formatPeriodLabel($year, $month),
                'generated_at' => now()->translatedFormat('d M Y H:i'),
                'generated_by' => $user->name,
            ],
            'stats' => $stats,
            'summaryCards' => $this->makeSummaryCardsFromStats($stats),
            'financeCards' => $this->makeFinanceCardsFromStats($stats),
            'monthlyChart' => $monthlyChart,
            'methodChart' => $methodChart,
            'contributionTypeChart' => $contributionTypeChart,
            'compositionChart' => $compositionChart,
            'summaryTableTitle' => $summaryTableTitle,
            'summaryRows' => $summaryRows,
            'detailSections' => $detailSections,
        ];
    }

    protected function makeSummaryCardsFromStats(array $stats): array
    {
        return [
            [
                'label' => db_trans('total_kandas'),
                'value' => number_format((int) ($stats['total_kandas'] ?? 0)),
            ],
            [
                'label' => db_trans('total_jumuiyas'),
                'value' => number_format((int) ($stats['total_jumuiyas'] ?? 0)),
            ],
            [
                'label' => db_trans('total_familias'),
                'value' => number_format((int) ($stats['total_familias'] ?? 0)),
            ],
            [
                'label' => db_trans('total_members'),
                'value' => number_format((int) ($stats['total_members'] ?? 0)),
            ],
        ];
    }

    protected function makeFinanceCardsFromStats(array $stats): array
    {
        return [
            [
                'label' => db_trans('total_tithes'),
                'value' => number_format((float) ($stats['total_tithes'] ?? 0), 2),
            ],
            [
                'label' => db_trans('total_offerings'),
                'value' => number_format((float) ($stats['total_offerings'] ?? 0), 2),
            ],
            [
                'label' => db_trans('total_cash_contributions'),
                'value' => number_format((float) ($stats['total_cash'] ?? 0), 2),
            ],
            [
                'label' => db_trans('total_bank_contributions'),
                'value' => number_format((float) ($stats['total_bank'] ?? 0), 2),
            ],
            [
                'label' => db_trans('total_mavuno'),
                'value' => number_format((float) ($stats['total_mavuno'] ?? 0), 2),
            ],
            [
                'label' => db_trans('grand_total'),
                'value' => number_format((float) ($stats['grand_total'] ?? 0), 2),
            ],
        ];
    }

    protected function preloadHierarchy(Collection $kandas): array
    {
        $kandaIds = $kandas->pluck('id');

        $jumuiyas = Jumuiya::whereIn('kanda_id', $kandaIds)->get();
        $familias = Familia::whereIn('jumuiya_id', $jumuiyas->pluck('id'))->get();
        $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();

        return [
            'kandaIds' => $kandaIds,
            'jumuiyas' => $jumuiyas,
            'familias' => $familias,
            'members' => $members,
        ];
    }

    protected function scopedKandasQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);
        $query = Kanda::query();

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereKey($scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            if ($scope->kandaId) {
                return $query->whereKey($scope->kandaId);
            }

            return $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }

    protected function buildKandaRowForPdf(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        int $year,
        ?int $month,
        User $user
    ): array {
        $summary = $this->buildKandaSummaryForPeriod($kanda, $jumuiyas, $familias, $members, $year, $month);
        $jumuiyaRows = $this->buildJumuiyaBreakdownForPdf($kanda, $jumuiyas, $familias, $members, $year, $month, $user);

        return [
            'id' => $kanda->id,
            'name' => $kanda->name,
            'jumuiyas_count' => (int) $summary['jumuiyas_count'],
            'familias_count' => (int) $summary['familias_count'],
            'members_count' => (int) $summary['members_count'],
            'tithe_total' => (float) $summary['tithe_total'],
            'offering_total' => (float) $summary['offering_total'],
            'cash_total' => (float) $summary['cash_total'],
            'bank_total' => (float) $summary['bank_total'],
            'mavuno_total' => (float) $summary['mavuno_total'],
            'grand_total' => (float) $summary['grand_total'],
            'details_url' => $user->can('kandas.view') && Route::has('kandas.show') ? route('kandas.show', $kanda) : '#',
            'report_url' => $user->can('kanda-reports.view') && Route::has('kanda-reports.show') ? route('kanda-reports.show', $kanda) : '#',
            'jumuiyas' => $jumuiyaRows,
        ];
    }

    protected function buildKandaSummaryForPeriod(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        int $year,
        ?int $month
    ): array {
        $kandaJumuiyas = $jumuiyas->where('kanda_id', $kanda->id);
        $kandaFamilias = $familias->whereIn('jumuiya_id', $kandaJumuiyas->pluck('id'));
        $kandaMembers = $members->whereIn('familia_id', $kandaFamilias->pluck('id'));
        $memberIds = $kandaMembers->pluck('id');
        $jumuiyaIds = $kandaJumuiyas->pluck('id');

        $titheTotal = Schema::hasTable('tithes')
            ? (float) $this->applyPeriodFilter(
                Tithe::query()->whereIn('jumuiya_id', $jumuiyaIds),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $cashTotal = Schema::hasTable('cash_contributions')
            ? (float) $this->applyPeriodFilter(
                CashContribution::query()->whereIn('member_id', $memberIds),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $mavunoTotal = Schema::hasTable('cash_contributions')
            ? (float) $this->applyPeriodFilter(
                CashContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->whereHas('contributionType', fn ($q) => $q->where('slug', 'mavuno')),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $bankTotal = Schema::hasTable('bank_contributions')
            ? (float) $this->applyPeriodFilter(
                BankContribution::query()->whereIn('member_id', $memberIds),
                'contribution_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        $offeringTotal = Schema::hasTable('offerings')
            ? (float) $this->applyPeriodFilter(
                Offering::query()->where(function ($query) use ($jumuiyaIds, $kanda) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds)
                        ->orWhere('kanda_id', $kanda->id);
                }),
                'collection_date',
                $year,
                $month
            )->sum('amount')
            : 0.0;

        return [
            'jumuiyas_count' => $kandaJumuiyas->count(),
            'familias_count' => $kandaFamilias->count(),
            'members_count' => $kandaMembers->count(),
            'tithe_total' => $titheTotal,
            'mavuno_total' => $mavunoTotal,
            'cash_total' => $cashTotal,
            'bank_total' => $bankTotal,
            'offering_total' => $offeringTotal,
            'grand_total' => $titheTotal + $cashTotal + $bankTotal + $offeringTotal,
        ];
    }

    protected function buildJumuiyaBreakdownForPdf(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        int $year,
        ?int $month,
        User $user
    ): Collection {
        $kandaJumuiyas = $jumuiyas->where('kanda_id', $kanda->id)->sortBy('name');

        return $kandaJumuiyas->map(function (Jumuiya $jumuiya) use ($familias, $members, $year, $month, $user) {
            $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
            $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
            $memberIds = $jumuiyaMembers->pluck('id');

            $titheTotal = Schema::hasTable('tithes')
                ? (float) $this->applyPeriodFilter(
                    Tithe::query()->where('jumuiya_id', $jumuiya->id),
                    'contribution_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            $cashTotal = Schema::hasTable('cash_contributions')
                ? (float) $this->applyPeriodFilter(
                    CashContribution::query()->whereIn('member_id', $memberIds),
                    'contribution_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            $mavunoTotal = Schema::hasTable('cash_contributions')
                ? (float) $this->applyPeriodFilter(
                    CashContribution::query()
                        ->whereIn('member_id', $memberIds)
                        ->whereHas('contributionType', fn ($q) => $q->where('slug', 'mavuno')),
                    'contribution_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            $bankTotal = Schema::hasTable('bank_contributions')
                ? (float) $this->applyPeriodFilter(
                    BankContribution::query()->whereIn('member_id', $memberIds),
                    'contribution_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            $offeringTotal = Schema::hasTable('offerings')
                ? (float) $this->applyPeriodFilter(
                    Offering::query()->where('jumuiya_id', $jumuiya->id),
                    'collection_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            return [
                'name' => $jumuiya->name,
                'jumuiyas_count' => 1,
                'familias_count' => $jumuiyaFamilias->count(),
                'members_count' => $jumuiyaMembers->count(),
                'tithe_total' => $titheTotal,
                'offering_total' => $offeringTotal,
                'cash_total' => $cashTotal,
                'bank_total' => $bankTotal,
                'mavuno_total' => $mavunoTotal,
                'grand_total' => $titheTotal + $offeringTotal + $cashTotal + $bankTotal,
                'details_url' => $user->can('jumuiyas.view') && Route::has('jumuiyas.show')
                    ? route('jumuiyas.show', $jumuiya)
                    : '#',
                'report_url' => $user->can('jumuiya-reports.view') && Route::has('jumuiya-reports.show')
                    ? route('jumuiya-reports.show', $jumuiya)
                    : '#',
            ];
        })->values();
    }

    protected function getMonthlyFinanceChartForVisibleKandas(Collection $kandas, int $year, ?int $forcedMonth = null): array
    {
        $kandaIds = $kandas->pluck('id');
        $jumuiyaIds = Jumuiya::whereIn('kanda_id', $kandaIds)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        return $this->buildMonthlySeries($jumuiyaIds, $memberIds, $kandaIds, $year, $forcedMonth);
    }

    protected function getMonthlyFinanceChartForKanda(Kanda $kanda, int $year, ?int $forcedMonth = null): array
    {
        $jumuiyaIds = Jumuiya::where('kanda_id', $kanda->id)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        return $this->buildMonthlySeries($jumuiyaIds, $memberIds, collect([$kanda->id]), $year, $forcedMonth);
    }

    protected function getMonthlyFinanceChartForJumuiya(Jumuiya $jumuiya, int $year, ?int $forcedMonth = null): array
    {
        $jumuiyaIds = collect([$jumuiya->id]);
        $familiaIds = Familia::where('jumuiya_id', $jumuiya->id)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');
        $kandaIds = $jumuiya->kanda_id ? collect([$jumuiya->kanda_id]) : collect();

        return $this->buildMonthlySeries($jumuiyaIds, $memberIds, $kandaIds, $year, $forcedMonth);
    }

    protected function buildMonthlySeries(Collection $jumuiyaIds, Collection $memberIds, Collection $kandaIds, int $year, ?int $forcedMonth = null): array
    {
        $labels = [];
        $tithes = [];
        $offerings = [];
        $cash = [];
        $bank = [];

        $months = $forcedMonth ? [$forcedMonth] : range(1, 12);

        foreach ($months as $month) {
            $labels[] = now()->copy()->month($month)->translatedFormat('M');

            $tithes[] = Schema::hasTable('tithes')
                ? (float) Tithe::query()
                    ->whereIn('jumuiya_id', $jumuiyaIds)
                    ->whereYear('contribution_date', $year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0.0;

            $offerings[] = Schema::hasTable('offerings')
                ? (float) Offering::query()
                    ->where(function ($query) use ($jumuiyaIds, $kandaIds) {
                        $query->whereIn('jumuiya_id', $jumuiyaIds);

                        if ($kandaIds->isNotEmpty()) {
                            $query->orWhereIn('kanda_id', $kandaIds);
                        }
                    })
                    ->whereYear('collection_date', $year)
                    ->whereMonth('collection_date', $month)
                    ->sum('amount')
                : 0.0;

            $cash[] = Schema::hasTable('cash_contributions')
                ? (float) CashContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', $year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0.0;

            $bank[] = Schema::hasTable('bank_contributions')
                ? (float) BankContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', $year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0.0;
        }

        return [
            'labels' => collect($labels),
            'tithes' => collect($tithes),
            'offerings' => collect($offerings),
            'cash' => collect($cash),
            'bank' => collect($bank),
        ];
    }

    protected function getContributionTypeChartForVisibleMembers(Collection $members, int $year, ?int $month = null): array
    {
        if (! Schema::hasTable('contribution_types')) {
            return [
                'labels' => collect(),
                'cash' => collect(),
                'bank' => collect(),
                'totals' => collect(),
            ];
        }

        $memberIds = $members->pluck('id');

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = $types->map(function (ContributionType $type) use ($memberIds, $year, $month) {
            $cash = Schema::hasTable('cash_contributions')
                ? (float) $this->applyPeriodFilter(
                    CashContribution::query()
                        ->whereIn('member_id', $memberIds)
                        ->where('contribution_type_id', $type->id),
                    'contribution_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            $bank = Schema::hasTable('bank_contributions')
                ? (float) $this->applyPeriodFilter(
                    BankContribution::query()
                        ->whereIn('member_id', $memberIds)
                        ->where('contribution_type_id', $type->id),
                    'contribution_date',
                    $year,
                    $month
                )->sum('amount')
                : 0.0;

            return [
                'name' => $type->name,
                'cash_total' => $cash,
                'bank_total' => $bank,
                'total' => $cash + $bank,
            ];
        })
        ->filter(fn (array $row) => $row['total'] > 0)
        ->sortByDesc('total')
        ->values();

        return [
            'labels' => $rows->pluck('name')->values(),
            'cash' => $rows->pluck('cash_total')->map(fn ($v) => (float) $v)->values(),
            'bank' => $rows->pluck('bank_total')->map(fn ($v) => (float) $v)->values(),
            'totals' => $rows->pluck('total')->map(fn ($v) => (float) $v)->values(),
        ];
    }

    protected function applyPeriodFilter(Builder $query, string $column, int $year, ?int $month = null): Builder
    {
        $query->whereYear($column, $year);

        if (! empty($month)) {
            $query->whereMonth($column, $month);
        }

        return $query;
    }

    protected function resolveLocale(User $user): string
    {
        return $user->locale
            ?? session('locale')
            ?? $this->siteSettingsService->get('site.default_locale', config('app.locale', 'en'))
            ?? config('app.locale', 'en');
    }

    protected function getSiteIdentity(): array
    {
        $logo = $this->siteSettingsService->get('site.logo');
        $favicon = $this->siteSettingsService->get('site.favicon');
        $siteImage = $this->siteSettingsService->get('site.image');

        return [
            'name' => $this->siteSettingsService->get('site.name', config('app.name')),
            'tagline' => $this->siteSettingsService->get('site.tagline'),
            'description' => $this->siteSettingsService->get('site.description'),
            'logo' => $logo,
            'favicon' => $favicon,
            'image' => $siteImage,
            'email' => $this->siteSettingsService->get('church.email'),
            'phone' => $this->siteSettingsService->get('church.phone'),
            'address' => $this->siteSettingsService->get('church.address'),
            'public_url' => $this->siteSettingsService->get('site.public_url', url('/')),
            'footer_note' => $this->siteSettingsService->get('footer.bottom_note'),
            'footer_tagline' => $this->siteSettingsService->get('footer.tagline'),
            'logo_url' => $this->normalizeStorageUrl($logo),
            'favicon_url' => $this->normalizeStorageUrl($favicon),
            'image_url' => $this->normalizeStorageUrl($siteImage),
        ];
    }

    protected function normalizeStorageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $cleanPath = ltrim(str_replace('storage/', '', $path), '/');

        return asset('storage/' . $cleanPath);
    }

    protected function getPdfBranding(): array
    {
        $site = $this->getSiteIdentity();

        $primaryLogo = $this->siteSettingsService->get('site.logo');
        $secondaryLogo = $this->siteSettingsService->get('site.secondary_logo');

        return [
            'site_name' => $site['name'] ?? config('app.name'),
            'diocese_name' => $this->siteSettingsService->get('church.diocese_name'),
            'email' => $site['email'] ?? null,
            'phone' => $site['phone'] ?? null,
            'address' => $site['address'] ?? null,

            'logo' => $this->toPublicPath($primaryLogo),
            'left_logo' => $this->toPublicPath($primaryLogo),
            'right_logo' => $this->toPublicPath($secondaryLogo ?: $primaryLogo),

            'left_logo_label' => null,
            'right_logo_label' => null,

            'footer_text' => $site['footer_note'] ?? ($site['name'] ?? config('app.name')),
            'footer_quote' => $site['footer_tagline'] ?? null,
        ];
    }

 protected function toPublicPath(?string $path): ?string
{
    if (blank($path)) {
        return null;
    }

    $clean = ltrim(trim($path), '/\\');
    $clean = str_replace('\\', '/', $clean);

    // full absolute path already
    if (
        preg_match('/^[A-Za-z]:[\/\\\\]/', $path) ||
        str_starts_with($path, '\\\\') ||
        str_starts_with($path, '/')
    ) {
        $absolutePath = str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (is_file($absolutePath)) {
            return $absolutePath;
        }
    }

    // storage path like storage/uploads/...
    if (str_starts_with($clean, 'storage/')) {
        $storagePath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $clean));

        if (is_file($storagePath)) {
            return $storagePath;
        }
    }

    // public relative path like uploads/frontend/logo/logo2.jpeg
    $publicPath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $clean));

    if (is_file($publicPath)) {
        return $publicPath;
    }

    return null;
}

    protected function formatPeriodLabel(int $year, ?int $month = null): string
    {
        if ($month) {
            return now()
                ->setYear($year)
                ->setMonth($month)
                ->startOfMonth()
                ->translatedFormat('F Y');
        }

        return (string) $year;
    }

    protected function resolveScopeLabel(User $user): string
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return db_trans('invalid_scope');
        }

        if ($scope->isGlobal()) {
            return db_trans('parish_scope');
        }

        if ($scope->isKanda()) {
            $kanda = Kanda::find($scope->kandaId);

            return $kanda ? $kanda->name : db_trans('kanda_scope');
        }

        if ($scope->isJumuiya()) {
            $jumuiya = Jumuiya::find($scope->jumuiyaId);

            return $jumuiya ? $jumuiya->name : db_trans('jumuiya_scope');
        }

        return db_trans('parish_scope');
    }

    protected function makeFilename(string $prefix): string
    {
        return $prefix . '-' . now()->format('Ymd-His') . '.pdf';
    }
}