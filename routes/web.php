<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Finance\FinanceDashboardController;
use App\Http\Controllers\Admin\Finance\OfferingController;
use App\Http\Controllers\Admin\SacramentReportController;
use App\Http\Controllers\ApostolicGroupController;
use App\Http\Controllers\ApostolicGroupMemberController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\FamiliaMemberController;
use App\Http\Controllers\JumuiyaController;
use App\Http\Controllers\JumuiyaMemberController;
use App\Http\Controllers\Admin\Finance\TitheController;
use App\Http\Controllers\Admin\Finance\TitheDashboardController;
use App\Http\Controllers\Admin\Finance\ProjectFinanceController;
use App\Http\Controllers\Admin\Finance\ProjectFinanceDashboardController;
use App\Http\Controllers\Admin\Finance\BankContributionController;
use App\Http\Controllers\Admin\Finance\CashContributionController;
use App\Http\Controllers\Admin\Finance\ContributionDashboardController;
use App\Http\Controllers\Admin\Finance\ContributionTypeController;
use App\Http\Controllers\Admin\Finance\BudgetDashboardController;
use App\Http\Controllers\Admin\Finance\BudgetExpenseEstimateController;
use App\Http\Controllers\Admin\Finance\BudgetIncomeEstimateController;
use App\Http\Controllers\Admin\Reports\FinanceReportController;
use App\Http\Controllers\Admin\Reports\BudgetEstimateController;
use App\Http\Controllers\Admin\Leadership\LeadershipAssignmentController;
use App\Http\Controllers\Admin\Leadership\LeadershipDashboardController;
use App\Http\Controllers\Admin\Leadership\LeadershipPositionController;
use App\Http\Controllers\Admin\Reports\ReportDashboardController;
use App\Http\Controllers\Admin\Reports\SacramentLegacyReportController;
use App\Http\Controllers\Admin\Liturgy\MassTypeController;
use App\Http\Controllers\Admin\Liturgy\OfferingTypeController;
use App\Http\Controllers\Admin\Liturgy\MassScheduleController;
use App\Http\Controllers\Admin\Liturgy\MassScheduleAssignmentController;
use App\Http\Controllers\Admin\Finance\BankAccountController;
use App\Http\Controllers\Admin\Operations\AssetCategoryController;
use App\Http\Controllers\Admin\Operations\ChurchAssetController;
use App\Http\Controllers\Admin\Operations\ServiceCategoryController;
use App\Http\Controllers\Admin\Operations\ServiceProviderController;
use App\Http\Controllers\Admin\Membership\AgeGroupController;
use App\Http\Controllers\JumuiyaReportController;
use App\Http\Middleware\SetLocale;
use App\Http\Controllers\KandaController;
use App\Http\Controllers\KandaReportController;
use App\Http\Controllers\MafundishoEnrollmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Middleware\TrackVisitorActivity;
use App\Http\Controllers\Admin\Communication\CommunicationPreferenceController;
use App\Http\Controllers\Admin\Communication\CommunicationTemplateController;
use App\Http\Controllers\Admin\Communication\CommunicationAutomationController;

use App\Http\Controllers\Admin\Communication\CommunicationApprovalController;
use App\Http\Controllers\Admin\Communication\CommunicationControlSettingsController;

use App\Http\Controllers\Admin\Communication\CommunicationScheduleController;

use App\Http\Controllers\Admin\Communication\SmsMessageController;
use App\Http\Controllers\Admin\Communication\CommunicationDashboardController;
use App\Http\Controllers\Admin\Communication\CommunicationLogController;
use App\Http\Controllers\Admin\Communication\CommunicationBalanceController;



Route::get('/debug-auth-check', function () {
    return [
        'logged_in' => Auth::check(),
        'user_id' => Auth::id(),
        'url' => request()->fullUrl(),
    ];
});

Route::get('/admin/communication/open-test', function () {
    dd('PREFIX TEST WORKS');
});

Route::get('lang/{locale}', function ($locale) {
    abort_unless(in_array($locale, ['en', 'sw']), 400);

    session(['locale' => $locale]);

    if (Auth::check()) {
        Auth::user()->update([
            'locale' => $locale,
        ]);
    }

    return back();
})->name('lang.switch');

