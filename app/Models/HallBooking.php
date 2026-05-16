<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HallBooking extends Model
{
    use HasFactory;

    public const BOOKING_PENDING = 'inasubiri';
    public const BOOKING_APPROVED = 'imeidhinishwa';
    public const BOOKING_REJECTED = 'imekataliwa';
    public const BOOKING_CANCELLED = 'imefutwa';
    public const BOOKING_COMPLETED = 'imekamilika';

    public const PAYMENT_UNPAID = 'haijalipwa';
    public const PAYMENT_SUBMITTED = 'malipo_yamewasilishwa';
    public const PAYMENT_VERIFIED = 'malipo_yamethibitishwa';
    public const PAYMENT_REJECTED = 'malipo_yamekataliwa';

    protected $fillable = [
        'hall_id',
        'booking_reference',
        'customer_name',
        'customer_phone',
        'customer_email',
        'booking_date',
        'price',
        'booking_status',
        'payment_status',
        'payment_reference',
        'payment_note',
        'payment_proof_path',
        'admin_note',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'expires_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'price' => 'decimal:2',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (HallBooking $booking): void {
            if (blank($booking->booking_reference)) {
                $booking->booking_reference = static::makeReference();
            }
        });
    }

    public static function makeReference(): string
    {
        do {
            $reference = 'HB-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (static::where('booking_reference', $reference)->exists());

        return $reference;
    }

    public static function bookingStatuses(): array
    {
        return [
            self::BOOKING_PENDING,
            self::BOOKING_APPROVED,
            self::BOOKING_REJECTED,
            self::BOOKING_CANCELLED,
            self::BOOKING_COMPLETED,
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_UNPAID,
            self::PAYMENT_SUBMITTED,
            self::PAYMENT_VERIFIED,
            self::PAYMENT_REJECTED,
        ];
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HallBookingStatusLog::class, 'booking_id')->latest();
    }

    public function getBookingStatusLabelKeyAttribute(): string
    {
        return 'hall_booking_status_' . $this->booking_status;
    }

    public function getPaymentStatusLabelKeyAttribute(): string
    {
        return 'hall_payment_status_' . $this->payment_status;
    }
}
