<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptVerificationLog extends Model
{
    use HasFactory;

    protected $table = 'receipt_verification_logs';

    protected $fillable = [
        'receipt_issue_id',
        'lookup_value',
        'lookup_type',
        'status',
        'ip_address',
        'user_agent',
        'performed_by',
        'notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function receiptIssue(): BelongsTo
    {
        return $this->belongsTo(ReceiptIssue::class);
    }
}