Route::middleware([SetLocale::class])->group(function () {
    Route::get('/', [LandingPageController::class, 'home'])
        ->middleware(TrackVisitorActivity::class)
        ->name('frontend.home');
    Route::get('/kanda', [LandingPageController::class, 'kandas'])->name('frontend.kandas');
    Route::get('/jumuiya', [LandingPageController::class, 'jumuiyas'])->name('frontend.jumuiyas');
    Route::get('/misa', [LandingPageController::class, 'masses'])->name('frontend.masses');
    Route::get('/matangazo', [LandingPageController::class, 'announcements'])->name('frontend.announcements');
    Route::get('/matangazo/{slug}', [LandingPageController::class, 'announcement'])->name('frontend.announcements.show');
    Route::get('/miradi', [LandingPageController::class, 'projects'])->name('frontend.projects');
    Route::get('/vikundi', [LandingPageController::class, 'ministries'])->name('frontend.ministries');
    Route::get('/uongozi', [LandingPageController::class, 'leadership'])->name('frontend.leadership');
    Route::get('/changia', [LandingPageController::class, 'giving'])->name('frontend.giving');
    Route::get('/gallery', [LandingPageController::class, 'gallery'])->name('frontend.gallery');
    Route::get('/contact', [LandingPageController::class, 'contact'])->name('frontend.contact');
Route::get('/historia_leo', [LandingPageController::class, 'history'])->name('frontend.history');
Route::get('/historia_leo/{slug}', [LandingPageController::class, 'historyShow'])->name('frontend.history.show');
Route::post('/contact/submit', [LandingPageController::class, 'submitContact'])
    ->name('frontend.contact.submit');
    Route::get('/pages/{slug}', [LandingPageController::class, 'page'])->name('frontend.page');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
	
	








Route::prefix('admin/communication')->name('admin.communication.')->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [CommunicationDashboardController::class, 'index'])
        ->middleware('permission:communication.view')
        ->name('dashboard');

    // ================= SMS =================
    Route::get('/sms', [SmsMessageController::class, 'index'])
        ->middleware('permission:communication.send')
        ->name('sms.index');

    Route::get('/sms/create', [SmsMessageController::class, 'create'])
        ->middleware('permission:communication.send')
        ->name('sms.create');

    Route::post('/sms/preview', [SmsMessageController::class, 'preview'])
        ->middleware('permission:communication.send')
        ->name('sms.preview');

    Route::post('/sms/store', [SmsMessageController::class, 'store'])
        ->middleware('permission:communication.send')
        ->name('sms.store');

    // ================= TEMPLATES =================
    Route::post('/templates/preview', [CommunicationTemplateController::class, 'preview'])
        ->middleware('permission:communication.manage_templates')
        ->name('templates.preview');

    Route::resource('templates', CommunicationTemplateController::class)
        ->middleware('permission:communication.manage_templates');

    // ================= AUTOMATIONS =================
    Route::get('automations/schema/{eventKey}', [CommunicationAutomationController::class, 'schema'])
        ->middleware('permission:communication.manage_automations')
        ->name('automations.schema');

    Route::patch('automations/{automation}/toggle', [CommunicationAutomationController::class, 'toggle'])
        ->middleware('permission:communication.manage_automations')
        ->name('automations.toggle');

    Route::resource('automations', CommunicationAutomationController::class)
        ->except(['show'])
        ->middleware('permission:communication.manage_automations');

    // ================= SCHEDULING =================
    Route::get('/schedules', [CommunicationScheduleController::class, 'index'])
        ->middleware('permission:communication.schedule')
        ->name('schedules.index');

    Route::post('/campaigns/{campaign}/schedule', [CommunicationScheduleController::class, 'schedule'])
        ->middleware('permission:communication.schedule')
        ->name('campaigns.schedule');

    Route::patch('/campaigns/{campaign}/reschedule', [CommunicationScheduleController::class, 'reschedule'])
        ->middleware('permission:communication.schedule')
        ->name('campaigns.reschedule');

    Route::patch('/campaigns/{campaign}/cancel-schedule', [CommunicationScheduleController::class, 'cancel'])
        ->middleware('permission:communication.schedule')
        ->name('campaigns.cancel_schedule');

    // ================= PREFERENCES =================
    Route::get('preferences', [CommunicationPreferenceController::class, 'index'])
        ->middleware('permission:communication.manage_preferences')
        ->name('preferences.index');

    Route::get('preferences/{member}/edit', [CommunicationPreferenceController::class, 'edit'])
        ->middleware('permission:communication.manage_preferences')
        ->name('preferences.edit');

    Route::put('preferences/{member}', [CommunicationPreferenceController::class, 'update'])
        ->middleware('permission:communication.manage_preferences')
        ->name('preferences.update');

    Route::post('preferences/{member}/opt-out', [CommunicationPreferenceController::class, 'optOut'])
        ->middleware('permission:communication.manage_preferences')
        ->name('preferences.opt-out');

    Route::post('preferences/{member}/opt-in', [CommunicationPreferenceController::class, 'optIn'])
        ->middleware('permission:communication.manage_preferences')
        ->name('preferences.opt-in');

    // ================= APPROVALS =================
    Route::get('/approvals', [CommunicationApprovalController::class, 'index'])
        ->middleware('permission:communication.approve_campaigns')
        ->name('approvals.index');

    Route::post('/approvals/{campaign}/approve', [CommunicationApprovalController::class, 'approve'])
        ->middleware('permission:communication.approve_campaigns')
        ->name('approvals.approve');

    Route::post('/approvals/{campaign}/reject', [CommunicationApprovalController::class, 'reject'])
        ->middleware('permission:communication.approve_campaigns')
        ->name('approvals.reject');

    Route::post('/approvals/{campaign}/cancel', [CommunicationApprovalController::class, 'cancel'])
        ->middleware('permission:communication.cancel_campaigns')
        ->name('approvals.cancel');

    Route::post('/approvals/{campaign}/retry-failed', [CommunicationApprovalController::class, 'retryFailed'])
        ->middleware('permission:communication.retry_failed')
        ->name('approvals.retry_failed');

    // ================= CONTROLS =================
    Route::get('/controls', [CommunicationControlSettingsController::class, 'edit'])
        ->middleware('permission:communication.manage_controls')
        ->name('controls.edit');

    Route::put('/controls', [CommunicationControlSettingsController::class, 'update'])
        ->middleware('permission:communication.manage_controls')
        ->name('controls.update');

    // ================= LOGS =================
    Route::get('/logs', [CommunicationLogController::class, 'index'])
        ->middleware('permission:communication.view_logs')
        ->name('logs.index');

    // ================= BALANCE =================
    Route::get('/balance', [CommunicationBalanceController::class, 'index'])
        ->middleware('permission:communication.manage_balance')
        ->name('balance.index');




});






