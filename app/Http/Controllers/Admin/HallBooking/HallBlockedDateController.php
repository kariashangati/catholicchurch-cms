<?php

namespace App\Http\Controllers\Admin\HallBooking;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallBlockedDate;
use Illuminate\Http\Request;

class HallBlockedDateController extends Controller
{
    public function index()
    {
        return view('admin.hall-bookings.blocked-dates.index', [
            'halls' => Hall::where('is_active', true)->orderBy('name')->get(),
            'blockedDates' => HallBlockedDate::with(['hall', 'creator'])->latest('blocked_date')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hall_id' => ['required', 'exists:halls,id'],
            'blocked_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $data['created_by'] = $request->user()?->id;

        HallBlockedDate::updateOrCreate([
            'hall_id' => $data['hall_id'],
            'blocked_date' => $data['blocked_date'],
        ], $data);

        return back()->with('success', db_trans('hall_blocked_date_saved_successfully'));
    }

    public function destroy(HallBlockedDate $blockedDate)
    {
        $blockedDate->delete();

        return back()->with('success', db_trans('hall_blocked_date_deleted_successfully'));
    }
}
