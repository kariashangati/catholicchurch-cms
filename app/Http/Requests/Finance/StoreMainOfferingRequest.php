<?php

namespace App\Http\Requests\Finance;

use App\Models\Offering;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMainOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'offering_date' => ['required', 'date'],
            'mass_name' => ['nullable', 'string', 'max:255'],
            'mass_type_id' => ['nullable', 'exists:mass_types,id'],

            'payment_method' => [
                'nullable',
                Rule::in([
                    Offering::PAYMENT_CASH,
                    Offering::PAYMENT_BANK,
                    Offering::PAYMENT_MOBILE_MONEY,
                    Offering::PAYMENT_OTHER,
                ]),
            ],

            'reference_no' => ['nullable', 'string', 'max:100'],
            'receipt_no' => ['nullable', 'string', 'max:100'],

            'status' => [
                'required',
                Rule::in(Offering::availableStatuses()),
            ],

            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}