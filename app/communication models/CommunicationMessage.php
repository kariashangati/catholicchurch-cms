<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationMessage extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'campaign_id',
        'template_id',
        'member_id',
        'familia_id',
        'jumuiya_id',
        'kanda_id',
        'apostolic_group_id',
        'leadership_assignment_id',
        'recipient_name',
        'recipient_phone',
        'recipient_phone_normalized',
        'recipient_type',
        'locale',
        'message_body',
        'segment_count',
        'status',
        'delivery_status',
        'provider',
        'provider_message_id',
        'provider_batch_id',
        'provider_status_code',
        'provider_status_text',
        'error_code',
        'error_message',
        'meta',
        'queued_at',
        'sent_at',
        'delivered_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'segment_count' => 'integer',
            'meta' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(CommunicationCampaign::class, 'campaign_id');
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function familia()
    {
        return $this->belongsTo(Familia::class);
    }

    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function kanda()
    {
        return $this->belongsTo(Kanda::class);
    }

    public function apostolicGroup()
    {
        return $this->belongsTo(ApostolicGroup::class);
    }

    public function leadershipAssignment()
    {
        return $this->belongsTo(LeadershipAssignment::class);
    }
}
