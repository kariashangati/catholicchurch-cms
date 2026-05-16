<?php

namespace App\Http\Controllers\Admin\HallBooking;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallBooking;
use Carbon\CarbonPeriod;

class HallBookingDashboardController extends Controller
{
    public function index()
    {
        $start = now()->copy()->startOfYear();
        $end = now()->copy()->endOfYear();

        $monthly = collect(CarbonPeriod::create($start, '1 month', $end))->map(function ($date) {
            $count = HallBooking::whereYear('booking_date', $date->year)
                ->whereMonth('booking_date', $date->month)
                ->count();

            return [
                'label' => $date->format('M'),
                'value' => $count,
            ];
        });

        $bookingStatusCounts = collect(HallBooking::bookingStatuses())->map(fn ($status) => [
            'label' => db_trans('hall_booking_status_' . $status),
            'value' => HallBooking::where('booking_status', $status)->count(),
        ]);

        return view('admin.hall-bookings.dashboard', [
            'page' => [
                'title' => db_trans('hall_booking_center'),
                'subtitle' => db_trans('hall_booking_center_subtitle'),
                'updated_at' => now(),
            ],
            'kpis' => [
                ['label' => db_trans('total_bookings'), 'value' => HallBooking::count(), 'icon' => 'fas fa-calendar-check', 'tone' => 'primary'],
                ['label' => db_trans('pending_payments'), 'value' => HallBooking::where('payment_status', HallBooking::PAYMENT_SUBMITTED)->count(), 'icon' => 'fas fa-receipt', 'tone' => 'warning'],
                ['label' => db_trans('approved_bookings'), 'value' => HallBooking::where('booking_status', HallBooking::BOOKING_APPROVED)->count(), 'icon' => 'fas fa-check-circle', 'tone' => 'success'],
                ['label' => db_trans('active_halls'), 'value' => Hall::where('is_active', true)->count(), 'icon' => 'fas fa-building', 'tone' => 'info'],
            ],
            'monthlyChart' => $monthly,
            'statusChart' => $bookingStatusCounts,
            'latestBookings' => HallBooking::with('hall')->latest()->limit(10)->get(),
            'upcomingBookings' => HallBooking::with('hall')
                ->whereDate('booking_date', '>=', today())
                ->where('booking_status', HallBooking::BOOKING_APPROVED)
                ->orderBy('booking_date')
                ->limit(10)
                ->get(),
            'pendingPayments' => HallBooking::with('hall')
                ->where('payment_status', HallBooking::PAYMENT_SUBMITTED)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
