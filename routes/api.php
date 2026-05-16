<?php

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/mpesa/callback', function (Request $request) {
    Log::info('M-Pesa callback received', [
        'payload' => $request->all(),
    ]);

    $originalConversationId = $request->input('input_OriginalConversationID');
    $transactionId = $request->input('input_TransactionID');
    $resultCode = $request->input('input_ResultCode');
    $resultDesc = $request->input('input_ResultDesc');
    $thirdPartyConversationId = $request->input('input_ThirdPartyConversationID');

    $payment = Payment::where('third_party_conversation_id', $thirdPartyConversationId)
        ->orWhere('conversation_id', $originalConversationId)
        ->first();

    if ($payment) {
        $payment->update([
            'status' => $resultCode === 'INS-0' ? 'paid' : 'failed',
            'transaction_id' => $transactionId,
            'callback_payload' => $request->all(),
            'paid_at' => $resultCode === 'INS-0' ? now() : null,
        ]);
    } else {
        Log::warning('M-Pesa callback payment not found', [
            'input_OriginalConversationID' => $originalConversationId,
            'input_ThirdPartyConversationID' => $thirdPartyConversationId,
            'input_ResultCode' => $resultCode,
            'input_ResultDesc' => $resultDesc,
        ]);
    }

    return response()->json([
        'output_OriginalConversationID' => $originalConversationId,
        'output_ResponseCode' => 'INS-0',
        'output_ResponseDesc' => 'Successfully Accepted Result',
        'output_ThirdPartyConversationID' => $thirdPartyConversationId,
    ]);
});