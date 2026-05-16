<?php

namespace App\Http\Requests\Access;

use App\Support\AdminScope;
use App\Models\Jumuiya;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access.users.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'scope_type' => $this->input('scope_type', AdminScope::TYPE_GLOBAL),
            'kanda_id' => $this->filled('kanda_id') ? (int) $this->input('kanda_id') : null,
            'jumuiya_id' => $this->filled('jumuiya_id') ? (int) $this->input('jumuiya_id') : null,
            'member_id' => $this->filled('member_id') ? (int) $this->input('member_id') : null,
            'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? $this->input('is_active'),
            'send_sms' => filter_var($this->input('send_sms'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? $this->input('send_sms'),
        ]);
    }

    public function rules(): array
    {
        return [
            'scope_type' => ['required', Rule::in([
                AdminScope::TYPE_GLOBAL,
                AdminScope::TYPE_KANDA,
                AdminScope::TYPE_JUMUIYA,
            ])],

            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],

            'member_id' => [
                'required',
                'integer',
                'exists:members,id',
                Rule::unique('users', 'member_id'),
            ],

            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'locale' => ['required', Rule::in(['en', 'sw'])],
            'is_active' => ['required', 'boolean'],

            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],

            'password_mode' => ['required', Rule::in(['manual', 'generated'])],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'send_sms' => ['nullable', 'boolean'],
            'sms_template_locale' => ['nullable', Rule::in(['en', 'sw'])],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $scopeType = $this->input('scope_type');
                $kandaId = $this->integer('kanda_id') ?: null;
                $jumuiyaId = $this->integer('jumuiya_id') ?: null;

                $member = Member::query()
                    ->with(['familia.jumuiya'])
                    ->find($this->integer('member_id'));

                if ($this->input('password_mode') === 'manual' && blank($this->input('password'))) {
                    $validator->errors()->add('password', 'Password is required when manual password mode is selected.');
                }

                if ($scopeType === AdminScope::TYPE_GLOBAL) {
                    if ($kandaId !== null) {
                        $validator->errors()->add('kanda_id', 'Global users must not have a kanda assigned.');
                    }

                    if ($jumuiyaId !== null) {
                        $validator->errors()->add('jumuiya_id', 'Global users must not have a jumuiya assigned.');
                    }
                }

                if ($scopeType === AdminScope::TYPE_KANDA) {
                    if ($kandaId === null) {
                        $validator->errors()->add('kanda_id', 'Kanda scope requires a selected kanda.');
                    }

                    if ($jumuiyaId !== null) {
                        $validator->errors()->add('jumuiya_id', 'Kanda-scoped users must not have a jumuiya assigned.');
                    }
                }

                if ($scopeType === AdminScope::TYPE_JUMUIYA) {
                    if ($jumuiyaId === null) {
                        $validator->errors()->add('jumuiya_id', 'Jumuiya scope requires a selected jumuiya.');
                    }
                }

                $jumuiya = null;

                if ($jumuiyaId !== null) {
                    $jumuiya = Jumuiya::query()->find($jumuiyaId);

                    if ($jumuiya && $kandaId !== null && (int) $jumuiya->kanda_id !== (int) $kandaId) {
                        $validator->errors()->add('jumuiya_id', 'Selected jumuiya does not belong to the selected kanda.');
                    }
                }

                if (! $member) {
                    return;
                }

                $memberJumuiyaId = $member->familia?->jumuiya_id;
                $memberKandaId = $member->familia?->jumuiya?->kanda_id;

                if ($scopeType === AdminScope::TYPE_KANDA) {
                    if ((int) $memberKandaId !== (int) $kandaId) {
                        $validator->errors()->add('member_id', 'Selected member does not belong to the selected kanda.');
                    }
                }

                if ($scopeType === AdminScope::TYPE_JUMUIYA) {
                    if ((int) $memberJumuiyaId !== (int) $jumuiyaId) {
                        $validator->errors()->add('member_id', 'Selected member does not belong to the selected jumuiya.');
                    }

                    if ($jumuiya && (int) $memberKandaId !== (int) $jumuiya->kanda_id) {
                        $validator->errors()->add('member_id', 'Selected member does not belong to the selected jumuiya hierarchy.');
                    }
                }
            },
        ];
    }
}