<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationCampaign extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_MANUAL_SINGLE = 'manual_single';
    public const TYPE_MANUAL_BULK = 'manual_bulk';
    public const TYPE_TEMPLATE_BULK = 'template_bulk';
    public const TYPE_AUTOMATION = 'automation';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PARTIALLY_FAILED = 'partially_failed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'title',
        'code',
        'type',
        'channel',
        'template_id',
        'automation_id',
        'source_event_key',
        'audience_type',
        'audience_filters',
        'message_body',
        'render_locale',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
        'approved_by',
        'cancelled_by',
        'total_recipients',
        'valid_recipients',
        'invalid_recipients',
        'total_messages',
        'total_segments',
        'sent_count',
        'failed_count',
        'estimated_cost_units',
        'actual_cost_units',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'audience_filters' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_cost_units' => 'decimal:2',
            'actual_cost_units' => 'decimal:2',
        ];
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }

    public function automation()
    {
        return $this->belongsTo(CommunicationAutomation::class, 'automation_id');
    }

    public function messages()
    {
        return $this->hasMany(CommunicationMessage::class, 'campaign_id');
    }

    public function balanceTransactions()
    {
        return $this->hasMany(CommunicationBalanceTransaction::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
