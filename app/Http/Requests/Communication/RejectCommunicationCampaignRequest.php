<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class RejectCommunicationCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.approve_campaigns') ?? false;
    }

    public function rules(): array
    {
        return [
            'approval_notes' => ['required', 'string', 'max:3000'],
        ];
    }
}
