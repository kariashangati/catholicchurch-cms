<?php

namespace App\Http\Requests\Cms;

class UpdatePageRequest extends StorePageRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.pages.update') ?? false;
    }
}