Route::prefix('operations')->name('operations.')->group(function () {
    Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->middleware('permission:operations.service-categories.view')->name('service-categories.index');
    Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->middleware('permission:operations.service-categories.create')->name('service-categories.store');
    Route::put('/service-categories/{service_category}', [ServiceCategoryController::class, 'update'])->middleware('permission:operations.service-categories.update')->name('service-categories.update');
    Route::delete('/service-categories/{service_category}', [ServiceCategoryController::class, 'destroy'])->middleware('permission:operations.service-categories.delete')->name('service-categories.destroy');

    Route::get('/service-providers', [ServiceProviderController::class, 'index'])->middleware('permission:operations.service-providers.view')->name('service-providers.index');
    Route::post('/service-providers', [ServiceProviderController::class, 'store'])->middleware('permission:operations.service-providers.create')->name('service-providers.store');
    Route::put('/service-providers/{service_provider}', [ServiceProviderController::class, 'update'])->middleware('permission:operations.service-providers.update')->name('service-providers.update');
    Route::delete('/service-providers/{service_provider}', [ServiceProviderController::class, 'destroy'])->middleware('permission:operations.service-providers.delete')->name('service-providers.destroy');

    Route::get('/asset-categories', [AssetCategoryController::class, 'index'])->middleware('permission:operations.asset-categories.view')->name('asset-categories.index');
    Route::post('/asset-categories', [AssetCategoryController::class, 'store'])->middleware('permission:operations.asset-categories.create')->name('asset-categories.store');
    Route::put('/asset-categories/{asset_category}', [AssetCategoryController::class, 'update'])->middleware('permission:operations.asset-categories.update')->name('asset-categories.update');
    Route::delete('/asset-categories/{asset_category}', [AssetCategoryController::class, 'destroy'])->middleware('permission:operations.asset-categories.delete')->name('asset-categories.destroy');

    Route::get('/church-assets', [ChurchAssetController::class, 'index'])->middleware('permission:operations.church-assets.view')->name('church-assets.index');
    Route::post('/church-assets', [ChurchAssetController::class, 'store'])->middleware('permission:operations.church-assets.create')->name('church-assets.store');
    Route::put('/church-assets/{church_asset}', [ChurchAssetController::class, 'update'])->middleware('permission:operations.church-assets.update')->name('church-assets.update');
    Route::delete('/church-assets/{church_asset}', [ChurchAssetController::class, 'destroy'])->middleware('permission:operations.church-assets.delete')->name('church-assets.destroy');
});
	
	
	Route::prefix('liturgy')->name('liturgy.')->group(function () {

    Route::get('/mass-types', [MassTypeController::class, 'index'])
        ->middleware('permission:liturgy.mass-types.view')
        ->name('mass-types.index');
    Route::post('/mass-types', [MassTypeController::class, 'store'])
        ->middleware('permission:liturgy.mass-types.create')
        ->name('mass-types.store');
    Route::put('/mass-types/{mass_type}', [MassTypeController::class, 'update'])
        ->middleware('permission:liturgy.mass-types.update')
        ->name('mass-types.update');
    Route::delete('/mass-types/{mass_type}', [MassTypeController::class, 'destroy'])
        ->middleware('permission:liturgy.mass-types.delete')
        ->name('mass-types.destroy');

    Route::get('/offering-types', [OfferingTypeController::class, 'index'])
        ->middleware('permission:liturgy.offering-types.view')
        ->name('offering-types.index');
    Route::post('/offering-types', [OfferingTypeController::class, 'store'])
        ->middleware('permission:liturgy.offering-types.create')
        ->name('offering-types.store');
    Route::put('/offering-types/{offering_type}', [OfferingTypeController::class, 'update'])
        ->middleware('permission:liturgy.offering-types.update')
        ->name('offering-types.update');
    Route::delete('/offering-types/{offering_type}', [OfferingTypeController::class, 'destroy'])
        ->middleware('permission:liturgy.offering-types.delete')
        ->name('offering-types.destroy');

    Route::get('/mass-schedules', [MassScheduleController::class, 'index'])
        ->middleware('permission:liturgy.mass-schedules.view')
        ->name('mass-schedules.index');
    Route::post('/mass-schedules', [MassScheduleController::class, 'store'])
        ->middleware('permission:liturgy.mass-schedules.create')
        ->name('mass-schedules.store');
    Route::put('/mass-schedules/{mass_schedule}', [MassScheduleController::class, 'update'])
        ->middleware('permission:liturgy.mass-schedules.update')
        ->name('mass-schedules.update');
    Route::delete('/mass-schedules/{mass_schedule}', [MassScheduleController::class, 'destroy'])
        ->middleware('permission:liturgy.mass-schedules.delete')
        ->name('mass-schedules.destroy');

    Route::post('/mass-schedule-assignments', [MassScheduleAssignmentController::class, 'store'])
        ->middleware('permission:liturgy.mass-schedules.update')
        ->name('mass-schedule-assignments.store');
    Route::put('/mass-schedule-assignments/{mass_schedule_assignment}', [MassScheduleAssignmentController::class, 'update'])
        ->middleware('permission:liturgy.mass-schedules.update')
        ->name('mass-schedule-assignments.update');
    Route::delete('/mass-schedule-assignments/{mass_schedule_assignment}', [MassScheduleAssignmentController::class, 'destroy'])
        ->middleware('permission:liturgy.mass-schedules.delete')
        ->name('mass-schedule-assignments.destroy');
});
	




