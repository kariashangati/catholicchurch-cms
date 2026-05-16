<?php

namespace App\Services\HallBooking;

use App\Models\Hall;
use App\Models\HallBooking;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HallAvailabilityService
{
    public function statusForDate(Hall $hall, CarbonInterface|string $date): string
    {
        $date = is_string($date) ? now()->parse($date) : $date;
        $dateString = $date->toDateString();

        if ($hall->blockedDates()->whereDate('blocked_date', $dateString)->exists()) {
            return 'imefungwa';
        }

        $approved = $hall->bookings()
            ->whereDate('booking_date', $dateString)
            ->where('booking_status', HallBooking::BOOKING_APPROVED)
            ->exists();

        if ($approved) {
            return 'imehifadhiwa';
        }

        $pending = $hall->bookings()
            ->whereDate('booking_date', $dateString)
            ->whereIn('booking_status', [HallBooking::BOOKING_PENDING])
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($pending) {
            return 'inasubiri';
        }

        return 'inapatikana';
    }

    public function isAvailable(Hall $hall, CarbonInterface|string $date): bool
    {
        return $this->statusForDate($hall, $date) === 'inapatikana';
    }

    public function monthlyCalendar(Hall $hall, int $year, int $month): Collection
    {
        $start = now()->setDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        return collect(range(1, (int) $end->format('d')))->map(function (int $day) use ($hall, $start) {
            $date = $start->copy()->day($day);

            return [
                'date' => $date->toDateString(),
                'day' => $day,
                'status' => $this->statusForDate($hall, $date),
            ];
        });
    }
}
