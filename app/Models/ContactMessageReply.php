<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessageReply extends Model
{
    public const SMS_PENDING = 'inasubiri';
    public const SMS_SENT = 'imetumwa';
    public const SMS_FAILED = 'imeshindwa';
    public const SMS_SKIPPED = 'haikutumwa';

    protected $fillable = [
        'contact_message_id',
        'user_id',
        'reply_body',
        'send_sms',
        'sms_status',
        'sms_error',
        'sms_sent_at',
        'provider_response',
    ];

    protected function casts(): array
    {
        return [
            'send_sms' => 'boolean',
            'sms_sent_at' => 'datetime',
            'provider_response' => 'array',
        ];
    }

    public function contactMessage()
    {
        return $this->belongsTo(ContactMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
