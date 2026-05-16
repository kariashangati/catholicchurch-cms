<?php

namespace App\Services\Cms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CmsMediaUploadService
{
    public function store(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($directory, 'public');

        return Storage::url($path);
    }
}
