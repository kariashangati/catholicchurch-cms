<?php

namespace App\Http\Controllers\Admin\Receipts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Receipts\IssueReceiptsRequest;
use App\Http\Requests\Receipts\ReceiptFilterRequest;
use App\Http\Requests\Receipts\SendReceiptSmsRequest;
use App\Models\ReceiptIssue;
use App\Services\Receipts\ReceiptIssueService;
use App\Services\Receipts\ReceiptPdfService;
use App\Services\Receipts\ReceiptQueryService;
use App\Services\Receipts\ReceiptSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReceiptController extends Controller
{
    public function __construct(
        protected ReceiptQueryService $queryService,
        protected ReceiptIssueService $issueService,
        protected ReceiptPdfService $pdfService,
        protected ReceiptSmsService $smsService,
    ) {
    }

    public function index(ReceiptFilterRequest $request)
    {
        $filters = $request->validated();
        $rows = $this->queryService->pendingPaginator($filters, $request->user());
        $options = $this->queryService->options($request->user());

        $stats = [
            'total_rows' => $rows->total(),
            'pending_rows' => collect($rows->items())->whereNull('receipt')->count(),
            'total_amount' => collect($rows->items())->sum('amount'),
        ];

        return view('admin.receipts.index', array_merge($options, compact('filters', 'rows', 'stats')));
    }

    public function create(ReceiptFilterRequest $request)
    {
        $filters = $request->validated();
        $options = $this->queryService->options($request->user());

        return view('admin.receipts.create', array_merge($options, compact('filters')));
    }

    public function review(ReceiptFilterRequest $request)
    {
        $filters = $request->validated();
        $rows = $this->queryService->groupedRowsForIssue($filters, $request->user());
        $options = $this->queryService->options($request->user());
        $stats = [
            'rows_count' => $rows->count(),
            'total_amount' => $rows->sum('amount'),
        ];

        return view('admin.receipts.review', array_merge($options, compact('filters', 'rows', 'stats')));
    }

    public function issue(IssueReceiptsRequest $request)
    {
        $result = $this->issueService->issueFromFilters($request->validated(), $request->user());

        if (empty($result['pdf_path']) || ! is_file($result['pdf_path'])) {
            return redirect()->route('receipts.index')->with('warning', db_trans('no_pending_receipts_found'));
        }

        return response()->download($result['pdf_path'])->deleteFileAfterSend(false);
    }

    public function download(Request $request, ReceiptIssue $receipt)
    {
        abort_unless($request->user()?->can('finance.receipts.print'), 403);

        $receipt = $this->issueService->markPrinted($receipt, $request->user(), true);
        $path = $receipt->pdf_path && is_file($receipt->pdf_path)
            ? $receipt->pdf_path
            : $this->pdfService->renderSingle($receipt);

        return response()->download($path)->deleteFileAfterSend(false);
    }

    public function sendSms(SendReceiptSmsRequest $request, ReceiptIssue $receipt)
    {
        $this->smsService->send($receipt->load('member'), $request->user(), $request->input('phone'), $request->input('message'));

        return back()->with('success', db_trans('receipt_sms_sent_successfully'));
    }
}
