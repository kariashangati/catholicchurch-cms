<?php

namespace App\Services\ContactMessages;

use App\Models\ContactMessage;
use App\Models\ContactMessageStatusLog;
use App\Models\User;

class ContactMessageStatusService
{
    public function change(ContactMessage $message, string $status, ?User $user = null, ?string $note = null): ContactMessage
    {
        $old = $message->status;

        $payload = ['status' => $status];

        if ($status === ContactMessage::STATUS_ANSWERED) {
            $payload['answered_by'] = $user?->id;
            $payload['answered_at'] = now();
        }

        if ($status === ContactMessage::STATUS_CLOSED) {
            $payload['closed_by'] = $user?->id;
            $payload['closed_at'] = now();
        }

        if ($status !== ContactMessage::STATUS_CLOSED) {
            $payload['closed_by'] = null;
            $payload['closed_at'] = null;
        }

        $message->forceFill($payload)->save();

        ContactMessageStatusLog::create([
            'contact_message_id' => $message->id,
            'old_status' => $old,
            'new_status' => $status,
            'note' => $note,
            'changed_by' => $user?->id,
        ]);

        return $message;
    }
}
