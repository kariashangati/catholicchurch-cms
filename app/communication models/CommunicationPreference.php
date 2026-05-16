<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'preferred_locale',
        'preferred_phone',
        'alternate_phone',
        'allow_sms',
        'allow_general_sms',
        'allow_finance_sms',
        'allow_reminder_sms',
        'allow_announcement_sms',
        'allow_automated_sms',
        'allow_manual_sms',
        'is_phone_verified',
        'phone_verified_at',
        'opted_out_at',
        'opt_out_reason',
        'last_message_sent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allow_sms' => 'boolean',
            'allow_general_sms' => 'boolean',
            'allow_finance_sms' => 'boolean',
            'allow_reminder_sms' => 'boolean',
            'allow_announcement_sms' => 'boolean',
            'allow_automated_sms' => 'boolean',
            'allow_manual_sms' => 'boolean',
            'is_phone_verified' => 'boolean',
            'phone_verified_at' => 'datetime',
            'opted_out_at' => 'datetime',
            'last_message_sent_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getResolvedPhoneAttribute(): ?string
    {
        return $this->preferred_phone ?: $this->member?->phone;
    }
}
