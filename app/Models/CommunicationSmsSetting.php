<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationSmsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'sender_id',
        'segment_length',
        'unicode_segment_length',
        'sms_unit_price',
        'currency',
        'is_active',
        'notes',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'segment_length' => 'integer',
            'unicode_segment_length' => 'integer',
            'sms_unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
