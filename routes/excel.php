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
use App\Http\Controllers\MafundishoEnrollmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\Admin\Liturgy\MassScheduleController;
use App\Http\Controllers\Admin\Liturgy\MassTypeController;
use App\Http\Controllers\Admin\Liturgy\OfferingTypeController;
use App\Http\Controllers\Admin\Operations\AssetCategoryController;
use App\Http\Controllers\Admin\Operations\ChurchAssetController;
use App\Http\Controllers\Admin\Operations\ServiceCategoryController;
use App\Http\Controllers\Admin\Operations\ServiceProviderController;
use App\Http\Controllers\Admin\Membership\AgeGroupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KandaReportController;
use App\Http\Controllers\JumuiyaReportController;

Route::middleware(['web', 'auth'])->group(function () {


    Route::get('/membership/age-groups/export/excel', [AgeGroupController::class, 'exportExcel'])
        ->middleware('permission:membership.age-groups.view')
        ->name('membership.age-groups.export.excel');

    Route::get('/operations/service-providers/export/excel', [ServiceProviderController::class, 'exportExcel'])
        ->middleware('permission:operations.service-providers.view')
        ->name('operations.service-providers.export.excel');

    Route::get('/operations/service-categories/export/excel', [ServiceCategoryController::class, 'exportExcel'])
        ->middleware('permission:operations.service-categories.view')
        ->name('operations.service-categories.export.excel');

    Route::get('/operations/church-assets/export/excel', [ChurchAssetController::class, 'exportExcel'])
        ->middleware('permission:operations.church-assets.view')
        ->name('operations.church-assets.export.excel');

    Route::get('/operations/asset-categories/export/excel', [AssetCategoryController::class, 'exportExcel'])
        ->middleware('permission:operations.asset-categories.view')
        ->name('operations.asset-categories.export.excel');

    Route::get('/finance/bank-accounts/export/excel', [BankAccountController::class, 'exportExcel'])
        ->middleware('permission:finance.bank-accounts.view')
        ->name('finance.bank-accounts.export.excel');

    Route::get('/liturgy/mass-types/export/excel', [MassTypeController::class, 'exportExcel'])
        ->middleware('permission:liturgy.mass-types.view')
        ->name('liturgy.mass-types.export.excel');

    Route::get('/liturgy/offering-types/export/excel', [OfferingTypeController::class, 'exportExcel'])
        ->middleware('permission:liturgy.offering-types.view')
        ->name('liturgy.offering-types.export.excel');

    Route::get('/liturgy/mass-schedules/export/excel', [MassScheduleController::class, 'exportExcel'])
        ->middleware('permission:liturgy.mass-schedules.view')
        ->name('liturgy.mass-schedules.export.excel');


    Route::get('/leadership/dashboard/export/excel', [LeadershipDashboardController::class, 'exportExcel'])
        ->middleware('permission:leadership.assignments.view|leadership.positions.view')
        ->name('leadership.dashboard.export.excel');

    Route::get('/leadership/assignments/export/excel', [LeadershipAssignmentController::class, 'exportExcel'])
        ->middleware('permission:leadership.assignments.view')
        ->name('leadership.assignments.export.excel');

    Route::get('/leadership/positions/export/excel', [LeadershipPositionController::class, 'exportExcel'])
        ->middleware('permission:leadership.positions.view')
        ->name('leadership.positions.export.excel');


Route::get('/jumuiya-financial-reports/export/excel', [JumuiyaReportController::class, 'exportIndexExcel'])
    ->middleware('permission:jumuiya-reports.view')
    ->name('jumuiya-reports.export.excel');

Route::get('/jumuiya-financial-reports/{jumuiya}/export/excel', [JumuiyaReportController::class, 'exportShowExcel'])
    ->middleware('permission:jumuiya-reports.view')
    ->name('jumuiya-reports.show.export.excel');


Route::get('/kanda-financial-reports/export/excel', [KandaReportController::class, 'exportIndexExcel'])
    ->middleware('permission:kanda-reports.view')
    ->name('kanda-reports.export.excel');

Route::get('/kanda-financial-reports/{kanda}/export/excel', [KandaReportController::class, 'exportShowExcel'])
    ->middleware('permission:kanda-reports.view')
    ->name('kanda-reports.show.export.excel');
    Route::get('/reports/sacraments/export/excel', [SacramentLegacyReportController::class, 'exportExcel'])
        ->middleware('permission:reports.sacraments.view')
        ->name('reports.sacraments.export.excel');

    Route::get('/reports/finance/export/excel', [FinanceReportController::class, 'exportFinancialSummaryExcel'])
        ->middleware('permission:reports.finance.view')
        ->name('finance.reports.financial-summary.export.excel');

    Route::get('/finance/reports/waliotoa-wasiotoa/export/excel', [GiversNonGiversReportController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.reports.waliotoa.export.excel');

    Route::get('/finance/budgets/income/export/excel', [BudgetIncomeEstimateController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.budgets.income.export.excel');

    Route::get('/finance/budgets/expense/export/excel', [BudgetExpenseEstimateController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.budgets.expense.export.excel');

    Route::get('/finance/contributions/cash/export/excel', [CashContributionController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.contributions.cash.export.excel');

    Route::get('/finance/contributions/bank/export/excel', [BankContributionController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.contributions.bank.export.excel');

    Route::get('/finance/activity/export/excel', [FinanceDashboardController::class, 'exportActivityExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.activity.export.excel');

    Route::get('/finance/offerings/export/excel', [OfferingController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.offerings.export.excel');

    Route::get('/finance/contributions/dashboard/cash/export/excel', [ContributionDashboardController::class, 'exportCashExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.contributions.cash.dashboard.export.excel');

    Route::get('/finance/contributions/dashboard/bank/export/excel', [ContributionDashboardController::class, 'exportBankExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.contributions.bank.dashboard.export.excel');

    Route::get('/finance/tithes/export/excel', [TitheController::class, 'exportExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.tithes.export.excel');

    Route::get('/finance/tithes/activity-log/export/excel', [TitheController::class, 'exportActivityLogExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.tithes.activity-log.export.excel');

    Route::get('/finance/tithes/activity-date/{date}/export/excel', [TitheController::class, 'exportActivityDateExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.tithes.activity-date.export.excel');

    Route::get('/finance/tithes/activity-date/{date}/recorder/{recorder}/export/excel', [TitheController::class, 'exportActivityRecorderExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.tithes.activity-recorder.export.excel');

    Route::get('/finance/projects/dashboard/top-projects/export/excel', [ProjectFinanceDashboardController::class, 'exportTopProjectsExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.projects.dashboard.top-projects.export.excel');

    Route::get('/finance/projects/export/excel', [ProjectFinanceController::class, 'exportProjectsExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.projects.export.excel');

    Route::get('/finance/projects/transactions/export/excel', [ProjectFinanceController::class, 'exportTransactionsExcel'])
        ->middleware('permission:finance.view')
        ->name('finance.projects.transactions.export.excel');

    Route::get('/kandas/export/excel', [KandaController::class, 'exportExcel'])->middleware('permission:kandas.view')->name('kandas.export.excel');
    Route::get('/kandas/{kanda}/export/excel', [KandaController::class, 'exportSingleExcel'])->middleware('permission:kandas.view')->name('kandas.export.single.excel');

    Route::get('/kandas/{kanda}/recent-members/export/excel', [KandaController::class, 'exportRecentMembersExcel'])
        ->middleware('permission:kandas.view')
        ->name('kandas.recent-members.export.excel');

    Route::get('/members/export/excel', [MemberController::class, 'exportExcel'])->middleware('permission:members.view')->name('members.export.excel');

    Route::get('/sacraments/kandas/export/excel', [SacramentReportController::class, 'exportKandasExcel'])->middleware('permission:sacraments.kanda.view')->name('sacraments.kandas.export.excel');
    Route::get('/sacraments/kandas/{kanda}/members/export/excel', [SacramentReportController::class, 'exportKandaMembersExcel'])->middleware('permission:sacraments.kanda.view')->name('sacraments.kandas.members.export.excel');

    Route::get('/sacraments/jumuiyas/export/excel', [SacramentReportController::class, 'exportJumuiyasExcel'])->middleware('permission:sacraments.jumuiya.view')->name('sacraments.jumuiyas.export.excel');
    Route::get('/sacraments/jumuiyas/{jumuiya}/members/export/excel', [SacramentReportController::class, 'exportJumuiyaMembersExcel'])->middleware('permission:sacraments.jumuiya.view')->name('sacraments.jumuiyas.members.export.excel');

    Route::get('/jumuiyas/export/excel', [JumuiyaController::class, 'exportExcel'])->middleware('permission:jumuiyas.view')->name('jumuiyas.export.excel');

    Route::get('/jumuiyas/{jumuiya}/familias/export/excel', [JumuiyaController::class, 'exportFamiliasExcel'])
        ->middleware('permission:jumuiyas.view')
        ->name('jumuiyas.familias.export.excel');

    Route::get('/mafundisho/{type}/{year}/excel', [MafundishoEnrollmentController::class, 'exportTypeExcel'])
        ->middleware('permission:mafundisho.view')
        ->name('excel.mafundisho.type');

    Route::get('/familias/export/excel', [FamiliaController::class, 'exportExcel'])->middleware('permission:familias.view')->name('familias.export.excel');
    Route::get('/familias/{familia}/members/export/excel', [FamiliaMemberController::class, 'exportExcel'])->middleware('permission:familias.view')->name('familias.members.export.excel');
    Route::get('/jumuiyas/{jumuiya}/members/excel', [JumuiyaMemberController::class, 'exportExcel'])->middleware('permission:jumuiyas.members.view')->name('jumuiyas.members.excel');

    Route::get('/apostolic-groups/export/excel', [ApostolicGroupController::class, 'exportExcel'])->middleware('permission:apostolic-groups.view')->name('apostolic-groups.export.excel');
    Route::get('/apostolic-groups/{apostolicGroup}/members/excel', [ApostolicGroupController::class, 'exportMembersExcel'])->middleware('permission:apostolic-groups.view')->name('apostolic-groups.members.excel');
});