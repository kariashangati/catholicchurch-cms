<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptDeliveryLog extends Model
{
    use HasFactory;

    protected $table = 'receipt_delivery_logs';

    protected $fillable = [
        'receipt_issue_id',
        'channel',
        'recipient',
        'template_key',
        'message_snapshot',
        'provider',
        'provider_message_id',
        'status',
        'provider_response',
        'sent_by',
        'sent_at',
        'attempt_no',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'attempt_no' => 'integer',
            'meta' => 'array',
        ];
    }

    public function receiptIssue(): BelongsTo
    {
        return $this->belongsTo(ReceiptIssue::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
