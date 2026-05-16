<?php

namespace App\Http\Requests\Cms;

class UpdateAnnouncementRequest extends StoreAnnouncementRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.announcements.update') ?? false;
    }
}