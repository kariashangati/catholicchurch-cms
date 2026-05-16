<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationBalanceTransaction extends Model
{
    use HasFactory;

    public const TYPE_TOP_UP = 'top_up';
    public const TYPE_DEBIT = 'debit';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'provider',
        'transaction_type',
        'reference_type',
        'reference_id',
        'campaign_id',
        'amount_units',
        'balance_before',
        'balance_after',
        'description',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_units' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(CommunicationCampaign::class, 'campaign_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
