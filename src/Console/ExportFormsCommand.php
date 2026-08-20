<?php

namespace Spiggle\FormBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spiggle\FormBuilder\Models\Form;

class ExportFormsCommand extends Command
{
    protected $signature = 'form-builder:export
                            {--slug= : Limit to one form slug}
                            {--path= : Output JSON path (default: storage/app/form-builder-export.json)}';

    protected $description = 'Export form definitions (SRD portable documents) to JSON';

    public function handle(): int
    {
        $query = Form::query()->orderBy('name');
        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $payload = $query->get()->map(fn (Form $form) => $form->document())->values()->all();
        $path = $this->option('path') ?: storage_path('app/form-builder-export.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Exported '.count($payload)." form(s) to {$path}");

        return self::SUCCESS;
    }
}
