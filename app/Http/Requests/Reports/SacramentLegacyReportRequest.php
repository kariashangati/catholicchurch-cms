<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class SacramentLegacyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familias,id'],
            'member_filter' => ['nullable', 'in:all,baptized,not_baptized,married,not_married,confirmed,not_confirmed,communion,not_communion,eucharist,not_eucharist,male,female'],
            'view_mode' => ['nullable', 'in:summary,detailed'],
        ];
    }
}
