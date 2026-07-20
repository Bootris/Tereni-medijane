<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesMedia
{
    /** Absolute URL for a stored public-disk path (frontend lives on another domain). */
    protected function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return url(Storage::disk('public')->url($path));
    }
}