Route::prefix('membership')->name('membership.')->group(function () {

        Route::get('/age-groups', [AgeGroupController::class, 'index'])
            ->middleware('permission:membership.age-groups.view')
            ->name('age-groups.index');

        Route::post('/age-groups', [AgeGroupController::class, 'store'])
            ->middleware('permission:membership.age-groups.create')
            ->name('age-groups.store');

        Route::put('/age-groups/{age_group}', [AgeGroupController::class, 'update'])
            ->middleware('permission:membership.age-groups.update')
            ->name('age-groups.update');

        Route::delete('/age-groups/{age_group}', [AgeGroupController::class, 'destroy'])
            ->middleware('permission:membership.age-groups.delete')
            ->name('age-groups.destroy');
    });




Route::prefix('finance')->name('finance.')->group(function () {


        Route::resource('bank-accounts', BankAccountController::class)
            ->except(['create', 'show', 'edit'])
            ->names('bank-accounts');
    });

	
	
	Route::prefix('reports')->name('reports.')->group(function () {
		
	    Route::get('/', [ReportDashboardController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('index');

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceReportController::class, 'index'])
            ->middleware('permission:reports.finance.view')
            ->name('index');

        Route::get('/compliance', [FinanceReportController::class, 'compliance'])
            ->middleware('permission:reports.finance.compliance.view')
            ->name('compliance');

        Route::get('/compliance/csv', [FinanceReportController::class, 'complianceCsv'])
            ->middleware('permission:reports.finance.compliance.export')
            ->name('compliance.csv');
    });

    Route::prefix('sacraments')->name('sacraments.')->group(function () {
        Route::get('/', [SacramentLegacyReportController::class, 'index'])
            ->middleware('permission:reports.sacraments.view')
            ->name('index');

        Route::post('/generate', [SacramentLegacyReportController::class, 'generate'])
            ->middleware('permission:reports.sacraments.view')
            ->name('generate');
    });

    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetEstimateController::class, 'index'])
            ->middleware('permission:reports.budgets.view')
            ->name('index');

        Route::post('/income', [BudgetEstimateController::class, 'storeIncome'])
            ->middleware('permission:reports.budgets.create')
            ->name('income.store');
        Route::put('/income/{budgetEstimateIncome}', [BudgetEstimateController::class, 'updateIncome'])
            ->middleware('permission:reports.budgets.update')
            ->name('income.update');
        Route::delete('/income/{budgetEstimateIncome}', [BudgetEstimateController::class, 'destroyIncome'])
            ->middleware('permission:reports.budgets.delete')
            ->name('income.destroy');

        Route::post('/expense', [BudgetEstimateController::class, 'storeExpense'])
            ->middleware('permission:reports.budgets.create')
            ->name('expense.store');
        Route::put('/expense/{budgetEstimateExpense}', [BudgetEstimateController::class, 'updateExpense'])
            ->middleware('permission:reports.budgets.update')
            ->name('expense.update');
        Route::delete('/expense/{budgetEstimateExpense}', [BudgetEstimateController::class, 'destroyExpense'])
            ->middleware('permission:reports.budgets.delete')
            ->name('expense.destroy');
    });
});
	
	
	
	
	
	Route::prefix('leadership')->name('leadership.')->group(function () {
    Route::get('/dashboard', [LeadershipDashboardController::class, 'index'])->middleware('permission:leadership.dashboard.view')->name('dashboard');
    Route::get('/positions', [LeadershipPositionController::class, 'index'])->middleware('permission:leadership.positions.view')->name('positions.index');
    Route::post('/positions', [LeadershipPositionController::class, 'store'])->middleware('permission:leadership.positions.create')->name('positions.store');
    Route::put('/positions/{position}', [LeadershipPositionController::class, 'update'])->middleware('permission:leadership.positions.update')->name('positions.update');
    Route::delete('/positions/{position}', [LeadershipPositionController::class, 'destroy'])->middleware('permission:leadership.positions.delete')->name('positions.destroy');
    Route::get('/assignments', [LeadershipAssignmentController::class, 'index'])->middleware('permission:leadership.assignments.view')->name('assignments.index');
    Route::post('/assignments', [LeadershipAssignmentController::class, 'store'])->middleware('permission:leadership.assignments.create')->name('assignments.store');
    Route::put('/assignments/{assignment}', [LeadershipAssignmentController::class, 'update'])->middleware('permission:leadership.assignments.update')->name('assignments.update');
    Route::delete('/assignments/{assignment}', [LeadershipAssignmentController::class, 'destroy'])->middleware('permission:leadership.assignments.delete')->name('assignments.destroy');
});
	
