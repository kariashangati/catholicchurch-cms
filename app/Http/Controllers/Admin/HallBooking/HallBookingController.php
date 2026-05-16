<?php

namespace App\Http\Controllers\Admin\HallBooking;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallBooking;
use App\Services\HallBooking\HallAvailabilityService;
use App\Services\HallBooking\HallBookingStatusService;
use App\Services\HallBooking\HallPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HallBookingController extends Controller
{
    public function __construct(
        protected HallPricingService $pricing,
        protected HallAvailabilityService $availability,
        protected HallBookingStatusService $statusService,
    ) {
    }

    public function index(Request $request)
    {
        $bookings = HallBooking::with('hall')
            ->when($request->filled('status'), fn ($q) => $q->where('booking_status', $request->status))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('hall_id'), fn ($q) => $q->where('hall_id', $request->hall_id))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('booking_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('booking_date', '<=', $request->to_date))
            ->latest()
            ->get();

        return view('admin.hall-bookings.bookings.index', [
            'bookings' => $bookings,
            'halls' => Hall::where('is_active', true)->orderBy('name')->get(),
            'bookingStatuses' => HallBooking::bookingStatuses(),
            'paymentStatuses' => HallBooking::paymentStatuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hall_id' => ['required', 'exists:halls,id'],
            'customer_name' => ['required', 'string', 'max:180'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_reference' => ['nullable', 'string', 'max:180'],
            'payment_note' => ['nullable', 'string'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $hall = Hall::findOrFail($data['hall_id']);
        abort_unless($this->availability->isAvailable($hall, $data['booking_date']), 422, db_trans('hall_date_not_available'));

        $data['price'] = $this->pricing->priceFor($hall, $data['booking_date']);
        $data['booking_status'] = HallBooking::BOOKING_PENDING;
        $data['payment_status'] = filled($data['payment_reference'] ?? null) ? HallBooking::PAYMENT_SUBMITTED : HallBooking::PAYMENT_UNPAID;
        $data['expires_at'] = now()->addDay();

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof_path'] = $request->file('payment_proof')->store('hall-bookings/payment-proofs', 'public');
        }

        unset($data['payment_proof']);
        HallBooking::create($data);

        return back()->with('success', db_trans('hall_booking_created_successfully'));
    }

    public function show(HallBooking $booking)
    {
        return view('admin.hall-bookings.bookings.show', [
            'booking' => $booking->load(['hall.images', 'logs.user', 'verifier', 'approver']),
            'bookingStatuses' => HallBooking::bookingStatuses(),
            'paymentStatuses' => HallBooking::paymentStatuses(),
        ]);
    }

    public function update(Request $request, HallBooking $booking)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:180'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'payment_reference' => ['nullable', 'string', 'max:180'],
            'payment_note' => ['nullable', 'string'],
            'admin_note' => ['nullable', 'string'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        if ($request->hasFile('payment_proof')) {
            if ($booking->payment_proof_path) {
                Storage::disk('public')->delete($booking->payment_proof_path);
            }
            $data['payment_proof_path'] = $request->file('payment_proof')->store('hall-bookings/payment-proofs', 'public');
        }

        unset($data['payment_proof']);
        $booking->update($data);

        return back()->with('success', db_trans('hall_booking_updated_successfully'));
    }

    public function changeStatus(Request $request, HallBooking $booking)
    {
        $data = $request->validate([
            'booking_status' => ['nullable', 'in:' . implode(',', HallBooking::bookingStatuses())],
            'payment_status' => ['nullable', 'in:' . implode(',', HallBooking::paymentStatuses())],
            'admin_note' => ['nullable', 'string'],
        ]);

        $this->statusService->update(
            $booking,
            $data['booking_status'] ?? null,
            $data['payment_status'] ?? null,
            $request->user(),
            $data['admin_note'] ?? null
        );

        return back()->with('success', db_trans('hall_booking_status_updated_successfully'));
    }

    public function verifyPayment(Request $request, HallBooking $booking)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string']]);

        $this->statusService->update(
            $booking,
            null,
            HallBooking::PAYMENT_VERIFIED,
            $request->user(),
            $data['admin_note'] ?? null
        );

        return back()->with('success', db_trans('hall_payment_verified_successfully'));
    }

    public function approve(Request $request, HallBooking $booking)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string']]);

        abort_unless($booking->payment_status === HallBooking::PAYMENT_VERIFIED, 422, db_trans('verify_payment_before_approval'));

        $this->statusService->update(
            $booking,
            HallBooking::BOOKING_APPROVED,
            null,
            $request->user(),
            $data['admin_note'] ?? null
        );

        return back()->with('success', db_trans('hall_booking_approved_successfully'));
    }

    public function reject(Request $request, HallBooking $booking)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string']]);

        $this->statusService->update(
            $booking,
            HallBooking::BOOKING_REJECTED,
            null,
            $request->user(),
            $data['admin_note'] ?? null
        );

        return back()->with('success', db_trans('hall_booking_rejected_successfully'));
    }

    public function destroy(HallBooking $booking)
    {
        $booking->delete();

        return back()->with('success', db_trans('hall_booking_deleted_successfully'));
    }
}
