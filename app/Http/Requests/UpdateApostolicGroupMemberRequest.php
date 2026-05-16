<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApostolicGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('apostolic-groups.members.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['member', 'leader', 'assistant_leader', 'secretary', 'treasurer', 'patron'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'joined_at' => ['nullable', 'date'],
            'left_at' => ['nullable', 'date', 'after_or_equal:joined_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}