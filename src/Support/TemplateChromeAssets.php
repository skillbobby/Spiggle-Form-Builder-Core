<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Facades\Route;

class TemplateChromeAssets
{
    public static function registerRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->get('/vendor/spiggle-form-builder/template-chrome/{file}', static function (string $file) {
                $filename = basename($file);
                if (! preg_match('/^[\w\-]+\.(svg|webp)$/i', $filename)) {
                    abort(404);
                }

                $base = realpath(__DIR__.'/../../resources/images/template-chrome');
                $path = realpath($base.'/'.$filename);

                if ($base === false || $path === false || ! str_starts_with($path, $base) || ! is_file($path)) {
                    abort(404);
                }

                $mime = str_ends_with(strtolower($filename), '.webp') ? 'image/webp' : 'image/svg+xml';

                return response()->file($path, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            })
            ->where('file', '[\w\-]+\.(svg|webp)')
            ->name('form-builder.template-chrome');
    }

    public static function url(string $filename): string
    {
        $published = public_path('vendor/spiggle-form-builder/template-chrome/'.basename($filename));

        if (is_file($published)) {
            return '/vendor/spiggle-form-builder/template-chrome/'.basename($filename);
        }

        return route('form-builder.template-chrome', ['file' => basename($filename)]);
    }
}
