<?php

namespace App\Http\Controllers\Receipts;

use App\Http\Controllers\Controller;
use App\Services\Receipts\ReceiptAccessService;
use App\Services\Receipts\ReceiptPdfService;
use App\Services\Receipts\ReceiptVerificationService;
use Illuminate\Http\Request;

class PublicReceiptController extends Controller
{
    public function __construct(
        protected ReceiptAccessService $accessService,
        protected ReceiptPdfService $pdfService,
        protected ReceiptVerificationService $verificationService,
    ) {
    }

    public function access(string $token, Request $request)
    {
        try {
            $receipt = method_exists($this->accessService, 'open')
                ? $this->accessService->open($token, $request)
                : $this->accessService->resolveAccessibleReceipt($token, $request)->receipt();
        } catch (\Throwable $e) {
            return view('receipts.expired', ['message' => $e->getMessage()]);
        }

        return view('receipts.access', compact('receipt', 'token'));
    }

    public function download(string $token, Request $request)
    {
        try {
            if (method_exists($this->accessService, 'download')) {
                $receipt = $this->accessService->download($token, $request);
            } else {
                $access = $this->accessService->resolveAccessibleReceipt($token, $request);
                $receipt = $access->receipt();
            }
        } catch (\Throwable $e) {
            return view('receipts.expired', ['message' => $e->getMessage()]);
        }

        $path = $receipt->pdf_path && is_file($receipt->pdf_path)
            ? $receipt->pdf_path
            : $this->pdfService->renderSingle($receipt);

        return response()->download($path)->deleteFileAfterSend(false);
    }

    public function verify()
    {
        return view('receipts.verify');
    }

    public function verifySearch(Request $request)
    {
        $result = null;

        if ($request->filled('receipt_no') || $request->filled('verification_code')) {
            $result = $this->verificationService->verifyByReference(
                $request->input('receipt_no'),
                $request->input('verification_code'),
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ]
            );
        }

        return view('receipts.verify', [
            'result' => $result,
            'receipt' => $result['receipt'] ?? null,
        ]);
    }
}
