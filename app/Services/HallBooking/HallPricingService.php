<?php

namespace App\Services\HallBooking;

use App\Models\Hall;
use App\Models\HallPriceRule;
use Carbon\CarbonInterface;

class HallPricingService
{
    public function priceFor(Hall $hall, CarbonInterface|string $date): float
    {
        $date = is_string($date) ? now()->parse($date) : $date;

        $special = $hall->priceRules()
            ->where('is_active', true)
            ->whereDate('specific_date', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->latest('id')
            ->first();

        if ($special instanceof HallPriceRule) {
            return (float) $special->price;
        }

        $weekly = $hall->priceRules()
            ->where('is_active', true)
            ->whereNull('specific_date')
            ->where('day_of_week', (int) $date->dayOfWeek)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->latest('id')
            ->first();

        return (float) ($weekly?->price ?? $hall->default_price ?? 0);
    }
}
