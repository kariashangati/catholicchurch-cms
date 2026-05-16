<?php

namespace App\Http\Controllers;

use App\Services\Receipts\ReceiptVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReceiptVerificationController extends Controller
{
    public function __construct(
        protected ReceiptVerificationService $receiptVerificationService,
    ) {
    }

    public function show(Request $request, string $token): View
    {
        // Important safety fallback:
        // Some route files define /receipts/verify/{token} before /receipts/verify/search.
        // In that case Laravel treats "search" as a token and ignores receipt_no / verification_code.
        // This forwards that request to the real manual search logic.
        if (strtolower(trim($token)) === 'search') {
            return $this->search($request);
        }

        $result = $this->receiptVerificationService->verifyByToken($token, [
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return view('receipts.verify', [
            'result' => $result,
            'receipt' => $result['receipt'] ?? null,
        ]);
    }

    public function search(Request $request): View
    {
        $validated = $request->validate([
            'receipt_no' => ['nullable', 'string', 'max:100'],
            'verification_code' => ['nullable', 'string', 'max:100'],
        ]);

        $result = null;

        if (filled($validated['receipt_no'] ?? null) || filled($validated['verification_code'] ?? null)) {
            $result = $this->receiptVerificationService->verifyByReference(
                $validated['receipt_no'] ?? null,
                $validated['verification_code'] ?? null,
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
