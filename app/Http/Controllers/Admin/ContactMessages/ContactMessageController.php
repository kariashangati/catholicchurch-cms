<?php

namespace App\Http\Controllers\Admin\ContactMessages;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Models\ContactReason;
use App\Models\User;
use App\Services\ContactMessages\ContactMessageSmsService;
use App\Services\ContactMessages\ContactMessageStatusService;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::with(['reason', 'kanda', 'jumuiya', 'assignee', 'replies.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('reason_id')) {
            $query->where('contact_reason_id', $request->integer('reason_id'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term): void {
                $q->where('full_name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('message', 'like', $term);
            });
        }

        return view('admin.contact-messages.index', [
            'messages' => $query->get(),
            'statuses' => ContactMessage::statuses(),
            'priorities' => ContactMessage::priorities(),
            'reasons' => ContactReason::orderBy('display_order')->orderBy('name')->get(),
            'admins' => User::orderBy('name')->get(),
            'filters' => $request->only(['status', 'reason_id', 'priority', 'q']),
        ]);
    }

    public function markRead(ContactMessage $contactMessage, ContactMessageStatusService $statuses)
    {
        if ($contactMessage->status === ContactMessage::STATUS_NEW) {
            $statuses->change($contactMessage, ContactMessage::STATUS_READ, auth()->user(), db_trans('contact_message_marked_read'));
        }

        return back()->with('success', db_trans('contact_message_marked_read'));
    }

    public function assign(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'in:'.implode(',', ContactMessage::priorities())],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $contactMessage->update($data);

        return back()->with('success', db_trans('contact_message_updated_successfully'));
    }

    public function answer(Request $request, ContactMessage $contactMessage, ContactMessageSmsService $sms, ContactMessageStatusService $statuses)
    {
        $data = $request->validate([
            'reply_body' => ['required', 'string', 'max:1000'],
            'send_sms' => ['nullable', 'boolean'],
            'close_after_answer' => ['nullable', 'boolean'],
        ]);

        $reply = ContactMessageReply::create([
            'contact_message_id' => $contactMessage->id,
            'user_id' => auth()->id(),
            'reply_body' => $data['reply_body'],
            'send_sms' => $request->boolean('send_sms'),
            'sms_status' => $request->boolean('send_sms') ? ContactMessageReply::SMS_PENDING : ContactMessageReply::SMS_SKIPPED,
        ]);

        $contactMessage->forceFill([
            'answer_message' => $data['reply_body'],
            'answered_by' => auth()->id(),
            'answered_at' => now(),
        ])->save();

        if ($request->boolean('send_sms')) {
            $sms->sendReply($contactMessage, $reply, auth()->user());
        }

        $statuses->change(
            $contactMessage->fresh(),
            $request->boolean('close_after_answer') ? ContactMessage::STATUS_CLOSED : ContactMessage::STATUS_ANSWERED,
            auth()->user(),
            db_trans('contact_message_answered')
        );

        return back()->with('success', db_trans('contact_message_answered_successfully'));
    }

    public function changeStatus(Request $request, ContactMessage $contactMessage, ContactMessageStatusService $statuses)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', ContactMessage::statuses())],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $statuses->change($contactMessage, $data['status'], auth()->user(), $data['note'] ?? null);

        return back()->with('success', db_trans('contact_status_updated_successfully'));
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return back()->with('success', db_trans('contact_message_deleted_successfully'));
    }
}
