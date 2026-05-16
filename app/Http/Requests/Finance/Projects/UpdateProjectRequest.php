<?php

namespace App\Http\Requests\Finance\Projects;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $projectId = is_object($project) ? $project->id : $project;

        return [
            'project_category_id' => ['required', 'integer', 'exists:project_categories,id'],
            'name' => ['required', 'string', 'max:180', Rule::unique('projects', 'name')->ignore($projectId)],
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