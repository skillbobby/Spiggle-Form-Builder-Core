<?php

use Illuminate\Support\Facades\Route;
use Spiggle\FormBuilder\Livewire\PublicForm;

$prefix = trim((string) config('form-builder.route_prefix', 'forms'), '/');

Route::middleware('web')
    ->prefix($prefix)
    ->group(function (): void {
        Route::get('/{path}', PublicForm::class)
            ->where('path', '.*')
            ->name('form-builder.public');
    });
