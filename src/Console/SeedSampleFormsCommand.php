<?php

namespace Spiggle\FormBuilder\Console;

use Illuminate\Console\Command;
use Spiggle\FormBuilder\Database\Seeders\SampleFormsSeeder;

class SeedSampleFormsCommand extends Command
{
    protected $signature = 'form-builder:seed {--fresh : Delete existing sample forms first}';

    protected $description = 'Seed sample forms and submissions (contact, event, job, feedback)';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $slugs = ['contact-us', 'event-registration', 'job-application', 'customer-feedback'];
            \Spiggle\FormBuilder\Models\Form::query()->whereIn('slug', $slugs)->each(function ($form): void {
                $form->submissions()->delete();
                $form->delete();
            });
            $this->warn('Removed existing sample forms.');
        }

        (new SampleFormsSeeder)->run();
        $this->info('Sample forms seeded.');

        return self::SUCCESS;
    }
}
