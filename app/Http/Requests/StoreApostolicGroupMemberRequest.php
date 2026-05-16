<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApostolicGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('apostolic-groups.members.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
            'role' => ['nullable', Rule::in(['member', 'leader', 'assistant_leader', 'secretary', 'treasurer', 'patron'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}