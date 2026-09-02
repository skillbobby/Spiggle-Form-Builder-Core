<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Models\Form;

class PathResolver
{
    public static function suggest(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'form';
        }

        return self::unique($base, $ignoreId);
    }

    public static function unique(string $path, ?int $ignoreId = null): string
    {
        $path = trim($path, '/');
        $path = Str::slug(str_replace('/', '-', $path));
        if ($path === '') {
            $path = 'form';
        }

        if (! self::conflicts($path, $ignoreId)) {
            return $path;
        }

        $hash = substr(sha1($path.microtime(true)), 0, 4);

        return self::unique($path.'-f'.$hash, $ignoreId);
    }

    public static function conflicts(string $path, ?int $ignoreId = null): bool
    {
        $path = trim($path, '/');

        $reserved = config('form-builder.reserved_paths', []);
        if (in_array($path, $reserved, true) || in_array(Str::before($path, '/'), $reserved, true)) {
            return true;
        }

        $exists = Form::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($path): void {
                $q->where('base_path', $path)->orWhere('slug', $path);
            })
            ->exists();

        if ($exists) {
            return true;
        }

        $prefix = trim((string) config('form-builder.route_prefix', 'forms'), '/');
        $full = $prefix !== '' ? $prefix.'/'.$path : $path;

        foreach (Route::getRoutes() as $route) {
            $uri = trim($route->uri(), '/');
            if ($uri === $path || $uri === $full) {
                return true;
            }
        }

        return false;
    }

    public static function publicUrl(Form $form): string
    {
        $prefix = trim((string) config('form-builder.route_prefix', 'forms'), '/');
        $path = trim($form->base_path, '/');

        return url($prefix !== '' ? $prefix.'/'.$path : $path);
    }

    public static function previewUrl(Form $form): string
    {
        return self::publicUrl($form).'?preview=1';
    }
}
