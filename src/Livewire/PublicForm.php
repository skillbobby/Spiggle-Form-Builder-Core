<?php

namespace Spiggle\FormBuilder\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\FormRenderer;
use Spiggle\FormBuilder\Services\SubmissionManager;
use Spiggle\FormBuilder\Services\ValidationBuilder;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\FieldVisibility;
use Spiggle\FormBuilder\Support\PageChrome;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;
use Spiggle\FormBuilder\Support\ThankYouLayouts;

class PublicForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    #[Locked]
    public int $formId;

    public array $data = [];

    public int $step = 0;

    /** @var list<int> */
    public array $openSections = [0];

    /** @var array<string, string> */
    public array $tagDraft = [];

    public bool $submitted = false;

    public ?string $successMessage = null;

    public ?string $redirectUrl = null;

    public ?string $submittedAt = null;

    public ?string $unavailabilityReason = null;

    public bool $previewMode = false;

    public function mount(string $path): void
    {
        $path = trim($path, '/');

        $form = Form::query()
            ->where(function ($q) use ($path): void {
                $q->where('base_path', $path)->orWhere('slug', $path);
            })
            ->first();

        if (! $form) {
            abort(404);
        }

        if (! $form->is_published) {
            if ($this->allowDraftPreview()) {
                $this->previewMode = true;
            } else {
                abort(404);
            }
        }

        $this->formId = $form->id;
        $this->unavailabilityReason = $form->is_published ? $form->unavailabilityReason() : null;

        if ($this->unavailabilityReason !== null) {
            return;
        }

        $this->hydrateDraft($form);
        $this->editors->fill($this->data);
    }

    public function editors(Schema $schema): Schema
    {
        $renderer = app(FormRenderer::class);
        $components = [];

        foreach ($this->form()->fields() as $field) {
            if (($field['type'] ?? '') !== 'textarea' || empty($field['meta']['use_editor'])) {
                continue;
            }

            $name = (string) $field['name'];
            $components[] = $renderer->makeRichEditor(
                $field,
                $name,
                hideLabel: true,
            );
        }

        return $schema
            ->components($components)
            ->statePath('data');
    }

    public function editorField(string $name): ?Htmlable
    {
        $component = $this->getSchema('editors')?->getComponentByStatePath(
            'data.'.$name,
            withHidden: true,
            withAbsoluteStatePath: true,
        );

        return $component instanceof Htmlable ? $component : null;
    }

    public function form(): Form
    {
        $query = Form::query();

        if (! $this->previewMode) {
            $query->published();
        }

        return $query->findOrFail($this->formId);
    }

    public function isAvailable(): bool
    {
        if ($this->previewMode) {
            return false;
        }

        return $this->unavailabilityReason === null && $this->form()->isPubliclyAvailable();
    }

    protected function allowDraftPreview(): bool
    {
        return request()->boolean('preview')
            && AuthorizesFormBuilder::userCanManageForms();
    }

    public function resolvedLayout(): string
    {
        return ContainerTypes::resolve($this->form()->container_type ?: 'single');
    }

    public function updated(string $property): void
    {
        if (! str_starts_with($property, 'data.')) {
            return;
        }

        $name = substr($property, 5);
        if ($name === '' || str_ends_with($name, '_raw')) {
            return;
        }

        $this->normalizeIncoming();
        $rules = app(ValidationBuilder::class)->rules($this->form(), null, $this->data);
        if (! isset($rules[$property])) {
            return;
        }

        $this->validateOnly(
            $property,
            $rules,
            [],
            app(ValidationBuilder::class)->attributes($this->form())
        );
    }

    public function nextStep(): void
    {
        $form = $this->form();
        if (! ContainerTypes::usesStepNav($this->resolvedLayout())) {
            return;
        }
        $this->validateCurrentSection();
        $this->saveDraft($form);
        $max = max(0, count($form->schema ?? []) - 1);
        $this->step = min($this->step + 1, $max);
        $this->ensureSectionOpen($this->step);
    }

    public function previousStep(): void
    {
        $this->step = max(0, $this->step - 1);
        $this->ensureSectionOpen($this->step);
        $this->saveDraft($this->form());
    }

    public function goToStep(int $step): void
    {
        $form = $this->form();
        $max = max(0, count($form->schema ?? []) - 1);
        $target = max(0, min($step, $max));

        if ($target === $this->step) {
            return;
        }

        if ($target > $this->step) {
            $this->validateCurrentSection();
        }

        $this->step = $target;
        $this->ensureSectionOpen($this->step);
        $this->saveDraft($form);
    }

    public function toggleSection(int $index): void
    {
        if (in_array($index, $this->openSections, true)) {
            $this->openSections = array_values(array_filter(
                $this->openSections,
                fn (int $open): bool => $open !== $index
            ));
        } else {
            $this->openSections[] = $index;
        }
        $this->step = $index;
    }

    public function commitTag(string $name): void
    {
        $chunk = trim((string) ($this->tagDraft[$name] ?? ''));
        $this->tagDraft[$name] = '';
        if ($chunk === '') {
            return;
        }

        $current = array_values(array_filter(
            is_array($this->data[$name] ?? null) ? $this->data[$name] : [],
            fn ($item): bool => is_string($item) && $item !== ''
        ));

        foreach (preg_split('/\s*,\s*/', $chunk) ?: [] as $tag) {
            $tag = trim((string) $tag);
            if ($tag === '' || in_array($tag, $current, true)) {
                continue;
            }
            $current[] = $tag;
        }

        $this->data[$name] = $current;
        $this->updated('data.'.$name);
    }

    public function removeTag(string $name, int $index): void
    {
        $current = is_array($this->data[$name] ?? null) ? $this->data[$name] : [];
        unset($current[$index]);
        $this->data[$name] = array_values($current);
    }

    public function submit(): void
    {
        $form = $this->form();

        if (! $this->isAvailable()) {
            $this->unavailabilityReason = $form->unavailabilityReason() ?? 'This form is not accepting responses.';

            return;
        }

        $this->validateForm();

        app(SubmissionManager::class)->capture($form, $this->data, request(), [
            'source' => 'public',
            'path' => $form->base_path,
        ]);

        $this->clearDraft($form);
        $this->submitted = true;
        $this->successMessage = $form->success_message ?: 'Thanks — your response has been recorded.';
        $this->redirectUrl = $form->redirect_url;
        $this->submittedAt = now()->format('M j, Y \a\t g:i A');
        $this->data = [];
    }

    public function submitAnother(): void
    {
        $form = $this->form();
        $this->submitted = false;
        $this->successMessage = null;
        $this->redirectUrl = null;
        $this->submittedAt = null;
        $this->step = 0;
        $this->openSections = [0];
        $this->tagDraft = [];
        $this->hydrateDraft($form);
        $this->editors->fill($this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function thankYouSettings(): array
    {
        $form = $this->form();
        $settings = SchemaNormalizer::thankYouSettings($form->settings ?? [], $form->toArray());
        $settings['layout'] = ThankYouLayouts::resolve((string) ($settings['layout'] ?? ThankYouLayouts::CORE_LAYOUT));

        return $settings;
    }

    /**
     * @return array{header_blocks: list<array<string, mixed>>, footer_blocks: list<array<string, mixed>>, header_placement: string, footer_placement: string}
     */
    public function pageChromeConfig(): array
    {
        return PageChrome::settings($this->form()->settings ?? []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageChromeBlocks(string $zone, string $context = 'all'): array
    {
        $config = $this->pageChromeConfig();
        $blocks = $zone === 'header' ? $config['header_blocks'] : $config['footer_blocks'];
        $placement = $zone === 'header' ? $config['header_placement'] : $config['footer_placement'];
        $partitioned = PageChrome::partitionBlocks($blocks, $zone, $placement);

        return match ($context) {
            'inside' => $partitioned['inside'],
            'outside' => $partitioned['outside'],
            default => $blocks,
        };
    }

    public function pageChromeZoneBleeds(string $zone): bool
    {
        $config = $this->pageChromeConfig();
        $placement = $zone === 'header' ? $config['header_placement'] : $config['footer_placement'];
        $inside = $this->pageChromeBlocks($zone, 'inside');

        return PageChrome::zoneBleeds($placement, $inside, $zone);
    }

    public function pageChromeBlockBleeds(array $block, string $zone): bool
    {
        $config = $this->pageChromeConfig();
        $placement = $zone === 'header' ? $config['header_placement'] : $config['footer_placement'];

        return PageChrome::blockBleeds($block, $zone, $placement);
    }

    public function render(): View
    {
        $form = $this->form();

        return view('form-builder::livewire.public-form', [
            'formModel' => $form,
            'publicUrl' => PathResolver::publicUrl($form),
        ])->layout('form-builder::layouts.public', [
            'title' => $form->name,
        ]);
    }

    protected function validateCurrentSection(): void
    {
        $this->validateForm($this->step);
    }

    protected function validateForm(?int $containerIndex = null): void
    {
        $form = $this->form();
        $this->normalizeIncoming();

        try {
            if ($containerIndex === null) {
                $this->syncEditors();
            }

            $this->validate(
                app(ValidationBuilder::class)->rules($form, $containerIndex, $this->data),
                [],
                app(ValidationBuilder::class)->attributes($form, $containerIndex)
            );
        } catch (ValidationException $e) {
            $this->revealFirstError($e);
            throw $e;
        }
    }

    protected function revealFirstError(ValidationException $e): void
    {
        $key = array_key_first($e->errors());
        if (! is_string($key) || ! str_starts_with($key, 'data.')) {
            return;
        }

        $name = substr($key, 5);
        $index = $this->containerIndexForField($name);
        if ($index !== null) {
            $this->step = $index;
            $this->ensureSectionOpen($index);
        }

        $this->dispatch('fb-focus-field', id: 'fb-'.$this->formId.'-'.$name);
    }

    protected function containerIndexForField(string $name): ?int
    {
        foreach ($this->form()->schema ?? [] as $index => $container) {
            foreach ($container['fields'] ?? [] as $field) {
                if (($field['name'] ?? null) === $name) {
                    return (int) $index;
                }
            }
        }

        return null;
    }

    protected function ensureSectionOpen(int $index): void
    {
        if (! in_array($index, $this->openSections, true)) {
            $this->openSections[] = $index;
        }
    }

    protected function syncEditors(): void
    {
        $schema = $this->getSchema('editors');
        if (! $schema || $schema->getComponents() === []) {
            return;
        }

        $state = $schema->getState();
        if (! is_array($state)) {
            return;
        }

        foreach ($state as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    protected function normalizeIncoming(): void
    {
        foreach ($this->form()->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }

            if (($field['type'] ?? '') === 'tags') {
                $raw = $this->data[$name.'_raw'] ?? $this->data[$name] ?? null;
                if (is_string($raw)) {
                    $this->data[$name] = array_values(array_filter(array_map('trim', explode(',', $raw))));
                }
            }
        }

        $this->stripHiddenFieldValues();
    }

    protected function stripHiddenFieldValues(): void
    {
        foreach ($this->form()->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name || FieldVisibility::isVisible($field, $this->data)) {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');

            if (FieldCatalog::isBoolean($type)) {
                $this->data[$name] = false;
            } elseif (FieldCatalog::storesArray($type)) {
                $this->data[$name] = [];
            } else {
                $this->data[$name] = null;
            }
        }
    }

    protected function hydrateDraft(Form $form): void
    {
        foreach ($form->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }
            if (FieldCatalog::isBoolean($field['type'] ?? 'text')) {
                $this->data[$name] = false;
            } elseif (FieldCatalog::storesArray($field['type'] ?? 'text')) {
                $this->data[$name] = [];
            } else {
                $this->data[$name] = null;
            }
        }

        if (! config('form-builder.drafts.enabled', true) || ! FeatureCatalog::proUnlocked()) {
            return;
        }

        $draft = session(config('form-builder.drafts.session_key', 'form_builder_drafts').'.'.$form->uuid);
        if (is_array($draft)) {
            foreach ($draft as $key => $value) {
                if ($value instanceof TemporaryUploadedFile) {
                    continue;
                }
                $this->data[$key] = $value;
            }
        }
    }

    protected function saveDraft(Form $form): void
    {
        if (! config('form-builder.drafts.enabled', true) || ! FeatureCatalog::proUnlocked()) {
            return;
        }

        $payload = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof TemporaryUploadedFile || (is_array($value) && ($value[0] ?? null) instanceof TemporaryUploadedFile)) {
                continue;
            }
            $payload[$key] = $value;
        }

        session([config('form-builder.drafts.session_key', 'form_builder_drafts').'.'.$form->uuid => $payload]);
    }

    protected function clearDraft(Form $form): void
    {
        session()->forget(config('form-builder.drafts.session_key', 'form_builder_drafts').'.'.$form->uuid);
    }
}
