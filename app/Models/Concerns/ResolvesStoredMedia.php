<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesStoredMedia
{
    /**
     * Resolve a stored media column to a browser-usable URL.
     *
     * Values are either a path on the public disk (uploaded through the admin) or an
     * absolute/rooted URL (older seeded records pointed at external images). Both need to keep
     * working, so absolute values pass through untouched.
     */
    protected function storedMediaUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
