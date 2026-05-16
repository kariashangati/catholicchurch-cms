<?php

namespace App\Http\Requests;

use App\Models\MafundishoEnrollment;
use App\Models\TeachingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMafundishoEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('mafundisho.update');
    }

    protected function prepareForValidation(): void
    {
        $teachingTypeId = $this->input('teaching_type_id');

        if (!$teachingTypeId && $this->filled('type')) {
            $teachingTypeId = TeachingType::query()
                ->where('slug', $this->input('type'))
                ->where('is_active', true)
                ->value('id');
        }

        $teachingType = $teachingTypeId
            ? TeachingType::query()->whereKey($teachingTypeId)->first()
            : null;

        $this->merge([
            'teaching_type_id' => $teachingTypeId ? (int) $teachingTypeId : null,
            'type' => $teachingType?->slug ?: $this->input('type'),
            'status' => $this->normalizeStatus($this->input('status')),
            'member_type_year_unique' => '1',
        ]);
    }

    public function rules(): array
    {
        $enrollmentId = $this->route('mafundisho_enrollment')?->id;

        return [
            'member_id' => ['required', 'exists:members,id'],

            'teaching_type_id' => [
                'required',
                'integer',
                Rule::exists('teaching_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],

            'type' => ['nullable', 'string', 'max:255'],

            'year' => ['required', 'digits:4', 'integer', 'min:1990', 'max:' . (now()->year + 5)],
            'status' => ['required', Rule::in(MafundishoEnrollment::availableStatuses())],

            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],

            'partner_name' => ['nullable', 'string', 'max:255'],
            'partner_jumuiya' => ['nullable', 'string', 'max:255'],
            'partner_phone' => ['nullable', 'string', 'max:50'],

            'notes' => ['nullable', 'string'],
            'form_context' => ['nullable', 'string', 'max:50'],

            'member_type_year_unique' => [
                Rule::unique('mafundisho_enrollments')
                    ->where(fn ($query) => $query
                        ->where('member_id', $this->input('member_id'))
                        ->where('teaching_type_id', $this->input('teaching_type_id'))
                        ->where('year', $this->input('year'))
                    )
                    ->ignore($enrollmentId),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $teachingType = TeachingType::query()
                ->whereKey($this->input('teaching_type_id'))
                ->first();

            if (!$teachingType) {
                return;
            }

            if ($teachingType->requires_partner_info && blank($this->input('partner_name'))) {
                $validator->errors()->add(
                    'partner_name',
                    db_trans('validation_partner_name_required_for_marriage_teaching')
                );
            }

            if ($this->input('status') === MafundishoEnrollment::STATUS_COMPLETED && blank($this->input('ended_at'))) {
                $validator->errors()->add(
                    'ended_at',
                    db_trans('validation_completion_date_required')
                );
            }
        });
    }

    protected function normalizeStatus(?string $status): string
    {
        return match ($status) {
            null, '', MafundishoEnrollment::STATUS_ACTIVE => MafundishoEnrollment::STATUS_CONTINUING,
            default => $status,
        };
    }
}