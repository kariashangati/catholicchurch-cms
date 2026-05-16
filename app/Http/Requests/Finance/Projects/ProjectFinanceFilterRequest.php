<?php

namespace App\Http\Requests\Finance\Projects;

use App\Models\Project;
use App\Models\ProjectTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectFinanceFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'project_category_id' => ['nullable', 'integer', 'exists:project_categories,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'project_status' => ['nullable', Rule::in(Project::availableStatuses())],
            'transaction_type' => ['nullable', Rule::in(ProjectTransaction::availableTypes())],
            'transaction_status' => ['nullable', Rule::in(ProjectTransaction::availableStatuses())],
        ];
    }
}