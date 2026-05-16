<?php

namespace App\Http\Requests\Finance;

use App\Models\Offering;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.update')
            || $this->user()?->can('finance.offerings.update')
            || false;
    }

    public function rules(): array
    {
        return [
            'form_context' => ['nullable', 'string', 'max:80'],
            'offering_type_id' => ['required', 'exists:offering_types,id'],
            'mass_type_id' => ['nullable', 'exists:mass_types,id'],
            'collection_scope' => ['required', Rule::in(Offering::availableScopes())],
            'centre_detail_id' => ['nullable', 'integer', 'exists:centre_details,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'collection_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', Rule::in(Offering::availablePaymentMethods())],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'receipt_no' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(Offering::availableStatuses())],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $scope = $this->input('collection_scope');

            if ($scope === Offering::SCOPE_KANDA && ! $this->filled('kanda_id')) {
                $validator->errors()->add('kanda_id', db_trans('kanda_is_required_for_kanda_offerings'));
            }

            if ($scope === Offering::SCOPE_JUMUIYA && ! $this->filled('jumuiya_id')) {
                $validator->errors()->add('jumuiya_id', db_trans('jumuiya_is_required_for_jumuiya_offerings'));
            }
        });
    }
}