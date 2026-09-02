<?php

namespace Spiggle\FormBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\FormTemplates;

class SampleFormsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FormTemplates::seederDefinitions() as $definition) {
            $submissions = $definition['submissions'] ?? [];
            unset($definition['submissions']);

            $form = Form::withTrashed()->updateOrCreate(
                ['slug' => $definition['slug']],
                array_merge($definition, ['deleted_at' => null])
            );

            if ($form->submissions()->exists()) {
                continue;
            }

            foreach ($submissions as $index => $data) {
                FormSubmission::query()->create([
                    'form_id' => $form->id,
                    'status' => $index === 0 ? 'reviewed' : 'new',
                    'data' => $data,
                    'meta' => ['source' => 'seeder'],
                    'created_at' => now()->subDays($index)->subHours($index + 1),
                ]);
            }
        }
    }
}
