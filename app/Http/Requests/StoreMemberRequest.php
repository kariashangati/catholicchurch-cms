<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('members.create');
    }

    public function rules(): array
    {
        return [
            'familia_id' => ['required', 'integer', 'exists:familias,id'],

            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'gender' => ['nullable', 'in:Male,Female'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],

            'occupation' => ['nullable', 'string', 'max:150'],
            'family_role' => ['nullable', 'in:Father,Mother,Child,Other'],

            'member_code' => ['nullable', 'string', 'max:50', 'unique:members,member_code'],
            'bahasha' => ['nullable', 'string', 'max:50'],

            'marriage_type' => ['nullable', 'string', 'max:100'],
            'baptism_certificate_number' => ['nullable', 'string', 'max:100'],
            'marriage_certificate_number' => ['nullable', 'string', 'max:100'],
            'baptism_parish' => ['nullable', 'string', 'max:150'],
            'baptism_diocese' => ['nullable', 'string', 'max:150'],

            'notes' => ['nullable', 'string'],

            'is_baptized' => ['nullable', 'boolean'],
            'has_communion' => ['nullable', 'boolean'],
            'has_confirmation' => ['nullable', 'boolean'],
            'receives_eucharist' => ['nullable', 'boolean'],
            'is_married' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->normalizeText($this->first_name),
            'middle_name' => $this->normalizeText($this->middle_name),
            'last_name' => $this->normalizeText($this->last_name),
            'phone' => $this->normalizeNullableString($this->phone),
            'occupation' => $this->normalizeNullableString($this->occupation),
            'family_role' => $this->normalizeNullableString($this->family_role),
            'member_code' => $this->normalizeNullableString($this->member_code),
            'bahasha' => $this->normalizeNullableString($this->bahasha),
            'marriage_type' => $this->normalizeNullableString($this->marriage_type),
            'baptism_certificate_number' => $this->normalizeNullableString($this->baptism_certificate_number),
            'marriage_certificate_number' => $this->normalizeNullableString($this->marriage_certificate_number),
            'baptism_parish' => $this->normalizeNullableString($this->baptism_parish),
            'baptism_diocese' => $this->normalizeNullableString($this->baptism_diocese),
            'notes' => $this->normalizeNullableString($this->notes),

            'is_baptized' => $this->boolean('is_baptized'),
            'has_communion' => $this->boolean('has_communion'),
            'has_confirmation' => $this->boolean('has_confirmation'),
            'receives_eucharist' => $this->boolean('receives_eucharist'),
            'is_married' => $this->boolean('is_married'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }

    public function messages(): array
    {
        return [
            'familia_id.required' => db_trans('validation_familia_required'),
            'familia_id.exists' => db_trans('validation_familia_invalid'),

            'first_name.required' => db_trans('validation_first_name_required'),
            'last_name.required' => db_trans('validation_last_name_required'),

            'gender.in' => db_trans('validation_gender_invalid'),
            'date_of_birth.date' => db_trans('validation_date_invalid'),
            'date_of_birth.before_or_equal' => db_trans('validation_date_of_birth_invalid'),

            'member_code.unique' => db_trans('validation_member_code_unique'),
        ];
    }

    private function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}