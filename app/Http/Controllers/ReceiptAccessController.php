<?php

namespace App\Http\Controllers;

use App\Services\Receipts\ReceiptAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReceiptAccessController extends Controller
{
    public function __construct(
        protected ReceiptAccessService $receiptAccessService,
    ) {
    }

    public function show(Request $request, string $token): View
    {
        $access = $this->receiptAccessService->resolveAccessibleReceipt($token, $request);

        if ($access->isExpired()) {
            return view('receipts.expired', [
                'receipt' => $access->receipt(),
                'reason' => $access->reason(),
            ]);
        }

        $this->receiptAccessService->recordOpen($access, $request);

        return view('receipts.access', [
            'receipt' => $access->receipt(),
            'accessMeta' => $access,
        ]);
    }

    public function download(Request $request, string $token): BinaryFileResponse
    {
        $access = $this->receiptAccessService->resolveAccessibleReceipt($token, $request);

        abort_if($access->isExpired(), 403, db_trans('receipt_access_link_expired'));

        $this->receiptAccessService->recordDownload($access, $request);

        return $this->receiptAccessService->download($access, $request);
    }
}
