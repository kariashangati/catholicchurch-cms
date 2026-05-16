<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { $id = $this->route('service_category')->id; return ['name' => ['required','string','max:255','unique:service_categories,name,' . $id],'description' => ['nullable','string','max:2000'],'is_active' => ['nullable','boolean']]; }
}
