<?php
namespace App\Http\Controllers\PublicSermons;
use App\Http\Controllers\Controller;
use App\Models\SermonAccessToken;
use App\Models\SermonView;
use Illuminate\Http\Request;

class PublicSermonController extends Controller
{
    public function show(Request $request, string $sermonToken)
    {
        $token = SermonAccessToken::with(['sermon','recipient'])->where('token', $sermonToken)->firstOrFail();
        abort_unless($token->isUsable(), 403, db_trans('sermon_link_not_available'));

        $token->forceFill([
            'opened_at' => $token->opened_at ?: now(),
            'last_opened_at' => now(),
            'open_count' => ($token->open_count ?? 0) + 1,
        ])->save();

        SermonView::create([
            'sermon_id' => $token->sermon_id,
            'sermon_access_token_id' => $token->id,
            'sermon_recipient_id' => $token->sermon_recipient_id,
            'member_id' => $token->member_id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 1000),
            'viewed_at' => now(),
        ]);

        return view('public.sermons.show', ['sermon'=>$token->sermon, 'recipient'=>$token->recipient]);
    }
}
