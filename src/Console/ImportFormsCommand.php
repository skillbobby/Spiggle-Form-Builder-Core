<?php

namespace Spiggle\FormBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;

class ImportFormsCommand extends Command
{
    protected $signature = 'form-builder:import
                            {path : JSON file produced by form-builder:export}
                            {--publish : Mark imported forms as published}';

    protected $description = 'Import form definitions from a portable JSON document';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! File::exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $decoded = json_decode(File::get($path), true);
        if (! is_array($decoded)) {
            $this->error('Invalid JSON.');

            return self::FAILURE;
        }

        if (isset($decoded['schema_version']) && isset($decoded['schema'])) {
            $decoded = [$decoded];
        }

        $created = 0;
        foreach ($decoded as $document) {
            if (! is_array($document)) {
                continue;
            }

            $name = (string) ($document['name'] ?? 'Imported form');
            $slug = Str::slug($name).'-'.substr(sha1((string) ($document['form_id'] ?? Str::uuid())), 0, 6);

            Form::query()->create([
                'name' => $name,
                'slug' => $slug,
                'base_path' => PathResolver::unique((string) ($document['base_path'] ?? $slug)),
                'container_type' => (string) ($document['container_type'] ?? 'single'),
                'schema_version' => (string) ($document['schema_version'] ?? '1.0'),
                'schema' => SchemaNormalizer::normalize($document['schema'] ?? []),
                'is_published' => (bool) $this->option('publish'),
                'is_active' => true,
            ]);
            $created++;
        }

        $this->info("Imported {$created} form(s).");

        return self::SUCCESS;
    }
}