Route::prefix('finance')->name('finance.')->group(function () {
	
	    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/financial-summary', [FinanceReportController::class, 'financialSummary'])->middleware('permission:finance.reports.summary.view')->name('financial-summary');
        Route::get('/income-breakdown', [FinanceReportController::class, 'incomeBreakdown'])->middleware('permission:finance.reports.summary.view')->name('income-breakdown');
        Route::get('/expense-breakdown', [FinanceReportController::class, 'expenseBreakdown'])->middleware('permission:finance.reports.summary.view')->name('expense-breakdown');
        Route::get('/contribution-compliance', [FinanceReportController::class, 'compliance'])->middleware('permission:finance.reports.compliance.view')->name('compliance');
    });

    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetDashboardController::class, 'index'])->middleware('permission:finance.budgets.view')->name('dashboard');
        Route::get('/income', [BudgetIncomeEstimateController::class, 'index'])->middleware('permission:finance.budgets.income.view')->name('income.index');
        Route::post('/income', [BudgetIncomeEstimateController::class, 'store'])->middleware('permission:finance.budgets.income.create')->name('income.store');
        Route::put('/income/{budgetIncomeEstimate}', [BudgetIncomeEstimateController::class, 'update'])->middleware('permission:finance.budgets.income.update')->name('income.update');
        Route::delete('/income/{budgetIncomeEstimate}', [BudgetIncomeEstimateController::class, 'destroy'])->middleware('permission:finance.budgets.income.delete')->name('income.destroy');
        Route::get('/expense', [BudgetExpenseEstimateController::class, 'index'])->middleware('permission:finance.budgets.expense.view')->name('expense.index');
        Route::post('/expense', [BudgetExpenseEstimateController::class, 'store'])->middleware('permission:finance.budgets.expense.create')->name('expense.store');
        Route::put('/expense/{budgetExpenseEstimate}', [BudgetExpenseEstimateController::class, 'update'])->middleware('permission:finance.budgets.expense.update')->name('expense.update');
        Route::delete('/expense/{budgetExpenseEstimate}', [BudgetExpenseEstimateController::class, 'destroy'])->middleware('permission:finance.budgets.expense.delete')->name('expense.destroy');
    });
	
	Route::prefix('contributions')->name('contributions.')->group(function () {
        Route::get('/dashboard', [ContributionDashboardController::class, 'index'])
            ->middleware('permission:finance.contributions.dashboard.view')
            ->name('dashboard');

        Route::get('/types', [ContributionTypeController::class, 'index'])
            ->middleware('permission:finance.contributions.types.view')
            ->name('types.index');
        Route::post('/types', [ContributionTypeController::class, 'store'])
            ->middleware('permission:finance.contributions.types.create')
            ->name('types.store');
        Route::put('/types/{type}', [ContributionTypeController::class, 'update'])
            ->middleware('permission:finance.contributions.types.update')
            ->name('types.update');
        Route::delete('/types/{type}', [ContributionTypeController::class, 'destroy'])
            ->middleware('permission:finance.contributions.types.delete')
            ->name('types.destroy');

        Route::get('/cash', [CashContributionController::class, 'index'])
            ->middleware('permission:finance.contributions.cash.view')
            ->name('cash.index');
        Route::post('/cash', [CashContributionController::class, 'store'])
            ->middleware('permission:finance.contributions.cash.create')
            ->name('cash.store');
        Route::put('/cash/{cashContribution}', [CashContributionController::class, 'update'])
            ->middleware('permission:finance.contributions.cash.update')
            ->name('cash.update');
        Route::delete('/cash/{cashContribution}', [CashContributionController::class, 'destroy'])
            ->middleware('permission:finance.contributions.cash.delete')
            ->name('cash.destroy');

        Route::get('/bank', [BankContributionController::class, 'index'])
            ->middleware('permission:finance.contributions.bank.view')
            ->name('bank.index');
        Route::post('/bank', [BankContributionController::class, 'store'])
            ->middleware('permission:finance.contributions.bank.create')
            ->name('bank.store');
        Route::put('/bank/{bankContribution}', [BankContributionController::class, 'update'])
            ->middleware('permission:finance.contributions.bank.update')
            ->name('bank.update');
        Route::delete('/bank/{bankContribution}', [BankContributionController::class, 'destroy'])
            ->middleware('permission:finance.contributions.bank.delete')
            ->name('bank.destroy');
    });
	
	
	    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/dashboard', [ProjectFinanceDashboardController::class, 'index'])
            ->middleware('permission:finance.projects.view')
            ->name('dashboard');

        Route::get('/', [ProjectFinanceController::class, 'index'])
            ->middleware('permission:finance.projects.view')
            ->name('index');

        Route::post('/categories', [ProjectFinanceController::class, 'storeCategory'])
            ->middleware('permission:finance.projects.create')
            ->name('categories.store');

        Route::put('/categories/{projectCategory}', [ProjectFinanceController::class, 'updateCategory'])
            ->middleware('permission:finance.projects.update')
            ->name('categories.update');

        Route::delete('/categories/{projectCategory}', [ProjectFinanceController::class, 'destroyCategory'])
            ->middleware('permission:finance.projects.delete')
            ->name('categories.destroy');

        Route::post('/', [ProjectFinanceController::class, 'storeProject'])
            ->middleware('permission:finance.projects.create')
            ->name('store');

        Route::put('/{project}', [ProjectFinanceController::class, 'updateProject'])
            ->middleware('permission:finance.projects.update')
            ->name('update');

        Route::delete('/{project}', [ProjectFinanceController::class, 'destroyProject'])
            ->middleware('permission:finance.projects.delete')
            ->name('destroy');

        Route::post('/transactions', [ProjectFinanceController::class, 'storeTransaction'])
            ->middleware('permission:finance.projects.transactions.create')
            ->name('transactions.store');

        Route::put('/transactions/{projectTransaction}', [ProjectFinanceController::class, 'updateTransaction'])
            ->middleware('permission:finance.projects.transactions.update')
            ->name('transactions.update');

        Route::delete('/transactions/{projectTransaction}', [ProjectFinanceController::class, 'destroyTransaction'])
            ->middleware('permission:finance.projects.transactions.delete')
            ->name('transactions.destroy');
    });
	
	
    Route::prefix('tithes')->name('tithes.')->group(function () {
		
		
		
		
		
		
		
		
		
		
		



Route::get('/dashboard', [TitheDashboardController::class, 'index'])
    ->middleware('permission:finance.tithes.view')
    ->name('dashboard');

Route::get('/', [TitheController::class, 'index'])
    ->middleware('permission:finance.tithes.view')
    ->name('index');

Route::get('/bulk-entry', [TitheController::class, 'bulkEntry'])
    ->middleware('permission:finance.tithes.bulk')
    ->name('bulk.entry');

Route::post('/bulk-preview', [TitheController::class, 'bulkPreview'])
    ->middleware('permission:finance.tithes.bulk')
    ->name('bulk.preview');

Route::get('/duplicates', [TitheController::class, 'duplicates'])
    ->middleware('permission:finance.tithes.duplicates.view')
    ->name('duplicates.index');

Route::get('/monthly/{month}', [TitheController::class, 'monthly'])
    ->middleware('permission:finance.tithes.view')
    ->whereNumber('month')
    ->name('monthly');

Route::get('/jumuiyas/{jumuiya}', [TitheController::class, 'showJumuiya'])
    ->middleware('permission:finance.tithes.view')
    ->name('jumuiya.show');

Route::get('/jumuiyas/{jumuiya}/matrix/{year?}', [TitheController::class, 'matrix'])
    ->middleware('permission:finance.tithes.view')
    ->whereNumber('year')
    ->name('jumuiya.matrix');

Route::post('/', [TitheController::class, 'store'])
    ->middleware('permission:finance.tithes.create')
    ->name('store');

Route::post('/bulk', [TitheController::class, 'bulkStore'])
    ->middleware('permission:finance.tithes.bulk')
    ->name('bulk.store');

Route::put('/{tithe}', [TitheController::class, 'update'])
    ->middleware('permission:finance.tithes.update')
    ->name('update');

Route::delete('/{tithe}', [TitheController::class, 'destroy'])
    ->middleware('permission:finance.tithes.delete')
    ->name('destroy');

Route::patch('/members/{member}/bahasha', [TitheController::class, 'updateBahasha'])
    ->middleware('permission:finance.tithes.update')
    ->name('bahasha.update');
    });
});


 

    // Sacrament Reports
    Route::prefix('sacraments')->name('sacraments.')->group(function () {
        Route::get('/', [SacramentReportController::class, 'index'])
            ->middleware('permission:sacraments.view')
            ->name('index');

        Route::get('/kandas', [SacramentReportController::class, 'kandas'])
            ->middleware('permission:sacraments.kanda.view')
            ->name('kandas');

        Route::get('/kandas/{kanda}', [SacramentReportController::class, 'showKanda'])
            ->middleware('permission:sacraments.detail.view')
            ->name('kandas.show');

        Route::get('/jumuiyas', [SacramentReportController::class, 'jumuiyas'])
            ->middleware('permission:sacraments.jumuiya.view')
            ->name('jumuiyas');

        Route::get('/jumuiyas/{jumuiya}', [SacramentReportController::class, 'showJumuiya'])
            ->middleware('permission:sacraments.detail.view')
            ->name('jumuiyas.show');
    });

    // Finance
    Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceDashboardController::class, 'index'])
        ->middleware('permission:finance.view')
        ->name('dashboard');

    Route::get('/offerings', [OfferingController::class, 'index'])
        ->middleware('permission:finance.view')
        ->name('offerings.index');

    Route::post('/offerings', [OfferingController::class, 'store'])
        ->middleware('permission:finance.create')
        ->name('offerings.store');

    Route::put('/offerings/{offering}', [OfferingController::class, 'update'])
        ->middleware('permission:finance.update')
        ->name('offerings.update');

    Route::delete('/offerings/{offering}', [OfferingController::class, 'destroy'])
        ->middleware('permission:finance.delete')
        ->name('offerings.destroy');
    });

    // Familias
    Route::get('/familias', [FamiliaController::class, 'index'])
        ->middleware('permission:familias.view')
        ->name('familias.index');

    Route::post('/familias', [FamiliaController::class, 'store'])
        ->middleware('permission:familias.create')
        ->name('familias.store');

    Route::get('/familias/{familia}', [FamiliaController::class, 'show'])
        ->middleware('permission:familias.view')
        ->name('familias.show');

    Route::put('/familias/{familia}', [FamiliaController::class, 'update'])
        ->middleware('permission:familias.update')
        ->name('familias.update');

    Route::delete('/familias/{familia}', [FamiliaController::class, 'destroy'])
        ->middleware('permission:familias.delete')
        ->name('familias.destroy');

    Route::post('/familias/{familia}/members', [FamiliaMemberController::class, 'store'])
        ->middleware('permission:familias.members.create')
        ->name('familias.members.store');

    Route::put('/familias/{familia}/members/{member}', [FamiliaMemberController::class, 'update'])
        ->middleware('permission:familias.members.update')
        ->name('familias.members.update');

    Route::delete('/familias/{familia}/members/{member}', [FamiliaMemberController::class, 'destroy'])
        ->middleware('permission:familias.members.delete')
        ->name('familias.members.destroy');

    // Apostolic Groups
    Route::get('/apostolic-groups', [ApostolicGroupController::class, 'index'])
        ->middleware('permission:apostolic-groups.view')
        ->name('apostolic-groups.index');

    Route::post('/apostolic-groups', [ApostolicGroupController::class, 'store'])
        ->middleware('permission:apostolic-groups.create')
        ->name('apostolic-groups.store');

    Route::get('/apostolic-groups/{apostolicGroup}', [ApostolicGroupController::class, 'show'])
        ->middleware('permission:apostolic-groups.view')
        ->name('apostolic-groups.show');

    Route::put('/apostolic-groups/{apostolicGroup}', [ApostolicGroupController::class, 'update'])
        ->middleware('permission:apostolic-groups.update')
        ->name('apostolic-groups.update');

    Route::delete('/apostolic-groups/{apostolicGroup}', [ApostolicGroupController::class, 'destroy'])
        ->middleware('permission:apostolic-groups.delete')
        ->name('apostolic-groups.destroy');

    Route::post('/apostolic-groups/{apostolicGroup}/sync-rule', [ApostolicGroupController::class, 'syncRuleMembers'])
        ->middleware('permission:apostolic-groups.sync-rule')
        ->name('apostolic-groups.sync-rule');

    Route::post('/apostolic-groups/{apostolicGroup}/members', [ApostolicGroupMemberController::class, 'store'])
        ->middleware('permission:apostolic-groups.members.create')
        ->name('apostolic-groups.members.store');

    Route::put('/apostolic-groups/{apostolicGroup}/members/{groupMember}', [ApostolicGroupMemberController::class, 'update'])
        ->middleware('permission:apostolic-groups.members.update')
        ->name('apostolic-groups.members.update');

    Route::delete('/apostolic-groups/{apostolicGroup}/members/{groupMember}', [ApostolicGroupMemberController::class, 'destroy'])
        ->middleware('permission:apostolic-groups.members.delete')
        ->name('apostolic-groups.members.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Members
    Route::get('/members', [MemberController::class, 'index'])
        ->middleware('permission:members.view')
        ->name('members.index');

    Route::get('/members/create', [MemberController::class, 'create'])
        ->middleware('permission:members.create')
        ->name('members.create');

    Route::post('/members', [MemberController::class, 'store'])
        ->middleware('permission:members.create')
        ->name('members.store');

    Route::get('/members/{member}', [MemberController::class, 'show'])
        ->middleware('permission:members.view')
        ->name('members.show');

    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])
        ->middleware('permission:members.update')
        ->name('members.edit');

    Route::put('/members/{member}', [MemberController::class, 'update'])
        ->middleware('permission:members.update')
        ->name('members.update');

    Route::delete('/members/{member}', [MemberController::class, 'destroy'])
        ->middleware('permission:members.delete')
        ->name('members.destroy');

    // Kandas
    Route::get('/kandas', [KandaController::class, 'index'])
        ->middleware('permission:kandas.view')
        ->name('kandas.index');

    Route::get('/kandas/create', [KandaController::class, 'create'])
        ->middleware('permission:kandas.create')
        ->name('kandas.create');

    Route::post('/kandas', [KandaController::class, 'store'])
        ->middleware('permission:kandas.create')
        ->name('kandas.store');

    Route::get('/kandas/{kanda}', [KandaController::class, 'show'])
        ->middleware('permission:kandas.view')
        ->name('kandas.show');

    Route::get('/kandas/{kanda}/edit', [KandaController::class, 'edit'])
        ->middleware('permission:kandas.update')
        ->name('kandas.edit');

    Route::put('/kandas/{kanda}', [KandaController::class, 'update'])
        ->middleware('permission:kandas.update')
        ->name('kandas.update');

    Route::delete('/kandas/{kanda}', [KandaController::class, 'destroy'])
        ->middleware('permission:kandas.delete')
        ->name('kandas.destroy');

    // Kanda Financial Reports
    Route::get('/kanda-reports', [KandaReportController::class, 'index'])
        ->middleware('permission:kanda-reports.view')
        ->name('kanda-reports.index');

    Route::get('/kanda-reports/{kanda}', [KandaReportController::class, 'show'])
        ->middleware('permission:kanda-reports.view')
        ->name('kanda-reports.show');

    // Jumuiyas
    Route::get('/jumuiyas', [JumuiyaController::class, 'index'])
        ->middleware('permission:jumuiyas.view')
        ->name('jumuiyas.index');

    Route::get('/jumuiyas/{jumuiya}', [JumuiyaController::class, 'show'])
        ->middleware('permission:jumuiyas.view')
        ->name('jumuiyas.show');

    Route::post('/jumuiyas', [JumuiyaController::class, 'store'])
        ->middleware('permission:jumuiyas.create')
        ->name('jumuiyas.store');

    Route::get('/jumuiyas/{jumuiya}/edit', [JumuiyaController::class, 'edit'])
        ->middleware('permission:jumuiyas.update')
        ->name('jumuiyas.edit');

    Route::put('/jumuiyas/{jumuiya}', [JumuiyaController::class, 'update'])
        ->middleware('permission:jumuiyas.update')
        ->name('jumuiyas.update');

    Route::delete('/jumuiyas/{jumuiya}', [JumuiyaController::class, 'destroy'])
        ->middleware('permission:jumuiyas.delete')
        ->name('jumuiyas.destroy');

    Route::get('/jumuiyas/{jumuiya}/members', [JumuiyaMemberController::class, 'index'])
        ->middleware('permission:jumuiyas.members.view')
        ->name('jumuiyas.members.index');

    // Jumuiya Financial Reports
    Route::get('/jumuiya-reports', [JumuiyaReportController::class, 'index'])
        ->middleware('permission:jumuiya-reports.view')
        ->name('jumuiya-reports.index');

    Route::get('/jumuiya-reports/{jumuiya}', [JumuiyaReportController::class, 'show'])
        ->middleware('permission:jumuiya-reports.view')
        ->name('jumuiya-reports.show');

    // Translations
    Route::get('/translations', [TranslationController::class, 'index'])
        ->middleware('role:Super Admin')
        ->name('translations.index');

    Route::get('/translations/create', [TranslationController::class, 'create'])
        ->middleware('role:Super Admin')
        ->name('translations.create');

    Route::post('/translations', [TranslationController::class, 'store'])
        ->middleware('role:Super Admin')
        ->name('translations.store');

    Route::get('/translations/{translation}/edit', [TranslationController::class, 'edit'])
        ->middleware('role:Super Admin')
        ->name('translations.edit');

    Route::put('/translations/{translation}', [TranslationController::class, 'update'])
        ->middleware('role:Super Admin')
        ->name('translations.update');

    Route::delete('/translations/{translation}', [TranslationController::class, 'destroy'])
        ->middleware('role:Super Admin')
        ->name('translations.destroy');
});

require __DIR__ . '/auth.php';

require __DIR__ . '/audit.php';
require __DIR__ . '/access_control.php';
require __DIR__ . '/system_config.php';
require __DIR__ . '/cms.php';
require __DIR__ . '/pdf.php';
require __DIR__ . '/excel.php';
require __DIR__ . '/mafundisho.php';
require __DIR__.'/tithes.php';
require __DIR__ . '/bulkcontribution.php';
require __DIR__ . '/finance_report.php';
require __DIR__ . '/reports.php';
require __DIR__ . '/translations.php';
require __DIR__ . '/receipts.php';
require __DIR__ . '/communication.php';

require __DIR__ . '/theme.php';
require __DIR__.'/booking.php';
require __DIR__.'/sermons.php';
 require __DIR__.'/contact-messages.php';