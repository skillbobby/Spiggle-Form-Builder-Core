<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\FormRenderer;
use Spiggle\FormBuilder\Support\SchemaNormalizer;

it('renders a field schema without error', function () {
    $form = new Form([
        'name' => 'Contact',
        'container_type' => 'single',
        'schema' => SchemaNormalizer::normalize([
            [
                'label' => 'Details',
                'fields' => [
                    [
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'Email',
                        'required' => true,
                    ],
                ],
            ],
        ]),
    ]);

    $components = app(FormRenderer::class)->toFilament($form);

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Section::class);

    $field = collect($components[0]->getDefaultChildComponents())
        ->flatMap(fn ($child) => $child->getDefaultChildComponents())
        ->first(fn ($child) => $child instanceof TextInput);

    expect($field)->toBeInstanceOf(TextInput::class)
        ->and($field->getLabel())->toBe('Email')
        ->and($field->isRequired())->toBeTrue();
});
