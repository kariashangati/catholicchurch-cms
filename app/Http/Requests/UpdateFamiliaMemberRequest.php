<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFamiliaMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('familias.members.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $member = $this->route('member');

        $this->merge([
            'is_baptized' => $this->boolean('is_baptized'),
            'has_communion' => $this->boolean('has_communion'),
            'has_confirmation' => $this->boolean('has_confirmation'),
            'receives_eucharist' => $this->boolean('receives_eucharist'),
            'is_married' => $this->boolean('is_married'),
            'is_active' => $this->boolean('is_active', true),
            'form_context' => $this->input('form_context', 'member_edit_' . ($member?->id ?? 'unknown')),
        ]);
    }

    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'family_role' => ['nullable', Rule::in(['Father', 'Mother', 'Child', 'Other'])],
            'member_code' => ['nullable', 'string', 'max:100', Rule::unique('members', 'member_code')->ignore($member->id)],
            'bahasha' => ['nullable', 'string', 'max:100'],
            'is_baptized' => ['nullable', 'boolean'],
            'has_communion' => ['nullable', 'boolean'],
            'has_confirmation' => ['nullable', 'boolean'],
            'receives_eucharist' => ['nullable', 'boolean'],
            'is_married' => ['nullable', 'boolean'],
            'marriage_type' => ['nullable', 'string', 'max:100'],
            'baptism_certificate_number' => ['nullable', 'string', 'max:100'],
            'marriage_certificate_number' => ['nullable', 'string', 'max:100'],
            'baptism_parish' => ['nullable', 'string', 'max:255'],
            'baptism_diocese' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'form_context' => ['nullable', 'string', 'max:100'],
        ];
    }
}
