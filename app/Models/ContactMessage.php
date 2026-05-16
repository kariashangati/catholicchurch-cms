<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const STATUS_NEW = 'mpya';
    public const STATUS_READ = 'imesomwa';
    public const STATUS_ANSWERED = 'imejibiwa';
    public const STATUS_CLOSED = 'imefungwa';

    public const PRIORITY_LOW = 'chini';
    public const PRIORITY_NORMAL = 'kawaida';
    public const PRIORITY_HIGH = 'juu';
    public const PRIORITY_URGENT = 'haraka';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'contact_reason_id',
        'kanda_id',
        'jumuiya_id',
        'message',
        'status',
        'priority',
        'assigned_to',
        'admin_note',
        'answer_message',
        'answered_by',
        'answered_at',
        'closed_by',
        'closed_at',
        'last_sms_status',
        'last_sms_error',
        'last_sms_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_sms_sent_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_READ,
            self::STATUS_ANSWERED,
            self::STATUS_CLOSED,
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_NORMAL,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
        ];
    }

    public function reason()
    {
        return $this->belongsTo(ContactReason::class, 'contact_reason_id');
    }

    public function kanda()
    {
        return $this->belongsTo(Kanda::class);
    }

    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function answerer()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function replies()
    {
        return $this->hasMany(ContactMessageReply::class);
    }

    public function logs()
    {
        return $this->hasMany(ContactMessageStatusLog::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_READ]);
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', self::STATUS_ANSWERED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => db_trans('contact_status_new'),
            self::STATUS_READ => db_trans('contact_status_read'),
            self::STATUS_ANSWERED => db_trans('contact_status_answered'),
            self::STATUS_CLOSED => db_trans('contact_status_closed'),
            default => (string) $this->status,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => db_trans('priority_low'),
            self::PRIORITY_HIGH => db_trans('priority_high'),
            self::PRIORITY_URGENT => db_trans('priority_urgent'),
            default => db_trans('priority_normal'),
        };
    }
}
