<?php

namespace App\Services\HallBooking;

use App\Models\HallBooking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HallBookingStatusService
{
    public function update(
        HallBooking $booking,
        ?string $bookingStatus,
        ?string $paymentStatus,
        ?User $user,
        ?string $note = null
    ): HallBooking {
        return DB::transaction(function () use ($booking, $bookingStatus, $paymentStatus, $user, $note) {
            $oldBookingStatus = $booking->booking_status;
            $oldPaymentStatus = $booking->payment_status;

            if ($bookingStatus !== null) {
                $booking->booking_status = $bookingStatus;
            }

            if ($paymentStatus !== null) {
                $booking->payment_status = $paymentStatus;
            }

            if ($paymentStatus === HallBooking::PAYMENT_VERIFIED) {
                $booking->verified_by = $user?->id;
                $booking->verified_at = now();
            }

            if ($bookingStatus === HallBooking::BOOKING_APPROVED) {
                $booking->approved_by = $user?->id;
                $booking->approved_at = now();
            }

            if ($note !== null) {
                $booking->admin_note = $note;
            }

            $booking->save();

            $booking->logs()->create([
                'old_booking_status' => $oldBookingStatus,
                'new_booking_status' => $booking->booking_status,
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => $booking->payment_status,
                'note' => $note,
                'changed_by' => $user?->id,
            ]);

            return $booking->fresh(['hall', 'logs']);
        });
    }
}
