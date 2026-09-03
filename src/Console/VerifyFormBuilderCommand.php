<?php

namespace Spiggle\FormBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Services\ExportService;
use Spiggle\FormBuilder\Services\FormRenderer;
use Spiggle\FormBuilder\Services\SubmissionManager;
use Spiggle\FormBuilder\Services\ValidationBuilder;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;

class VerifyFormBuilderCommand extends Command
{
    protected $signature = 'form-builder:verify';

    protected $description = 'Exercise schema, validation, persistence, routing, export, and UX contracts for Form Builder';

    public function handle(): int
    {
        $failures = 0;

        $failures += $this->assertTrue(Schema::hasTable(config('form-builder.tables.forms', 'form_builder_forms')), 'forms table exists');
        $failures += $this->assertTrue(Schema::hasTable(config('form-builder.tables.submissions', 'form_builder_submissions')), 'submissions table exists');
        $failures += $this->assertTrue(Schema::hasTable(config('form-builder.tables.audit_logs', 'form_builder_audit_logs')), 'audit_logs table exists');

        $failures += $this->assertTrue(FieldCatalog::requiresOptions('select'), 'select requires options (Dynamic Fields catalog)');
        $failures += $this->assertTrue(FieldCatalog::requiresOptions('multi_select'), 'multi_select requires options');
        $failures += $this->assertTrue(FieldCatalog::isBoolean('toggle'), 'toggle is boolean-like');
        $failures += $this->assertTrue(FieldCatalog::isFile('file'), 'file type detected');
        $failures += $this->assertTrue(array_key_exists('email', FieldCatalog::labels()), 'email type is available');

        $createPage = file_get_contents(
            dirname(__DIR__, 2).'/src/Filament/Resources/Forms/Pages/CreateForm.php'
        ) ?: '';
        $failures += $this->assertTrue(str_contains($createPage, "getResourceUrl('index')"), 'create form redirects to list index');

        $formSource = file_get_contents(
            dirname(__DIR__, 2).'/src/Filament/Resources/Forms/Schemas/FormForm.php'
        ) ?: '';
        $designerSource = file_get_contents(
            dirname(__DIR__, 2).'/src/Filament/Livewire/FormDesigner.php'
        ) ?: '';
        $editView = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/filament/resources/forms/pages/edit-form.blade.php'
        ) ?: '';
        $chooseStart = file_get_contents(
            dirname(__DIR__, 2).'/src/Filament/Resources/Forms/Pages/ChooseFormStart.php'
        ) ?: '';
        $failures += $this->assertTrue(str_contains($designerSource, 'class FormDesigner'), 'visual form designer component exists');
        $failures += $this->assertTrue(str_contains($designerSource, 'function save('), 'designer persists forms');
        $failures += $this->assertTrue(str_contains($editView, 'FormDesigner::class'), 'edit form loads visual designer');
        $failures += $this->assertTrue(str_contains($chooseStart, 'applyTemplate'), 'create wizard applies templates');
        $failures += $this->assertTrue(! str_contains($formSource, 'builderSchema'), 'legacy repeater builder removed');
        $failures += $this->assertTrue(str_contains($formSource, "['default' => 1, 'md' => 2]"), 'settings section is responsive');
        $failures += $this->assertTrue(str_contains($formSource, 'container_type'), 'settings include layout selector');

