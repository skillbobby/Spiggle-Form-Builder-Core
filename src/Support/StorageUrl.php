<?php

namespace Spiggle\FormBuilder\Support;

class StorageUrl
{
    /**
     * Resolve a stored image reference for the current request origin.
     *
     * Uploads store root-relative paths (/storage/...) so previews work when
     * APP_URL differs from the host/port you use in the browser (e.g. 127.0.0.1:8090).
     */
    public static function resolve(?string $urlOrPath): ?string
    {
        if (blank($urlOrPath)) {
            return null;
        }

        $urlOrPath = trim($urlOrPath);

        if (str_starts_with($urlOrPath, '/') && ! str_starts_with($urlOrPath, '//')) {
            return $urlOrPath;
        }

        if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
            $path = parse_url($urlOrPath, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : $urlOrPath;
        }

        if (str_starts_with($urlOrPath, 'storage/')) {
            return '/'.$urlOrPath;
        }

        return self::fromPublicDiskPath($urlOrPath);
    }

    public static function fromPublicDiskPath(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }
}
