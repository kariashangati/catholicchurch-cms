<?php

namespace App\Http\Requests;

use App\Models\ChurchAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChurchAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('condition_status')) {
            $this->merge([
                'condition_status' => ChurchAsset::normalizeConditionStatus($this->input('condition_status')),
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('church_asset')->id;

        return [
            'asset_category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'asset_code' => ['nullable', 'string', 'max:100', 'unique:church_assets,asset_code,' . $id],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'condition_status' => ['required', Rule::in(ChurchAsset::conditionStatuses())],
            'location' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