        $publicCss = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/layouts/public.blade.php'
        ) ?: '';
        $failures += $this->assertTrue(str_contains($publicCss, 'grid-template-columns: repeat(12'), 'public renderer uses 12-column grid');
        $failures += $this->assertTrue(str_contains($publicCss, '@media (min-width: 768px)'), 'public renderer stacks on mobile');

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $form = Form::factory()->create([
                'name' => 'Verify Contact',
                'slug' => 'verify-contact-'.substr(sha1(uniqid('', true)), 0, 8),
                'container_type' => 'wizard',
                'is_published' => true,
                'schema' => SchemaNormalizer::normalize([
                    [
                        'label' => 'Who',
                        'fields' => [
                            ['name' => 'full_name', 'type' => 'text', 'label' => 'Full name', 'required' => true, 'column_span' => 6],
                            ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true, 'column_span' => 6],
                        ],
                    ],
                    [
                        'label' => 'Details',
                        'fields' => [
                            ['name' => 'topic', 'type' => 'select', 'label' => 'Topic', 'required' => true, 'options' => [
                                ['label' => 'Hello', 'value' => 'hello'],
                            ]],
                            ['name' => 'agree', 'type' => 'toggle', 'label' => 'Agree', 'column_span' => 12],
                        ],
                    ],
                ]),
            ]);

            $doc = $form->document();
            $failures += $this->assertTrue(($doc['schema_version'] ?? null) === '1.0', 'portable document has schema_version');
            $failures += $this->assertTrue(($doc['form_id'] ?? '') === $form->uuid, 'portable document has form_id');
            $failures += $this->assertTrue(($doc['base_path'] ?? '') === $form->base_path, 'portable document has base_path');
            $failures += $this->assertTrue(($doc['container_type'] ?? '') === 'wizard', 'portable document has container_type');
            $failures += $this->assertTrue(is_array($doc['schema'] ?? null) && count($doc['schema']) === 2, 'portable document has schema containers');

            $components = app(FormRenderer::class)->toFilament($form);
            $failures += $this->assertTrue($components !== [], 'Filament renderer produced components');
            if (FeatureCatalog::proUnlocked()) {
                $failures += $this->assertTrue($components[0] instanceof \Filament\Schemas\Components\Wizard, 'wizard layout maps to Filament Wizard');
            } else {
                $failures += $this->assertTrue($components[0] instanceof \Filament\Schemas\Components\Section, 'Pro locked — wizard falls back to single-page sections');
            }

            $rules = app(ValidationBuilder::class)->rules($form);
            $failures += $this->assertTrue(isset($rules['data.full_name']), 'validation includes full_name');
            $invalid = Validator::make(['data' => []], $rules);
            $failures += $this->assertTrue($invalid->fails(), 'empty payload fails server validation');

            $valid = Validator::make(['data' => [
                'full_name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'topic' => 'hello',
                'agree' => true,
            ]], $rules);
            $failures += $this->assertTrue($valid->passes(), 'complete payload passes validation');

            $step0 = app(ValidationBuilder::class)->rules($form, 0);
            $failures += $this->assertTrue(isset($step0['data.full_name']) && ! isset($step0['data.topic']), 'wizard step 0 only validates first container');

            $submission = app(SubmissionManager::class)->capture($form, [
                'full_name' => '<b>Ada</b>',
                'email' => 'ada@example.com',
                'topic' => 'hello',
                'agree' => true,
            ], null, ['source' => 'verify']);

            $failures += $this->assertTrue($submission instanceof FormSubmission, 'submission created');
            $failures += $this->assertTrue($submission->data['full_name'] === 'Ada', 'HTML is sanitized on text fields');
            $failures += $this->assertTrue($form->fresh()->submissions()->count() === 1, 'submission persists against form');

            $clone = null;
            if (FeatureCatalog::proUnlocked()) {
                $clone = $form->cloneForm();
                $failures += $this->assertTrue($clone->id !== $form->id, 'clone creates a new form');
                $failures += $this->assertTrue($clone->base_path !== $form->base_path, 'clone gets a unique path');
                $failures += $this->assertTrue($clone->is_published === false, 'clone starts unpublished');
            } else {
                $this->warn('Pro locked — skipping clone checks.');
            }

            $failures += $this->assertTrue(PathResolver::conflicts($form->base_path), 'existing base_path is detected as a conflict');
            $unique = PathResolver::unique($form->base_path, $form->id);
            $failures += $this->assertTrue($unique === $form->base_path, 'unique() keeps path when ignoring self');

            $export = app(ExportService::class)->export('csv', $form);
            $failures += $this->assertTrue(is_file($export['absolute']), 'CSV export wrote a file');
            $csv = file_get_contents($export['absolute']) ?: '';
            $failures += $this->assertTrue(str_contains($csv, 'email'), 'CSV includes field headers');
            $failures += $this->assertTrue(str_contains($csv, 'ada@example.com'), 'CSV includes submission data');

            if (FeatureCatalog::proUnlocked()) {
                $xlsx = app(ExportService::class)->export('xlsx', $form);
                $failures += $this->assertTrue(is_file($xlsx['absolute']), 'XLSX export wrote a file');
                $pdf = app(ExportService::class)->export('pdf', $form);
                $failures += $this->assertTrue(is_file($pdf['absolute']), 'PDF export wrote a file');
            } else {
                $this->warn('Pro locked — skipping XLSX/PDF export checks.');
            }

            $url = $form->publicUrl();
            $failures += $this->assertTrue(str_contains($url, '/forms/'), 'public URL uses configured prefix');

            if (class_exists(\Spiggle\DynamicFields\Support\FieldTypes::class)) {
                $failures += $this->assertTrue(
                    FieldCatalog::labels() === \Spiggle\DynamicFields\Support\FieldTypes::labels(),
                    'FieldCatalog composes Dynamic Fields types'
                );
            } else {
                $this->warn('Dynamic Fields not installed — catalog is using local fallback types.');
            }

            $published = Form::query()->published()->whereKey($form->id)->exists();
            $failures += $this->assertTrue($published, 'published scope includes the verify form');
        } finally {
            \Illuminate\Support\Facades\DB::rollBack();
        }

        $this->printSummary($failures);

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function assertTrue(bool $condition, string $label): int
    {
        if ($condition) {
            $this->info("PASS  {$label}");

            return 0;
        }

        $this->error("FAIL  {$label}");

        return 1;
    }

    protected function printSummary(int $failures): void
    {
        if ($failures === 0) {
            $this->info('Form Builder verify: all checks passed.');

            return;
        }

        $this->error("Form Builder verify: {$failures} check(s) failed.");
    }
}
