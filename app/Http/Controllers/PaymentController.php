<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function create()
    {
        return view('payments.create');
    }

    public function store(Request $request, MpesaService $mpesa)
    {
        $validated = $request->validate([
            'phone' => ['required', 'regex:/^[0-9]{12,14}$/'],
            'amount' => ['required', 'numeric', 'min:100'],
        ], [
            'phone.regex' => 'Phone must be in international format, example: 255712345678',
        ]);

        $paymentReference = 'PAY'.now()->format('YmdHis').rand(100, 999);

        /*
         * Must be max 40 characters.
         * M-Pesa docs regex: ^[0-9a-zA-Z \w+]{1,40}$
         */
        $thirdPartyConversationId = str_replace('-', '', (string) Str::uuid());

        $payment = Payment::create([
            'phone' => $validated['phone'],
            'amount' => $validated['amount'],
            'currency' => config('services.mpesa.currency'),
            'country' => config('services.mpesa.country'),
            'payment_reference' => $paymentReference,
            'third_party_conversation_id' => $thirdPartyConversationId,
            'status' => 'pending',
        ]);

        $payload = [
            'input_Amount' => (string) $payment->amount,
            'input_Country' => config('services.mpesa.country'),
            'input_Currency' => config('services.mpesa.currency'),
            'input_CustomerMSISDN' => $payment->phone,
            'input_ServiceProviderCode' => config('services.mpesa.service_provider_code'),
            'input_ThirdPartyConversationID' => $payment->third_party_conversation_id,
            'input_TransactionReference' => $payment->payment_reference,
            'input_PurchasedItemsDesc' => 'Test Payment '.$payment->payment_reference,
            'input_APIVersion' => config('services.mpesa.api_version'),
        ];

        $payment->update([
            'request_payload' => $payload,
        ]);

        try {
            $result = $mpesa->c2bMultiStage($payload);

            $body = $result['body'] ?? [];

            $payment->update([
                'mpesa_response' => $result,
                'status' => (($body['output_ResponseCode'] ?? null) === 'INS-0')
                    ? 'initiated'
                    : 'failed',
                'conversation_id' => $body['output_ConversationID'] ?? null,
                'transaction_id' => $body['output_TransactionID'] ?? null,
            ]);

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Payment request sent. Check customer phone for M-Pesa prompt.');

        } catch (Throwable $e) {
            $payment->update([
                'status' => 'failed',
                'mpesa_response' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            return redirect()
                ->route('payments.show', $payment)
                ->with('error', $e->getMessage());
        }
    }

    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }
}