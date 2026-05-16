<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\StoreBudgetEstimateExpenseRequest;
use App\Http\Requests\Reports\StoreBudgetEstimateIncomeRequest;
use App\Http\Requests\Reports\UpdateBudgetEstimateExpenseRequest;
use App\Http\Requests\Reports\UpdateBudgetEstimateIncomeRequest;
use App\Models\BudgetEstimateExpense;
use App\Models\BudgetEstimateIncome;
use App\Services\Reports\BudgetEstimateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetEstimateController extends Controller
{
    public function __construct(protected BudgetEstimateService $service)
    {
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('reports.budgets.view'), 403);

        $year = (int) ($request->input('year') ?: now()->year);

        return view('admin.reports.budgets.index', $this->service->dashboardData($request->user(), $year));
    }

    public function storeIncome(StoreBudgetEstimateIncomeRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('reports.budgets.create'), 403);

        $this->service->createIncome($request->validated(), auth()->user());

        return back()->with('success', db_trans('budget_income_created_successfully'));
    }

    public function updateIncome(UpdateBudgetEstimateIncomeRequest $request, BudgetEstimateIncome $budgetEstimateIncome): RedirectResponse
    {
        abort_unless(auth()->user()?->can('reports.budgets.update'), 403);

        $this->service->updateIncome($budgetEstimateIncome, $request->validated(), auth()->user());

        return back()->with('success', db_trans('budget_income_updated_successfully'));
    }

    public function destroyIncome(BudgetEstimateIncome $budgetEstimateIncome): RedirectResponse
    {
        abort_unless(auth()->user()?->can('reports.budgets.delete'), 403);

        $budgetEstimateIncome->delete();

        return back()->with('success', db_trans('budget_income_deleted_successfully'));
    }

    public function storeExpense(StoreBudgetEstimateExpenseRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('reports.budgets.create'), 403);

        $this->service->createExpense($request->validated(), auth()->user());

        return back()->with('success', db_trans('budget_expense_created_successfully'));
    }

    public function updateExpense(UpdateBudgetEstimateExpenseRequest $request, BudgetEstimateExpense $budgetEstimateExpense): RedirectResponse
    {
        abort_unless(auth()->user()?->can('reports.budgets.update'), 403);

        $this->service->updateExpense($budgetEstimateExpense, $request->validated(), auth()->user());

        return back()->with('success', db_trans('budget_expense_updated_successfully'));
    }

    public function destroyExpense(BudgetEstimateExpense $budgetEstimateExpense): RedirectResponse
    {
        abort_unless(auth()->user()?->can('reports.budgets.delete'), 403);

        $budgetEstimateExpense->delete();

        return back()->with('success', db_trans('budget_expense_deleted_successfully'));
    }
}
