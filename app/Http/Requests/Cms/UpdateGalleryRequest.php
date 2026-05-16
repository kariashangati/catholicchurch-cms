<?php

namespace App\Http\Requests\Cms;

class UpdateGalleryRequest extends StoreGalleryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.galleries.update') ?? false;
    }
}
