<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBookingStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'old_booking_status',
        'new_booking_status',
        'old_payment_status',
        'new_payment_status',
        'note',
        'changed_by',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class, 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
