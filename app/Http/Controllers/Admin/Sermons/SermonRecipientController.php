<?php
namespace App\Http\Controllers\Admin\Sermons;
use App\Http\Controllers\Controller;
use App\Models\SermonAccessToken;
use App\Models\SermonRecipient;
use App\Services\Sermons\SermonSmsService;

class SermonRecipientController extends Controller
{
    public function index()
    {
        return view('admin.sermons.recipients.index', ['recipients'=>SermonRecipient::with(['sermon','token','member'])->latest()->get()]);
    }
    public function resend(SermonRecipient $recipient, SermonSmsService $sms)
    {
        $sms->sendRecipientLink($recipient);
        return back()->with('success', db_trans('sermon_sms_queued_successfully'));
    }
    public function toggleToken(SermonAccessToken $token)
    {
        $token->forceFill(['is_active'=>!$token->is_active])->save();
        return back()->with('success', db_trans('updated_successfully'));
    }
}
