<?php

namespace App\Http\Requests\Finance\Projects;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_category_id' => ['required', 'integer', 'exists:project_categories,id'],
            'name' => ['required', 'string', 'max:180', 'unique:projects,name'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(Project::availableStatuses())],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}