<?php

namespace App\Http\Controllers\Admin\HallBooking;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallPriceRule;
use Illuminate\Http\Request;

class HallPriceRuleController extends Controller
{
    public function index()
    {
        return view('admin.hall-bookings.prices.index', [
            'halls' => Hall::where('is_active', true)->orderBy('name')->get(),
            'rules' => HallPriceRule::with('hall')->latest()->get(),
            'weekDays' => $this->weekDays(),
        ]);
    }

    public function store(Request $request)
    {
        HallPriceRule::create($this->validated($request));

        return back()->with('success', db_trans('hall_price_rule_created_successfully'));
    }

    public function update(Request $request, HallPriceRule $priceRule)
    {
        $priceRule->update($this->validated($request));

        return back()->with('success', db_trans('hall_price_rule_updated_successfully'));
    }

    public function destroy(HallPriceRule $priceRule)
    {
        $priceRule->delete();

        return back()->with('success', db_trans('hall_price_rule_deleted_successfully'));
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'hall_id' => ['required', 'exists:halls,id'],
            'rule_type' => ['required', 'in:weekly,specific_date'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'specific_date' => ['nullable', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['rule_type'] === 'specific_date') {
            $data['day_of_week'] = null;
            $request->validate(['specific_date' => ['required', 'date']]);
        } else {
            $data['specific_date'] = null;
            $request->validate(['day_of_week' => ['required', 'integer', 'between:0,6']]);
        }

        unset($data['rule_type']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    protected function weekDays(): array
    {
        return [
            0 => db_trans('sunday'),
            1 => db_trans('monday'),
            2 => db_trans('tuesday'),
            3 => db_trans('wednesday'),
            4 => db_trans('thursday'),
            5 => db_trans('friday'),
            6 => db_trans('saturday'),
        ];
    }
}
