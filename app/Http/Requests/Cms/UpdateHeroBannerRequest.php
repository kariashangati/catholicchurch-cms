<?php

namespace App\Http\Requests\Cms;

class UpdateHeroBannerRequest extends StoreHeroBannerRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.heroes.update') ?? false;
    }
}
