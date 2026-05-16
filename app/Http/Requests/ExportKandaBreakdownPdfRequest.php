<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportKandaBreakdownPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kanda-reports.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_date.date' => db_trans('validation_date'),
            'to_date.date' => db_trans('validation_date'),
            'to_date.after_or_equal' => db_trans('validation_to_date_after_or_equal_from_date'),
        ];
    }

    public function validatedFilters(): array
    {
        $validated = $this->validated();

        return [
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];
    }
}