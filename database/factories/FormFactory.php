<?php

namespace Spiggle\FormBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Models\Form;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' Form';

        return [
            'uuid' => (string) Str::uuid(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'base_path' => Str::slug($name),
            'description' => fake()->sentence(),
            'container_type' => 'single',
            'schema_version' => '1.0',
            'schema' => [
                [
                    'label' => 'Details',
                    'fields' => [
                        [
                            'name' => 'full_name',
                            'type' => 'text',
                            'label' => 'Full name',
                            'required' => true,
                            'column_span' => 6,
                        ],
                        [
                            'name' => 'email',
                            'type' => 'email',
                            'label' => 'Email',
                            'required' => true,
                            'column_span' => 6,
                            'validation_rules' => ['email'],
                        ],
                    ],
                ],
            ],
            'settings' => ['label_position' => 'above'],
            'is_published' => true,
            'is_active' => true,
            'success_message' => 'Thanks — we received your response.',
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function wizard(): static
    {
        return $this->state(['container_type' => 'wizard']);
    }
}
