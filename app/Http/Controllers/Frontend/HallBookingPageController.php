<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallBooking;
use App\Services\Frontend\FrontendContentService;
use App\Services\HallBooking\HallAvailabilityService;
use App\Services\HallBooking\HallPricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HallBookingPageController extends Controller
{
    public function __construct(
        protected FrontendContentService $frontend,
        protected HallPricingService $pricing,
        protected HallAvailabilityService $availability,
    ) {
    }

    public function index(Request $request): View
    {
        $halls = Hall::query()
            ->where('is_active', true)
            ->with(['images'])
            ->withCount([
                'bookings as approved_bookings_count' => fn ($query) => $query->where('booking_status', HallBooking::BOOKING_APPROVED),
            ])
            ->orderBy('name')
            ->get();

        $featuredHall = $halls->first();

        return view('frontend.pages.hall-bookings.index', array_merge(
            $this->frontend->shared(),
            [
                'halls' => $halls,
                'featuredHall' => $featuredHall,
                'nextAvailable' => $featuredHall ? $this->nextAvailableDate($featuredHall) : null,
            ]
        ));
    }

    public function show(Request $request, Hall $hall): View
    {
        abort_unless($hall->is_active, 404);

        $hall->load(['images', 'priceRules' => fn ($query) => $query->where('is_active', true)->latest()]);

        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();

        return view('frontend.pages.hall-bookings.show', array_merge(
            $this->frontend->shared(),
            [
                'hall' => $hall,
                'calendar' => $this->calendarPayload($hall, $monthStart),
                'monthStart' => $monthStart,
                'today' => today(),
                'nextAvailable' => $this->nextAvailableDate($hall),
            ]
        ));
    }

    public function availability(Request $request, Hall $hall): JsonResponse
    {
        abort_unless($hall->is_active, 404);

        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();

        return response()->json($this->calendarPayload($hall, $monthStart));
    }

    public function store(Request $request, Hall $hall): RedirectResponse
    {
        abort_unless($hall->is_active, 404);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:180'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_reference' => ['nullable', 'string', 'max:180'],
            'payment_note' => ['nullable', 'string', 'max:1000'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'accept_conditions' => ['accepted'],
        ]);

        abort_unless($this->availability->isAvailable($hall, $data['booking_date']), 422, db_trans('hall_date_not_available'));

        $data['hall_id'] = $hall->id;
        $data['price'] = $this->pricing->priceFor($hall, $data['booking_date']);
        $data['booking_status'] = HallBooking::BOOKING_PENDING;
        $data['payment_status'] = filled($data['payment_reference'] ?? null) || $request->hasFile('payment_proof')
            ? HallBooking::PAYMENT_SUBMITTED
            : HallBooking::PAYMENT_UNPAID;
        $data['expires_at'] = now()->addDay();

        if ($request->hasFile('payment_proof')) {
            $data['payment_proof_path'] = $request->file('payment_proof')->store('hall-bookings/payment-proofs', 'public');
        }

        unset($data['payment_proof'], $data['accept_conditions']);

        $booking = HallBooking::create($data);

        return redirect()
            ->route('frontend.hall-bookings.confirmation', $booking->booking_reference)
            ->with('success', db_trans('frontend_hall_booking_created_successfully'));
    }

    public function confirmation(string $reference): View
    {
        $booking = HallBooking::query()
            ->with('hall.images')
            ->where('booking_reference', strtoupper($reference))
            ->firstOrFail();

        return view('frontend.pages.hall-bookings.confirmation', array_merge(
            $this->frontend->shared(),
            [
                'booking' => $booking,
                'statusLabels' => $this->statusLabels(),
                'paymentLabels' => $this->paymentLabels(),
            ]
        ));
    }

    public function track(Request $request): View
    {
        $booking = null;
        $notFound = false;

        if ($request->filled('reference') && $request->filled('phone')) {
            $booking = $this->findBooking($request->string('reference')->toString(), $request->string('phone')->toString());
            $notFound = ! $booking;
        }

        return view('frontend.pages.hall-bookings.track', array_merge(
            $this->frontend->shared(),
            [
                'booking' => $booking,
                'notFound' => $notFound,
                'statusLabels' => $this->statusLabels(),
                'paymentLabels' => $this->paymentLabels(),
            ]
        ));
    }

    public function submitTrack(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
        ]);

        return redirect()->route('frontend.hall-bookings.track', [
            'reference' => strtoupper(trim($data['reference'])),
            'phone' => trim($data['phone']),
        ]);
    }

    public function submitPayment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
            'payment_reference' => ['required', 'string', 'max:180'],
            'payment_note' => ['nullable', 'string', 'max:1000'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $booking = $this->findBooking($data['reference'], $data['phone']);

        if (! $booking) {
            return back()->with('error', db_trans('frontend_hall_booking_not_found'));
        }

        if (in_array($booking->payment_status, [HallBooking::PAYMENT_VERIFIED], true)) {
            return back()->with('error', db_trans('frontend_hall_payment_already_verified'));
        }

        $update = [
            'payment_status' => HallBooking::PAYMENT_SUBMITTED,
            'payment_reference' => $data['payment_reference'],
            'payment_note' => $data['payment_note'] ?? $booking->payment_note,
        ];

        if ($request->hasFile('payment_proof')) {
            if ($booking->payment_proof_path) {
                Storage::disk('public')->delete($booking->payment_proof_path);
            }
            $update['payment_proof_path'] = $request->file('payment_proof')->store('hall-bookings/payment-proofs', 'public');
        }

        $booking->update($update);

        return redirect()
            ->route('frontend.hall-bookings.track', ['reference' => $booking->booking_reference, 'phone' => $booking->customer_phone])
            ->with('success', db_trans('frontend_hall_payment_submitted_successfully'));
    }

    protected function calendarPayload(Hall $hall, Carbon $monthStart): array
    {
        $start = $monthStart->copy()->startOfMonth();
        $end = $monthStart->copy()->endOfMonth();
        $firstWeekday = (int) $start->dayOfWeek;
        $days = [];

        for ($i = 0; $i < $firstWeekday; $i++) {
            $days[] = null;
        }

        for ($day = 1; $day <= (int) $end->format('d'); $day++) {
            $date = $start->copy()->day($day);
            $isPast = $date->lt(today());
            $status = $isPast ? 'imepita' : $this->availability->statusForDate($hall, $date);
            $price = $this->pricing->priceFor($hall, $date);

            $days[] = [
                'date' => $date->toDateString(),
                'day' => $day,
                'weekday' => $date->translatedFormat('D'),
                'status' => $status,
                'status_label' => $this->availabilityLabel($status),
                'available' => $status === 'inapatikana',
                'price' => $price,
                'price_label' => 'TZS ' . number_format($price, 2),
            ];
        }

        return [
            'year' => (int) $start->year,
            'month' => (int) $start->month,
            'month_label' => $start->translatedFormat('F Y'),
            'previous' => $start->copy()->subMonth()->format('Y-m'),
            'next' => $start->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'legend' => [
                'inapatikana' => $this->availabilityLabel('inapatikana'),
                'inasubiri' => $this->availabilityLabel('inasubiri'),
                'imehifadhiwa' => $this->availabilityLabel('imehifadhiwa'),
                'imefungwa' => $this->availabilityLabel('imefungwa'),
            ],
        ];
    }

    protected function nextAvailableDate(Hall $hall): ?array
    {
        for ($i = 0; $i < 90; $i++) {
            $date = today()->addDays($i);
            if ($this->availability->isAvailable($hall, $date)) {
                return [
                    'date' => $date,
                    'price' => $this->pricing->priceFor($hall, $date),
                ];
            }
        }

        return null;
    }

    protected function findBooking(string $reference, string $phone): ?HallBooking
    {
        $booking = HallBooking::query()
            ->with('hall.images')
            ->where('booking_reference', strtoupper(trim($reference)))
            ->first();

        if (! $booking) {
            return null;
        }

        $inputPhone = preg_replace('/\D+/', '', $phone);
        $bookingPhone = preg_replace('/\D+/', '', (string) $booking->customer_phone);

        if ($inputPhone === '' || $bookingPhone === '') {
            return null;
        }

        if ($inputPhone === $bookingPhone || str_ends_with($bookingPhone, substr($inputPhone, -9)) || str_ends_with($inputPhone, substr($bookingPhone, -9))) {
            return $booking;
        }

        return null;
    }

    protected function availabilityLabel(string $status): string
    {
        return match ($status) {
            'inapatikana' => db_trans('frontend_hall_status_available'),
            'inasubiri' => db_trans('frontend_hall_status_pending'),
            'imehifadhiwa' => db_trans('frontend_hall_status_booked'),
            'imefungwa' => db_trans('frontend_hall_status_blocked'),
            'imepita' => db_trans('frontend_hall_status_past'),
            default => $status,
        };
    }

    protected function statusLabels(): array
    {
        return [
            HallBooking::BOOKING_PENDING => db_trans('hall_booking_status_inasubiri'),
            HallBooking::BOOKING_APPROVED => db_trans('hall_booking_status_imeidhinishwa'),
            HallBooking::BOOKING_REJECTED => db_trans('hall_booking_status_imekataliwa'),
            HallBooking::BOOKING_CANCELLED => db_trans('hall_booking_status_imefutwa'),
            HallBooking::BOOKING_COMPLETED => db_trans('hall_booking_status_imekamilika'),
        ];
    }

    protected function paymentLabels(): array
    {
        return [
            HallBooking::PAYMENT_UNPAID => db_trans('hall_payment_status_haijalipwa'),
            HallBooking::PAYMENT_SUBMITTED => db_trans('hall_payment_status_malipo_yamewasilishwa'),
            HallBooking::PAYMENT_VERIFIED => db_trans('hall_payment_status_malipo_yamethibitishwa'),
            HallBooking::PAYMENT_REJECTED => db_trans('hall_payment_status_malipo_yamekataliwa'),
        ];
    }
}
