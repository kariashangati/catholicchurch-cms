<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApostolicGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('apostolic-groups.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:apostolic_groups,name'],
            'code' => ['required', 'string', 'max:50', 'unique:apostolic_groups,code'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'leader_member_id' => ['nullable', 'exists:members,id'],
            'assistant_leader_member_id' => ['nullable', 'different:leader_member_id', 'exists:members,id'],
            'patron_member_id' => ['nullable', 'different:leader_member_id', 'different:assistant_leader_member_id', 'exists:members,id'],
            'membership_rule_type' => ['required', Rule::in(['manual', 'gender', 'family_role'])],
            'membership_rule_value' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'founded_on' => ['nullable', 'date'],
            'meeting_day' => ['nullable', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'meeting_time' => ['nullable', 'date_format:H:i'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
        ];
    }
}