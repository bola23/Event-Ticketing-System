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

        $url = Storage::disk('public')->url($path);

        // The public disk builds its URLs from APP_URL, which routinely disagrees with the
        // host the site is actually being browsed on (localhost vs 127.0.0.1:8000, a Laragon
        // vhost, a staging domain). Since these files are served by this application out of
        // public/storage, link to them by root-relative path so the host never matters. A disk
        // deliberately pointed somewhere else — a CDN, say — keeps its absolute URL.
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($urlHost === null || $urlHost === $appHost) {
            return parse_url($url, PHP_URL_PATH) ?: $url;
        }

        return $url;
    }
}
