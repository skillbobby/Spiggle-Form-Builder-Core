<?php

namespace Spiggle\FormBuilder;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spiggle\FormBuilder\Console\ExportFormsCommand;
use Spiggle\FormBuilder\Console\ImportFormsCommand;
use Spiggle\FormBuilder\Console\SeedSampleFormsCommand;
use Spiggle\FormBuilder\Console\VerifyFormBuilderCommand;
use Spiggle\FormBuilder\Http\Middleware\ResolveFormPath;
use Spiggle\FormBuilder\Livewire\PublicForm;
use Spiggle\FormBuilder\Services\AnalyticsService;
use Spiggle\FormBuilder\Services\AuditLogger;
use Spiggle\FormBuilder\Services\ExportService;
use Spiggle\FormBuilder\Services\FormRenderer;
use Spiggle\FormBuilder\Services\SubmissionManager;
use Spiggle\FormBuilder\Services\ValidationBuilder;

class FormBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/form-builder.php', 'form-builder');

        $this->app->singleton(FormRenderer::class);
        $this->app->singleton(ValidationBuilder::class);
        $this->app->singleton(SubmissionManager::class);
        $this->app->singleton(ExportService::class);
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(AnalyticsService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/form-builder.php' => config_path('form-builder.php'),
        ], 'form-builder-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'form-builder-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/form-builder'),
        ], 'form-builder-views');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'form-builder');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (class_exists(Livewire::class)) {
            Livewire::component('form-builder.public-form', PublicForm::class);
        }

        if (config('form-builder.root_paths')) {
            Route::aliasMiddleware('form-builder.resolve', ResolveFormPath::class);
            $this->app->make('router')->pushMiddlewareToGroup('web', ResolveFormPath::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedSampleFormsCommand::class,
                VerifyFormBuilderCommand::class,
                ExportFormsCommand::class,
                ImportFormsCommand::class,
            ]);
        }
    }
}
