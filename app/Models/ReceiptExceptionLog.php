<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptExceptionLog extends Model
{
    use HasFactory;

    protected $table = 'receipt_exception_logs';

    protected $fillable = ['receipt_issue_id', 'category', 'message', 'context', 'reported_by', 'reported_at', 'resolved_at'];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
            'context' => 'array'
        ];
    }

    public function receiptIssue(): BelongsTo
    {
        return $this->belongsTo(ReceiptIssue::class);
    }
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
