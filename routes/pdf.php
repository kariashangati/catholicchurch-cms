<?php

use App\Http\Controllers\Admin\Finance\BankAccountController;
use App\Http\Controllers\Admin\Finance\BankContributionController;
use App\Http\Controllers\Admin\Finance\BudgetExpenseEstimateController;
use App\Http\Controllers\Admin\Finance\BudgetIncomeEstimateController;
use App\Http\Controllers\Admin\Finance\CashContributionController;
use App\Http\Controllers\Admin\Finance\ContributionDashboardController;
use App\Http\Controllers\Admin\Finance\FinanceDashboardController;
use App\Http\Controllers\Admin\Finance\GiversNonGiversReportController;
use App\Http\Controllers\Admin\Finance\OfferingController;
use App\Http\Controllers\Admin\Finance\ProjectFinanceController;
use App\Http\Controllers\Admin\Finance\ProjectFinanceDashboardController;
use App\Http\Controllers\Admin\Finance\TitheController;
use App\Http\Controllers\Admin\Reports\FinanceReportController;
use App\Http\Controllers\Admin\Leadership\LeadershipAssignmentController;
use App\Http\Controllers\Admin\Leadership\LeadershipDashboardController;
use App\Http\Controllers\Admin\Leadership\LeadershipPositionController;
use App\Http\Controllers\Admin\Reports\SacramentLegacyReportController;
use App\Http\Controllers\Admin\SacramentReportController;
use App\Http\Controllers\ApostolicGroupController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\FamiliaMemberController;
use App\Http\Controllers\JumuiyaController;
use App\Http\Controllers\JumuiyaMemberController;
use App\Http\Controllers\KandaController;
use App\Http\Controllers\KandaReportExportController;
use App\Http\Controllers\MafundishoEnrollmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\KandaReportController;
use App\Http\Controllers\Admin\Liturgy\MassScheduleController;
use App\Http\Controllers\Admin\Liturgy\MassTypeController;
use App\Http\Controllers\Admin\Liturgy\OfferingTypeController;
use App\Http\Controllers\Admin\Operations\AssetCategoryController;
use App\Http\Controllers\Admin\Operations\ChurchAssetController;
use App\Http\Controllers\Admin\Operations\ServiceCategoryController;
use App\Http\Controllers\Admin\Operations\ServiceProviderController;
use App\Http\Controllers\Admin\Membership\AgeGroupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JumuiyaReportController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('pdf')->name('pdf.')->group(function () {


        Route::get('/membership/age-groups/export', [AgeGroupController::class, 'exportPdf'])
            ->middleware('permission:membership.age-groups.view')
            ->name('membership.age-groups.export');

        Route::get('/operations/service-providers/export', [ServiceProviderController::class, 'exportPdf'])
            ->middleware('permission:operations.service-providers.view')
            ->name('operations.service-providers.export');

        Route::get('/operations/service-categories/export', [ServiceCategoryController::class, 'exportPdf'])
            ->middleware('permission:operations.service-categories.view')
            ->name('operations.service-categories.export');

        Route::get('/operations/church-assets/export', [ChurchAssetController::class, 'exportPdf'])
            ->middleware('permission:operations.church-assets.view')
            ->name('operations.church-assets.export');

        Route::get('/operations/asset-categories/export', [AssetCategoryController::class, 'exportPdf'])
            ->middleware('permission:operations.asset-categories.view')
            ->name('operations.asset-categories.export');

        Route::get('/finance/bank-accounts/export', [BankAccountController::class, 'exportPdf'])
            ->middleware('permission:finance.bank-accounts.view')
            ->name('finance.bank-accounts.export');

        Route::get('/liturgy/mass-types/export', [MassTypeController::class, 'exportPdf'])
            ->middleware('permission:liturgy.mass-types.view')
            ->name('liturgy.mass-types.export');

        Route::get('/liturgy/offering-types/export', [OfferingTypeController::class, 'exportPdf'])
            ->middleware('permission:liturgy.offering-types.view')
            ->name('liturgy.offering-types.export');

        Route::get('/liturgy/mass-schedules/export', [MassScheduleController::class, 'exportPdf'])
            ->middleware('permission:liturgy.mass-schedules.view')
            ->name('liturgy.mass-schedules.export');


        Route::get('/leadership/dashboard/export', [LeadershipDashboardController::class, 'exportPdf'])
            ->middleware('permission:leadership.assignments.view|leadership.positions.view')
            ->name('leadership.dashboard.export');

        Route::get('/leadership/assignments/export', [LeadershipAssignmentController::class, 'exportPdf'])
            ->middleware('permission:leadership.assignments.view')
            ->name('leadership.assignments.export');

        Route::get('/leadership/positions/export', [LeadershipPositionController::class, 'exportPdf'])
            ->middleware('permission:leadership.positions.view')
            ->name('leadership.positions.export');


    Route::get('/jumuiya-financial-reports/export', [JumuiyaReportController::class, 'exportIndexPdf'])
    ->middleware('permission:jumuiya-reports.view')
    ->name('jumuiya-reports.export');

Route::get('/jumuiya-financial-reports/{jumuiya}/export', [JumuiyaReportController::class, 'exportShowPdf'])
    ->middleware('permission:jumuiya-reports.view')
    ->name('jumuiya-reports.show.export');

    Route::get('/kanda-financial-reports/export', [KandaReportController::class, 'exportIndexPdf'])
    ->middleware('permission:kanda-reports.view')
    ->name('kanda-reports.export');

Route::get('/kanda-financial-reports/{kanda}/export', [KandaReportController::class, 'exportShowPdf'])
    ->middleware('permission:kanda-reports.view')
    ->name('kanda-reports.show.export');
        Route::get('/reports/sacraments/export', [SacramentLegacyReportController::class, 'exportPdf'])
            ->middleware('permission:reports.sacraments.view')
            ->name('reports.sacraments.export');

        Route::get('/reports/finance/export', [FinanceReportController::class, 'exportFinancialSummaryPdf'])
            ->middleware('permission:reports.finance.view')
            ->name('finance.reports.financial-summary.export');

        Route::get('/finance/reports/waliotoa-wasiotoa/export', [GiversNonGiversReportController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.reports.waliotoa.export');

        Route::get('/finance/budgets/income/export', [BudgetIncomeEstimateController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.budgets.income.export');

        Route::get('/finance/budgets/expense/export', [BudgetExpenseEstimateController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.budgets.expense.export');

        Route::get('/finance/contributions/cash/export', [CashContributionController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.contributions.cash.export');

        Route::get('/finance/contributions/bank/export', [BankContributionController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.contributions.bank.export');

        Route::get('/finance/activity/export', [FinanceDashboardController::class, 'exportActivityPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.activity.export');

        Route::get('/finance/offerings/export', [OfferingController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.offerings.export');

        Route::get('/finance/contributions/dashboard/cash/export', [ContributionDashboardController::class, 'exportCashPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.contributions.cash.dashboard.export');

        Route::get('/finance/contributions/dashboard/bank/export', [ContributionDashboardController::class, 'exportBankPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.contributions.bank.dashboard.export');

        Route::get('/finance/tithes/export', [TitheController::class, 'exportPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.tithes.export');

        Route::get('/finance/tithes/activity-log/export', [TitheController::class, 'exportActivityLogPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.tithes.activity-log.export');

        Route::get('/finance/tithes/activity-date/{date}/export', [TitheController::class, 'exportActivityDatePdf'])
            ->middleware('permission:finance.view')
            ->name('finance.tithes.activity-date.export');

        Route::get('/finance/tithes/activity-date/{date}/recorder/{recorder}/export', [TitheController::class, 'exportActivityRecorderPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.tithes.activity-recorder.export');

        Route::get('/finance/projects/dashboard/top-projects/export', [ProjectFinanceDashboardController::class, 'exportTopProjectsPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.projects.dashboard.top-projects.export');

        Route::get('/finance/projects/export', [ProjectFinanceController::class, 'exportProjectsPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.projects.export');

        Route::get('/finance/projects/transactions/export', [ProjectFinanceController::class, 'exportTransactionsPdf'])
            ->middleware('permission:finance.view')
            ->name('finance.projects.transactions.export');

        Route::get('/mafundisho/{type}/{year}/export', [MafundishoEnrollmentController::class, 'exportTypePdf'])
            ->middleware('permission:mafundisho.view')
            ->name('mafundisho.type');

        Route::prefix('kanda-reports')->name('kanda-reports.')->group(function () {
            Route::get('/scoped', [KandaReportExportController::class, 'scoped'])->middleware('permission:kanda-reports.view')->name('scoped');
            Route::get('/kanda/{kanda}', [KandaReportExportController::class, 'kanda'])->middleware('permission:kanda-reports.view')->name('kanda');
            Route::get('/jumuiya/{jumuiya}', [KandaReportExportController::class, 'jumuiya'])->middleware('permission:jumuiya-reports.view')->name('jumuiya');
            Route::get('/breakdown', [KandaController::class, 'exportBreakdownPdf'])->middleware('permission:kanda-reports.view')->name('breakdown');
            Route::get('/breakdown/kanda/{kanda}', [KandaController::class, 'exportSingleBreakdownPdf'])->middleware('permission:kanda-reports.view')->name('kanda-breakdown');
        });

        Route::get('/kandas/{kanda}/recent-members/export', [KandaController::class, 'exportRecentMembersPdf'])
            ->middleware('permission:kandas.view')
            ->name('kandas.recent-members.export');

        Route::get('/members/export', [MemberController::class, 'exportPdf'])->middleware('permission:members.view')->name('members.export');
        Route::get('/members/{member}/profile', [MemberController::class, 'exportProfilePdf'])->middleware('permission:members.view')->name('members.profile');

        Route::get('/sacraments/kandas/export', [SacramentReportController::class, 'exportKandasPdf'])->middleware('permission:sacraments.kanda.view')->name('sacraments.kandas.export');
        Route::get('/sacraments/kandas/{kanda}/members/export', [SacramentReportController::class, 'exportKandaMembersPdf'])->middleware('permission:sacraments.kanda.view')->name('sacraments.kandas.members.export');

        Route::get('/sacraments/jumuiyas/export', [SacramentReportController::class, 'exportJumuiyasPdf'])->middleware('permission:sacraments.jumuiya.view')->name('sacraments.jumuiyas.export');
        Route::get('/sacraments/jumuiyas/{jumuiya}/members/export', [SacramentReportController::class, 'exportJumuiyaMembersPdf'])->middleware('permission:sacraments.jumuiya.view')->name('sacraments.jumuiyas.members.export');

        Route::get('/jumuiyas/export', [JumuiyaController::class, 'exportPdf'])->middleware('permission:jumuiyas.view')->name('jumuiyas.export');

        Route::get('/jumuiyas/{jumuiya}/familias/export', [JumuiyaController::class, 'exportFamiliasPdf'])
            ->middleware('permission:jumuiyas.view')
            ->name('jumuiyas.familias.export');

        Route::get('/familias/export', [FamiliaController::class, 'exportPdf'])->middleware('permission:familias.view')->name('familias.export');
        Route::get('/familias/{familia}/members/export', [FamiliaMemberController::class, 'exportPdf'])->middleware('permission:familias.view')->name('familias.members.export');
        Route::get('/jumuiyas/{jumuiya}/members/pdf', [JumuiyaMemberController::class, 'exportPdf'])->middleware('permission:jumuiyas.members.view')->name('jumuiyas.members.pdf');

        Route::get('/apostolic-groups/export', [ApostolicGroupController::class, 'exportPdf'])->middleware('permission:apostolic-groups.view')->name('apostolic-groups.export');
        Route::get('/apostolic-groups/{apostolicGroup}/members/pdf', [ApostolicGroupController::class, 'exportMembersPdf'])->middleware('permission:apostolic-groups.view')->name('apostolic-groups.members.pdf');
    });
});