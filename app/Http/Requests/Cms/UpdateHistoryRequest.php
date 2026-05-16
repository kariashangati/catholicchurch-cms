<?php

namespace App\Http\Requests\Cms;

class UpdateHistoryRequest extends StoreHistoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.histories.update') ?? false;
    }
}
