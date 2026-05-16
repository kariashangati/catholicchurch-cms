<?php

namespace App\Http\Requests\Communication;

use App\Models\CommunicationTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunicationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.manage_templates') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:communication_templates,code'],
            'channel' => ['required', 'string', Rule::in([CommunicationTemplate::CHANNEL_SMS])],
            'category' => ['required', 'string', 'max:50'],
            'locale' => ['required', 'string', Rule::in(['sw', 'en'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'event_key' => ['nullable', 'string', 'max:100'],
            'audience_type' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in([
                CommunicationTemplate::STATUS_DRAFT,
                CommunicationTemplate::STATUS_ACTIVE,
                CommunicationTemplate::STATUS_INACTIVE,
                CommunicationTemplate::STATUS_ARCHIVED,
            ])],
            'notes' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['nullable', 'string', 'max:100'],
        ];
    }
}
