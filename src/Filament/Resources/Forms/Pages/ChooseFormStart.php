<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\FormTemplates;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;
use Spiggle\FormBuilder\Support\TemplateCategories;

class ChooseFormStart extends Page
{
    protected static string $resource = FormResource::class;

    protected static ?string $title = 'Create form';

    protected static bool $shouldRegisterNavigation = false;

    /** Wizard step: 1 = start, 2 = category, 3 = template */
    public int $step = 1;

    public ?string $selectedCategory = null;

    public ?string $selectedTemplate = null;

    public string $searchQuery = '';

    protected string $view = 'form-builder::filament.resources.forms.pages.choose-form-start';

    public function showTemplates(): void
    {
        $this->step = 2;
    }

    public function selectCategory(string $key): void
    {
        $this->selectedCategory = $key;
        $this->selectedTemplate = null;
        $this->searchQuery = '';
        $this->step = 3;
    }

    public function selectTemplate(string $slug): void
    {
        $this->selectedTemplate = $slug;
    }

    public function goBack(): void
    {
        if ($this->step === 3) {
            $this->step = 2;
            $this->selectedCategory = null;
            $this->selectedTemplate = null;
            $this->searchQuery = '';

            return;
        }

        if ($this->step === 2) {
            $this->step = 1;
        }
    }

    public function confirmTemplate(): void
    {
        if ($this->selectedTemplate === null) {
            return;
        }

        $this->applyTemplate($this->selectedTemplate);
    }

    public function createBlank(): void
    {
        $form = Form::query()->create([
            'name' => 'Untitled form',
            'slug' => FormTemplates::uniqueSlug('untitled-form'),
            'base_path' => PathResolver::suggest('Untitled form'),
            'container_type' => 'single',
            'schema' => SchemaNormalizer::normalize([
                [
                    'label' => 'Details',
                    'fields' => [],
                ],
            ]),
            'is_published' => false,
            'is_active' => true,
            'success_message' => 'Thanks — your response has been recorded.',
        ]);

        $this->redirect(static::getResource()::getUrl('edit', ['record' => $form]));
    }

    public function applyTemplate(string $slug): void
    {
        if (FormTemplates::isPro($slug) && ! \Spiggle\FormBuilder\Support\FeatureCatalog::proUnlocked()) {
            ProUpsell::notify('Pro form templates');

            return;
        }

        $template = FormTemplates::find($slug);
        if ($template === null) {
            return;
        }

        $definition = FormTemplates::definition($slug);
        $requestedLayout = (string) ($template['definition']['container_type'] ?? 'single');
        $resolvedLayout = ContainerTypes::resolve($requestedLayout);

        if ($requestedLayout !== $resolvedLayout) {
            Notification::make()
                ->warning()
                ->title('Layout adjusted')
                ->body('Without Pro, advanced layouts save as single page on the public form.')
                ->send();
        }

        $name = (string) $definition['name'];
        $uniqueSlug = FormTemplates::uniqueSlug((string) $definition['slug']);

        $form = Form::query()->create([
            'name' => $name,
            'slug' => $uniqueSlug,
            'base_path' => PathResolver::suggest($name),
            'description' => $definition['description'] ?? null,
            'container_type' => $resolvedLayout,
            'schema' => $definition['schema'],
            'settings' => $definition['settings'] ?? [],
            'is_published' => false,
            'is_active' => true,
            'success_message' => $definition['success_message'] ?? 'Thanks — your response has been recorded.',
        ]);

        $this->redirect(static::getResource()::getUrl('edit', ['record' => $form]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function filteredTemplates(): array
    {
        if ($this->selectedCategory === null) {
            return [];
        }

        $grouped = $this->templatesByCategory();
        $templates = $grouped[$this->selectedCategory]['templates'] ?? [];
        $needle = strtolower(trim($this->searchQuery));

        if ($needle === '') {
            return $templates;
        }

        return array_values(array_filter(
            $templates,
            fn (array $template): bool => str_contains(strtolower((string) $template['name']), $needle)
                || str_contains(strtolower((string) ($template['description'] ?? '')), $needle),
        ));
    }

    public function selectedCategoryLabel(): string
    {
        if ($this->selectedCategory === null) {
            return '';
        }

        return TemplateCategories::label($this->selectedCategory);
    }

    /**
     * @return array<string, array{category: array{label: string, icon: string}, templates: list<array<string, mixed>>}>
     */
    public function templatesByCategory(): array
    {
        return FormTemplates::groupedByCategory();
    }
}
