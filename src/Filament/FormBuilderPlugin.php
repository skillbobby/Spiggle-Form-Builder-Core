<?php

namespace Spiggle\FormBuilder\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\FormSubmissionResource;

class FormBuilderPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'spiggle-form-builder';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            FormResource::class,
            FormSubmissionResource::class,
        ]);

        $licensePage = 'Spiggle\\FormBuilder\\Pro\\Filament\\Pages\\ManageAddonLicense';
        if (class_exists($licensePage)) {
            $panel->pages([$licensePage]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
