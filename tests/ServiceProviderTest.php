<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spiggle\FormBuilder\FormBuilderServiceProvider;
use Spiggle\FormBuilder\Services\FormRenderer;

it('boots the service provider', function () {
    expect(app()->getProvider(FormBuilderServiceProvider::class))->not->toBeNull()
        ->and(config('form-builder.route_prefix'))->toBe('forms')
        ->and(app(FormRenderer::class))->toBeInstanceOf(FormRenderer::class)
        ->and(Route::has('form-builder.public'))->toBeTrue()
        ->and(Livewire::exists('form-builder.public-form'))->toBeTrue()
        ->and(Livewire::exists('form-builder.form-designer'))->toBeTrue();
});
