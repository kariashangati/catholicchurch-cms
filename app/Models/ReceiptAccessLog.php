<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptAccessLog extends Model
{
    use HasFactory;

    protected $table = 'receipt_access_logs';

    protected $fillable = [
        'receipt_issue_id',
        'access_token',
        'access_type',
        'status',
        'ip_address',
        'user_agent',
        'performed_by',
        'notes',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return ['accessed_at' => 'datetime'];
    }

    public function receiptIssue(): BelongsTo
    {
        return $this->belongsTo(ReceiptIssue::class);
    }
}
