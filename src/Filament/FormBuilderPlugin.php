<?php

namespace Spiggle\FormBuilder\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
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
        $panel->assets([
            Css::make('form-designer', __DIR__.'/../../resources/css/form-designer.css'),
        ]);

        $panel->resources([
            FormResource::class,
            FormSubmissionResource::class,
        ]);

        if (class_exists(\Spiggle\DynamicFields\Licensing\Filament\SpiggleLicensingPlugin::class)) {
            $panel->plugin(\Spiggle\DynamicFields\Licensing\Filament\SpiggleLicensingPlugin::make());
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
