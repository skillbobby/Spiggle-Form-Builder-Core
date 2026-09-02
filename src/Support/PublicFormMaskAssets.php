<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Facades\Route;

class PublicFormMaskAssets
{
    public static function registerRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->get('/vendor/spiggle-form-builder/public-form-mask.js', static function () {
                $path = realpath(__DIR__.'/../../resources/dist/public-form-mask.js');

                if ($path === false || ! is_file($path)) {
                    abort(404);
                }

                return response()->file($path, [
                    'Content-Type' => 'application/javascript; charset=UTF-8',
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            })
            ->name('form-builder.public-form-mask');
    }

    public static function url(): string
    {
        return route('form-builder.public-form-mask');
    }
}
