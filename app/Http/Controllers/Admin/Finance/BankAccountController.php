<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\BankAccountReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreBankAccountRequest;
use App\Http\Requests\Finance\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Services\Finance\BankAccountService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BankAccountController extends Controller
{
    public function __construct(private readonly BankAccountService $service)
    {
    }

    public function index(): View
    {
        abort_unless(request()->user()?->can('finance.bank-accounts.view'), 403);

        return view('admin.finance.bank-accounts.index', $this->service->indexData());
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(request()->user()?->can('finance.bank-accounts.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.bank-accounts-report',
            $this->service->exportData(),
            'bank-accounts'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(request()->user()?->can('finance.bank-accounts.view'), 403);

        return Excel::download(
            new BankAccountReportExport($this->service->exportRows()),
            'bank-accounts-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('finance.bank-accounts.index')
            ->with('success', db_trans('bank_account_created_successfully'));
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bank_account): RedirectResponse
    {
        $this->service->update($bank_account, $request->validated());

        return redirect()
            ->route('finance.bank-accounts.index')
            ->with('success', db_trans('bank_account_updated_successfully'));
    }

    public function destroy(BankAccount $bank_account): RedirectResponse
    {
        try {
            $this->service->delete($bank_account);

            return redirect()
                ->route('finance.bank-accounts.index')
                ->with('success', db_trans('bank_account_deleted_successfully'));
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('finance.bank-accounts.index')
                ->with('error', db_trans('bank_account_delete_blocked'));
        }
    }
}
