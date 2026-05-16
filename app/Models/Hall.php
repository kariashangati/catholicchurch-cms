<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Hall extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'capacity',
        'location',
        'conditions',
        'default_price',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'payment_instructions',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'default_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Hall $hall): void {
            if (blank($hall->slug)) {
                $hall->slug = Str::slug($hall->name);
            }
        });
    }

    public function priceRules(): HasMany
    {
        return $this->hasMany(HallPriceRule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(HallBooking::class);
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(HallBlockedDate::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(HallImage::class)->orderByDesc('is_cover')->orderBy('sort_order');
    }

    public function coverImage(): HasMany
    {
        return $this->hasMany(HallImage::class)->where('is_cover', true)->orderBy('sort_order');
    }
}
