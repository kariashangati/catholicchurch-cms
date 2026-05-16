<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Receipts\IssueReceiptsRequest;
use App\Http\Requests\Receipts\ReceiptFilterRequest;
use App\Http\Requests\Receipts\SendReceiptSmsRequest;
use App\Models\ReceiptIssue;
use App\Services\Receipts\ReceiptIssueService;
use App\Services\Receipts\ReceiptPdfService;
use App\Services\Receipts\ReceiptQueryService;
use App\Services\Receipts\ReceiptQrCodeService;
use App\Services\Receipts\ReceiptSmsService;
use App\Services\Receipts\ReceiptVerificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    public function __construct(
        protected ReceiptQueryService $queryService,
        protected ReceiptIssueService $issueService,
        protected ReceiptPdfService $pdfService,
        protected ReceiptSmsService $smsService,
        protected ReceiptQrCodeService $qrCodeService,
        protected ReceiptVerificationService $verificationService,
    ) {
    }

    public function index(ReceiptFilterRequest $request)
    {
        $filters = $request->validated();
        $rows = $this->queryService->pendingPaginator($filters, $request->user());
        $options = $this->queryService->options($request->user());

        $pageItems = collect($rows->items());
        $stats = [
            'total_rows' => $rows->total(),
            'pending_rows' => $pageItems->whereNull('receipt')->count(),
            'total_amount' => $pageItems->sum('amount'),
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

    public function store(IssueReceiptsRequest $request)
    {
        $result = $this->issueService->issueFromFilters($request->validated(), $request->user());

        if (empty($result['pdf_path']) || ! is_file($result['pdf_path'])) {
            return redirect()->route('receipts.index')->with('warning', db_trans('no_pending_receipts_found'));
        }

        return response()->download($result['pdf_path'])->deleteFileAfterSend(false);
    }

    public function history(ReceiptFilterRequest $request)
    {
        $filters = $request->validated();

        return view('admin.finance.receipts.history', [
            'filters' => $filters,
            'receipts' => $this->queryService->history($filters, $request->user()),
            'statusOptions' => [],
        ]);
    }

    public function pending(ReceiptFilterRequest $request)
    {
        $filters = $request->validated();

        return view('admin.finance.receipts.pending', [
            'filters' => $filters,
            'pendingReceipts' => $this->queryService->pending($filters, $request->user()),
            'pendingCounts' => $this->queryService->pendingCounts($filters, $request->user()),
        ]);
    }

    public function exceptions(Request $request)
    {
        return view('admin.finance.receipts.exceptions', [
            'exceptions' => $this->queryService->exceptions($request->user()),
        ]);
    }

    public function show(ReceiptIssue $receipt)
    {
        return view('admin.finance.receipts.show', [
            'receipt' => $receipt->loadMissing([
                'issuedBy',
                'member',
                'familia',
                'jumuiya',
                'kanda',
                'contributionType',
                'actionLogs.user',
                'deliveryLogs',
                'accessLogs',
                'verificationLogs',
                'exceptionLogs',
            ]),
            'timeline' => $this->queryService->timeline($receipt),
            'qrVerificationUrl' => $this->qrCodeService->verificationUrl($receipt),
        ]);
    }

    public function downloadPdf(Request $request, ReceiptIssue $receipt): Response
    {
        $receipt = $this->issueService->markPrinted($receipt, $request->user(), true);

        return $this->pdfService->downloadIssuedReceipt($receipt, [
            'qr_svg' => $this->qrCodeService->svg($receipt),
            'verification_url' => $this->qrCodeService->verificationUrl($receipt),
        ]);
    }

    public function sendSms(SendReceiptSmsRequest $request, ReceiptIssue $receipt)
    {
        $this->smsService->send($receipt->load('member'), $request->user(), $request->input('phone'), $request->input('message'));

        return back()->with('success', db_trans('receipt_sms_sent_successfully'));
    }

    public function resendSms(SendReceiptSmsRequest $request, ReceiptIssue $receipt)
    {
        return $this->sendSms($request, $receipt);
    }

    public function reprint(Request $request, ReceiptIssue $receipt)
    {
        $this->issueService->markPrinted($receipt, $request->user(), false);

        return back()->with('success', db_trans('receipt_printed_successfully'));
    }
}
