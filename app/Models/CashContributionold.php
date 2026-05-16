<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'contribution_type_id',
        'member_id',
        'contribution_date',
        'amount',
        'group_name',
        'status',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'contribution_date' => 'date',
        ];
    }

    public function type()
    {
        return $this->belongsTo(ContributionType::class, 'contribution_type_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}