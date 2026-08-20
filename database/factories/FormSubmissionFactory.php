<?php

namespace Spiggle\FormBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'form_id' => Form::factory(),
            'status' => fake()->randomElement(['new', 'reviewed', 'archived']),
            'data' => [
                'full_name' => fake()->name(),
                'email' => fake()->safeEmail(),
            ],
            'meta' => ['source' => 'factory'],
        ];
    }
}
