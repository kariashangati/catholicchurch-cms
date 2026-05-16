<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class CancelCommunicationCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.cancel_campaigns') ?? false;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:3000'],
        ];
    }
}
