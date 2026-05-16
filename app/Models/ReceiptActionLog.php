<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptActionLog extends Model
{
    use HasFactory;

    protected $table = 'receipt_action_logs';

    protected $fillable = [
        'receipt_issue_id',
        'action',
        'description',
        'performed_by',
        'from_status',
        'to_status',
        'actor_id',
        'actor_name',
        'reason',
        'meta',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function receiptIssue(): BelongsTo
    {
        return $this->belongsTo(ReceiptIssue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function actor(): BelongsTo
    {
        return $this->user();
    }
}
