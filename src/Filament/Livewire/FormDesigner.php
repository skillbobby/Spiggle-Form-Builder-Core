<?php

namespace Spiggle\FormBuilder\Filament\Livewire;

use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\ContentBlockCatalog;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\FieldNameGenerator;
use Spiggle\FormBuilder\Support\InputMaskCatalog;
use Spiggle\FormBuilder\Support\LabelPositions;
use Spiggle\FormBuilder\Support\PageChrome;
use Spiggle\FormBuilder\Support\PaletteCatalog;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;
use Spiggle\FormBuilder\Support\ThankYouLayouts;
use Spiggle\FormBuilder\Support\StorageUrl;

class FormDesigner extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithFileUploads;

    #[Locked]
    public int $formId;

    public string $name = '';

    public string $containerType = 'single';

    public bool $isPublished = false;

    public bool $isActive = true;

    public ?string $activeFrom = null;

    public ?string $activeUntil = null;

    public string $successMessage = '';

    public ?string $redirectUrl = null;

    /** @var array<int, array<string, mixed>> */
    public array $schema = [];

    /** @var array<string, mixed> */
    public array $settings = [];

    public int $activeSection = 0;

    public ?string $selectedId = null;

    /** header | footer | form | thank_you */
    public ?string $selectedZone = null;

    /** form | thank_you */
    public string $panel = 'form';

    /** header | footer | form */
    public string $insertTarget = 'form';

    /** When set, new palette items are appended to this section container's children. */
    public ?string $insertTargetSectionId = null;

    public bool $inspectorOpen = false;

    protected bool $inspectorDirty = false;

    public string $paletteTab = 'fields';

    public string $paletteSearch = '';

    public string $thankYouLayout = 'card';

    /** @var array<string, mixed> */
    public array $thankYouLayoutMeta = [];

    public string $thankYouTitle = 'Thank you!';

    public string $thankYouMessage = '';

    public bool $thankYouShowFormName = true;

    public bool $thankYouShowTimestamp = true;

    /** @var array<int, array<string, mixed>> */
    public array $thankYouBlocks = [];

    /** @var array<int, array<string, mixed>> */
    public array $thankYouHeaderBlocks = [];

    /** header | body — which thank-you zone receives palette drops */
    public string $thankYouInsertTarget = 'body';

    /** @var array{content: string} */
    public array $inspectorRich = ['content' => ''];

    public ?TemporaryUploadedFile $imageUpload = null;

    /** @var list<array<string, mixed>> */
    #[Session(key: 'form-designer-history-{formId}')]
    public array $historyStack = [];

    #[Session(key: 'form-designer-history-index-{formId}')]
    public int $historyIndex = -1;

    protected bool $restoringHistory = false;

    protected const HISTORY_LIMIT = 50;

    public function mount(int $formId): void
    {
        $this->formId = $formId;
        $this->loadForm();
        $this->seedHistory();
        $this->broadcastHistoryState();
    }

    #[On('designer-save')]
    public function onDesignerSave(): void
    {
        $this->save();
    }

    #[On('designer-undo')]
    public function onDesignerUndo(): void
    {
        $this->undo();
    }

    #[On('designer-redo')]
    public function onDesignerRedo(): void
    {
        $this->redo();
    }

    public function updatedName(?string $value): void
    {
        if ($value === null) {
            return;
        }

        $this->dispatch('designer-name-updated', name: $value);
    }

    public function loadForm(): void
    {
        $form = $this->formModel();
        $this->name = (string) $form->name;
        $this->containerType = (string) $form->container_type;
        $this->isPublished = (bool) $form->is_published;
        $this->isActive = (bool) $form->is_active;
        $this->activeFrom = $form->active_from?->format('Y-m-d\TH:i');
        $this->activeUntil = $form->active_until?->format('Y-m-d\TH:i');
        $this->successMessage = (string) $form->success_message;
        $this->redirectUrl = $form->redirect_url;
        $this->schema = $form->schema ?? [];
        $this->settings = is_array($form->settings) ? $form->settings : [];

        if ($this->schema === []) {
            $this->schema = SchemaNormalizer::normalize([
                ['label' => 'Details', 'fields' => []],
            ]);
        }

        $thankYou = SchemaNormalizer::thankYouSettings($this->settings, $form->toArray());
        $this->thankYouLayout = (string) $thankYou['layout'];
        $this->thankYouLayoutMeta = is_array($thankYou['layout_meta'] ?? null) ? $thankYou['layout_meta'] : [];
        $this->thankYouLayoutMeta = ThankYouLayouts::normalizeMeta($this->thankYouLayout, $this->thankYouLayoutMeta);
        $this->thankYouTitle = (string) $thankYou['title'];
        $this->thankYouMessage = (string) ($thankYou['message'] ?? $this->successMessage);
        $this->thankYouShowFormName = (bool) ($thankYou['show_form_name'] ?? true);
        $this->thankYouShowTimestamp = (bool) ($thankYou['show_timestamp'] ?? true);
        $this->thankYouBlocks = is_array($thankYou['blocks'] ?? null) ? $thankYou['blocks'] : [];
        $this->thankYouHeaderBlocks = is_array($thankYou['header_blocks'] ?? null) ? $thankYou['header_blocks'] : [];
        $this->ensureThankYouBlocks();
        $this->ensureThankYouHeaderBlocks();
    }

    protected function ensureThankYouHeaderBlocks(): void
    {
        if ($this->thankYouHeaderBlocks !== []) {
            $this->thankYouHeaderBlocks = array_values(array_map(
                fn (array $block): array => SchemaNormalizer::normalizeContentBlock($block),
                $this->thankYouHeaderBlocks,
            ));

            return;
        }

        if (! $this->thankYouShowFormName) {
            return;
        }

        $this->thankYouHeaderBlocks = ContentBlockCatalog::defaultThankYouHeaderBlocks($this->name);
    }

    public function focusThankYouTarget(string $target): void
    {
        $this->thankYouInsertTarget = $target === 'header' ? 'header' : 'body';
    }

    protected function ensureThankYouBlocks(): void
    {
        if ($this->thankYouBlocks !== []) {
            $this->thankYouBlocks = array_values(array_map(
                fn (array $block): array => SchemaNormalizer::normalizeContentBlock($block),
                $this->thankYouBlocks,
            ));

            return;
        }

        $title = $this->thankYouTitle !== '' ? $this->thankYouTitle : 'Thank you!';
        $message = $this->thankYouMessage !== ''
            ? $this->thankYouMessage
            : ($this->successMessage !== '' ? $this->successMessage : 'Your response has been received and saved.');

        $this->thankYouBlocks = ContentBlockCatalog::defaultThankYouBlocks($title, $message);
    }

    protected function syncThankYouLegacyFields(): void
    {
        foreach ($this->thankYouBlocks as $block) {
            if (($block['type'] ?? '') !== 'heading') {
                continue;
            }

            $text = trim((string) data_get($block, 'meta.text', ''));
            if ($text !== '') {
                $this->thankYouTitle = $text;
            }

            break;
        }

        foreach ($this->thankYouBlocks as $block) {
            if (($block['type'] ?? '') !== 'paragraph') {
                continue;
            }

            $text = trim((string) data_get($block, 'meta.text', ''));
            if ($text !== '') {
                $this->thankYouMessage = $text;
            }

            break;
        }
    }

    public function save(bool $quiet = false): void
    {
        $this->flushInspectorHistory();

        $form = $this->formModel();

        $settings = $this->settings;
        $settings = SchemaNormalizer::normalizePageChrome($settings);

        $this->syncThankYouLegacyFields();

        $thankYou = SchemaNormalizer::thankYouSettings($settings, [
            'success_message' => $this->successMessage,
            'redirect_url' => $this->redirectUrl,
        ]);
        $thankYou['layout'] = FeatureCatalog::proUnlocked() ? $this->thankYouLayout : ThankYouLayouts::CORE_LAYOUT;
        $thankYou['layout_meta'] = ThankYouLayouts::normalizeMeta(
            $thankYou['layout'],
            $this->thankYouLayoutMeta,
        );
        $thankYou['title'] = $this->thankYouTitle;
        $thankYou['message'] = $this->thankYouMessage;
        $thankYou['show_form_name'] = $this->thankYouShowFormName;
        $thankYou['show_timestamp'] = $this->thankYouShowTimestamp;
        $thankYou['blocks'] = array_values(array_map(
            fn (array $block): array => SchemaNormalizer::normalizeContentBlock($block),
            $this->thankYouBlocks,
        ));
        $thankYou['header_blocks'] = array_values(array_map(
            fn (array $block): array => SchemaNormalizer::normalizeContentBlock($block),
            $this->thankYouHeaderBlocks,
        ));
        $settings['thank_you'] = $thankYou;

        $this->schema = SchemaNormalizer::normalize($this->schema);

        $form->update([
            'name' => $this->name,
            'container_type' => ContainerTypes::resolve($this->containerType),
            'is_published' => $this->isPublished,
            'is_active' => $this->isActive,
            'active_from' => filled($this->activeFrom) ? $this->activeFrom : null,
            'active_until' => filled($this->activeUntil) ? $this->activeUntil : null,
            'success_message' => $this->successMessage,
            'redirect_url' => $this->redirectUrl,
            'schema' => $this->schema,
            'settings' => $settings,
        ]);

        $this->schema = $form->fresh()->schema ?? [];
        $this->settings = $form->fresh()->settings ?? [];

        if (! $quiet) {
            Notification::make()->title('Form saved')->success()->send();
        }

        $this->dispatch('designer-saved', name: $this->name);
    }

    public function previewDraft(): void
    {
        $this->save(quiet: true);

        $this->js('window.open('.json_encode($this->previewUrl()).', "_blank", "noopener")');
    }

    public function openPublicForm(): void
    {
        $this->save(quiet: true);

        $this->js('window.open('.json_encode($this->publicUrl()).', "_blank", "noopener")');
    }

    public function togglePublished(): void
    {
        $this->isPublished = ! $this->isPublished;
        $this->saveVisibility();
    }

    public function toggleActive(): void
    {
        $this->isActive = ! $this->isActive;
        $this->saveVisibility();
    }

    public function saveVisibility(): void
    {
        $this->formModel()->update([
            'is_published' => $this->isPublished,
            'is_active' => $this->isActive,
            'active_from' => filled($this->activeFrom) ? $this->activeFrom : null,
            'active_until' => filled($this->activeUntil) ? $this->activeUntil : null,
        ]);
    }

    public function updatedContainerType(?string $value): void
    {
        if ($value === null) {
            return;
        }

        if (! FeatureCatalog::proUnlocked() && FeatureCatalog::isProLayout($value)) {
            ProUpsell::guardLayout($value, function (string $key, mixed $state): void {
                if ($key === 'container_type') {
                    $this->containerType = (string) $state;
                }
            });
        }

        if ($this->containerType === 'single' && count($this->schema) > 1) {
            return;
        }

        if ($this->containerType === 'single' && $this->schema === []) {
            $this->schema = SchemaNormalizer::normalize([
                ['label' => 'Details', 'fields' => []],
            ]);
        }
    }

    public function selectPanel(string $panel): void
    {
        $this->panel = in_array($panel, ['form', 'thank_you'], true) ? $panel : 'form';

        if ($this->panel === 'thank_you') {
            $this->paletteTab = 'content';
            $this->thankYouInsertTarget = 'body';
        } elseif ($this->insertTarget === 'form') {
            $this->paletteTab = 'fields';
        }

        $this->closeInspector();
    }

    public function selectSection(int $index): void
    {
        $this->panel = 'form';
        $this->activeSection = max(0, min($index, count($this->schema) - 1));
        $this->closeInspector();
    }

    public function focusInsertTarget(string $zone): void
    {
        if (ContentBlockCatalog::isSectionZone($zone)) {
            $this->insertTarget = 'form';
            $this->insertTargetSectionId = ContentBlockCatalog::sectionIdFromZone($zone);
            $this->paletteTab = 'fields';
            $this->panel = 'form';
            $this->closeInspector();

            return;
        }

        $this->insertTarget = in_array($zone, ['header', 'footer', 'form'], true) ? $zone : 'form';
        $this->insertTargetSectionId = null;
        $this->paletteTab = $this->insertTarget === 'form' ? $this->paletteTab : 'content';
        $this->panel = 'form';

        if (in_array($this->insertTarget, ['header', 'footer'], true)) {
            $this->flushInspectorHistory();
            $this->selectedId = null;
            $this->selectedZone = $this->insertTarget;
            $this->inspectorOpen = true;
            $this->imageUpload = null;

            return;
        }

        $this->closeInspector();
    }

    public function setPaletteTab(string $tab): void
    {
        $this->paletteTab = in_array($tab, ['fields', 'content'], true) ? $tab : 'fields';
    }

    public function selectItem(string $id, string $zone): void
    {
        $this->flushInspectorHistory();
        $this->selectedId = $id;
        $this->selectedZone = $zone;
        $this->inspectorOpen = true;
        $this->syncInspectorRichState();
    }

    public function closeInspector(): void
    {
        $this->flushInspectorHistory();
        $this->inspectorOpen = false;
        $this->selectedId = null;
        $this->selectedZone = null;
        $this->imageUpload = null;
    }

    protected function markInspectorDirty(): void
    {
        $this->inspectorDirty = true;
    }

    protected function flushInspectorHistory(): void
    {
        if (! $this->inspectorDirty) {
            return;
        }

        $this->inspectorDirty = false;
        $this->commitHistory();
    }

    public function addSection(): void
    {
        if ($this->containerType === 'single') {
            return;
        }

        $this->schema[] = [
            'label' => 'Section '.(count($this->schema) + 1),
            'fields' => [],
        ];
        $this->activeSection = count($this->schema) - 1;
        $this->panel = 'form';
        $this->commitHistory();
    }

    public function removeSection(int $index): void
    {
        if ($this->containerType === 'single' || count($this->schema) <= 1) {
            return;
        }

        $index = max(0, min($index, count($this->schema) - 1));
        unset($this->schema[$index]);
        $this->schema = array_values($this->schema);

        if ($this->activeSection >= count($this->schema)) {
            $this->activeSection = max(0, count($this->schema) - 1);
        } elseif ($this->activeSection > $index) {
            $this->activeSection--;
        }

        $this->panel = 'form';
        $this->closeInspector();
        $this->commitHistory();
    }

    public function addField(string $type, bool $openInspector = true, ?int $insertAt = null): void
    {
        $label = FieldCatalog::labels()[$type] ?? ucfirst($type);
        $name = FieldNameGenerator::unique($label, $this->allFieldNames());

        $item = [
            'kind' => 'field',
            'name' => $name,
            'type' => $type,
            'label' => $label,
            'column_span' => 12,
            'required' => false,
        ];

        $this->pushItem($item, $openInspector, $insertAt);
    }

    public function addContentBlock(string $type, bool $openInspector = true, ?int $insertAt = null): void
    {
        if (! ProUpsell::guardContentBlock($type)) {
            return;
        }

        $block = ContentBlockCatalog::defaults($type);
        $this->pushItem($block, $openInspector, $insertAt);
    }

    public function addThankYouBlock(string $type): void
    {
        if (! ProUpsell::guardContentBlock($type)) {
            return;
        }

        $this->panel = 'thank_you';
        $this->pushItem(ContentBlockCatalog::defaults($type));
    }

    public function addFromPalette(string $kind, string $type, ?string $zone = null, bool $openInspector = true, ?int $insertAt = null): void
    {
        $zone = $zone ?? $this->paletteTargetZone();
        if ($zone === 'thank_you' || $zone === 'thank_you_header') {
            if ($kind !== 'content' || ! ProUpsell::guardContentBlock($type)) {
                return;
            }

            $this->panel = 'thank_you';
            $this->thankYouInsertTarget = $zone === 'thank_you_header' ? 'header' : 'body';
            $this->pushItem(ContentBlockCatalog::defaults($type), $openInspector, $insertAt);

            return;
        }

        $this->panel = 'form';

        if (ContentBlockCatalog::isSectionZone($zone)) {
            $this->insertTarget = 'form';
            $this->insertTargetSectionId = ContentBlockCatalog::sectionIdFromZone($zone);

            if ($kind === 'field') {
                $this->addField($type, $openInspector, $insertAt);
            } elseif ($kind === 'content' && ProUpsell::guardContentBlock($type)) {
                $this->addContentBlock($type, $openInspector, $insertAt);
            }

            return;
        }

        if ($zone === 'header' || $zone === 'footer') {
            if ($kind !== 'content') {
                return;
            }

            $this->insertTarget = $zone;
            $this->addContentBlock($type, $openInspector, $insertAt);

            return;
        }

        if ($kind === 'field') {
            $this->insertTarget = 'form';
            $this->insertTargetSectionId = null;
            $this->addField($type, $openInspector, $insertAt);

            return;
        }

        $this->insertTarget = 'form';
        $this->insertTargetSectionId = null;
        $this->addContentBlock($type, $openInspector, $insertAt);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $item
     * @return array<int, array<string, mixed>>
     */
    protected function insertIntoList(array $items, array $item, ?int $insertAt): array
    {
        $items = array_values($items);

        if ($insertAt === null) {
            $items[] = $item;

            return $items;
        }

        $index = max(0, min($insertAt, count($items)));
        array_splice($items, $index, 0, [$item]);

        return $items;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function pushItem(array $item, bool $openInspector = true, ?int $insertAt = null): void
    {
        if (! isset($item['id'])) {
            $item['id'] = (string) Str::uuid();
        }

        if ($this->panel === 'thank_you') {
            if ($this->thankYouInsertTarget === 'header') {
                $this->thankYouHeaderBlocks = $this->insertIntoList($this->thankYouHeaderBlocks, $item, $insertAt);
                $this->commitHistory();
                if ($openInspector) {
                    $this->selectItem((string) $item['id'], 'thank_you_header');
                }

                return;
            }

            $this->thankYouBlocks = $this->insertIntoList($this->thankYouBlocks, $item, $insertAt);
            $this->commitHistory();
            if ($openInspector) {
                $this->selectItem((string) $item['id'], 'thank_you');
            }

            return;
        }

        $target = $this->insertTarget;

        if ($target === 'header' || $target === 'footer') {
            $key = $target === 'header' ? 'header_blocks' : 'footer_blocks';
            $this->updatePageChrome(function (array &$chrome) use ($key, $item, $insertAt): void {
                $chrome[$key] = $this->insertIntoList($chrome[$key] ?? [], $item, $insertAt);
            });
            $this->commitHistory();
            if ($openInspector) {
                $this->selectItem((string) $item['id'], $target);
            }

            return;
        }

        if ($this->insertTargetSectionId !== null && $this->insertItemToSection($this->insertTargetSectionId, $item, $insertAt)) {
            $this->panel = 'form';
            $this->commitHistory();
            if ($openInspector) {
                $this->selectItem((string) $item['id'], 'section_'.$this->insertTargetSectionId);
            }

            return;
        }

        if (! isset($this->schema[$this->activeSection])) {
            $this->schema[] = ['label' => 'Details', 'fields' => []];
            $this->activeSection = 0;
        }

        $fields = $this->schema[$this->activeSection]['fields'] ?? [];
        $this->schema[$this->activeSection]['fields'] = $this->insertIntoList($fields, $item, $insertAt);
        $this->panel = 'form';
        $this->commitHistory();
        if ($openInspector) {
            $this->selectItem((string) $item['id'], 'form');
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function insertItemToSection(string $sectionId, array $item, ?int $insertAt = null): bool
    {
        if (! isset($this->schema[$this->activeSection]['fields'])) {
            return false;
        }

        foreach ($this->schema[$this->activeSection]['fields'] as $index => $field) {
            if (! is_array($field) || ($field['id'] ?? null) !== $sectionId || ! ContentBlockCatalog::isSection($field)) {
                continue;
            }

            $children = $field['children'] ?? [];
            $this->schema[$this->activeSection]['fields'][$index]['children'] = $this->insertIntoList($children, $item, $insertAt);

            return true;
        }

        return false;
    }

    public function removeSelected(): void
    {
        if ($this->selectedId === null || $this->selectedZone === null) {
            return;
        }

        $zone = $this->selectedZone;

        if ($zone === 'thank_you_header') {
            $this->thankYouHeaderBlocks = array_values(array_filter(
                $this->thankYouHeaderBlocks,
                fn (array $item): bool => ($item['id'] ?? null) !== $this->selectedId
            ));
            $this->closeInspector();
            $this->commitHistory();

            return;
        }

        if ($zone === 'thank_you') {
            $this->thankYouBlocks = array_values(array_filter(
                $this->thankYouBlocks,
                fn (array $item): bool => ($item['id'] ?? null) !== $this->selectedId
            ));
            $this->closeInspector();
            $this->commitHistory();

            return;
        }

        if ($zone === 'header' || $zone === 'footer') {
            $key = $zone === 'header' ? 'header_blocks' : 'footer_blocks';
            $selectedId = $this->selectedId;
            $this->updatePageChrome(function (array &$chrome) use ($key, $selectedId): void {
                $chrome[$key] = array_values(array_filter(
                    $chrome[$key] ?? [],
                    fn (array $item): bool => ($item['id'] ?? null) !== $selectedId
                ));
            });
            $this->closeInspector();
            $this->commitHistory();

            return;
        }

        if (ContentBlockCatalog::isSectionZone($zone)) {
            $sectionId = ContentBlockCatalog::sectionIdFromZone($zone);
            $selectedId = $this->selectedId;

            foreach ($this->schema as $si => $section) {
                foreach ($section['fields'] ?? [] as $fi => $item) {
                    if (! is_array($item) || ($item['id'] ?? null) !== $sectionId || ! ContentBlockCatalog::isSection($item)) {
                        continue;
                    }

                    $this->schema[$si]['fields'][$fi]['children'] = array_values(array_filter(
                        $item['children'] ?? [],
                        fn (array $child): bool => ($child['id'] ?? null) !== $selectedId
                    ));
                    $this->closeInspector();
                    $this->commitHistory();

                    return;
                }
            }

            return;
        }

        foreach ($this->schema as $si => $section) {
            $fields = $section['fields'] ?? [];
            $filtered = array_values(array_filter(
                $fields,
                fn (array $item): bool => ($item['id'] ?? null) !== $this->selectedId
            ));

            if (count($filtered) !== count($fields)) {
                $this->schema[$si]['fields'] = $filtered;
                $this->closeInspector();
                $this->commitHistory();

                return;
            }

            foreach ($fields as $fi => $item) {
                if (! is_array($item) || ! ContentBlockCatalog::isSection($item)) {
                    continue;
                }

                $children = $item['children'] ?? [];
                $filteredChildren = array_values(array_filter(
                    $children,
                    fn (array $child): bool => ($child['id'] ?? null) !== $this->selectedId
                ));

                if (count($filteredChildren) !== count($children)) {
                    $this->schema[$si]['fields'][$fi]['children'] = $filteredChildren;
                    $this->closeInspector();
                    $this->commitHistory();

                    return;
                }
            }
        }
    }

    public function moveItem(string $id, int $direction): void
    {
        $zone = $this->selectedZone ?? 'form';
        $collection = $this->collectionForZone($zone);
        if ($collection === null) {
            return;
        }

        $index = null;
        foreach ($collection as $i => $item) {
            if (($item['id'] ?? null) === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return;
        }

        $target = $index + $direction;
        if ($target < 0 || $target >= count($collection)) {
            return;
        }

        $item = $collection[$index];
        $collection[$index] = $collection[$target];
        $collection[$target] = $item;

        $this->replaceCollectionForZone($zone, $collection);
        $this->commitHistory();
    }

    /**
     * @param  list<string>  $orderedIds
     */
    public function reorderZone(string $zone, array $orderedIds): void
    {
        $collection = $this->collectionForZone($zone);
        if ($collection === null || $orderedIds === []) {
            return;
        }

        $map = [];
        foreach ($collection as $item) {
            $id = (string) ($item['id'] ?? '');
            if ($id !== '') {
                $map[$id] = $item;
            }
        }

        $reordered = [];
        foreach ($orderedIds as $id) {
            if (isset($map[$id])) {
                $reordered[] = $map[$id];
                unset($map[$id]);
            }
        }

        foreach ($map as $item) {
            $reordered[] = $item;
        }

        if ($reordered === $collection) {
            return;
        }

        $this->replaceCollectionForZone($zone, $reordered);
        $this->commitHistory();
    }

    public function sortItem(string $itemId, string $toZone, int $position): void
    {
        $itemId = trim($itemId);
        $toZone = trim($toZone);

        if ($itemId === '' || $toZone === '') {
            return;
        }

        $fromZone = $this->findZoneContainingItem($itemId);
        if ($fromZone === null) {
            return;
        }

        if ($fromZone === $toZone) {
            $this->moveItemWithinZone($toZone, $itemId, $position);

            return;
        }

        $this->moveItemBetweenZones($itemId, $fromZone, $toZone, $position);
    }

    public function moveItemBetweenZones(string $itemId, string $fromZone, string $toZone, ?int $insertAt = null): void
    {
        $itemId = trim($itemId);
        $fromZone = trim($fromZone);
        $toZone = trim($toZone);

        if ($itemId === '' || $fromZone === '' || $toZone === '' || $fromZone === $toZone) {
            return;
        }

        if (ContentBlockCatalog::isSectionZone($toZone) && ContentBlockCatalog::sectionIdFromZone($toZone) === $itemId) {
            return;
        }

        $item = $this->extractItemFromZone($itemId, $fromZone);
        if ($item === null) {
            return;
        }

        if (ContentBlockCatalog::isSection($item) && ContentBlockCatalog::isSectionZone($toZone)) {
            $this->insertItemIntoZone($fromZone, $item, null);

            return;
        }

        $this->insertItemIntoZone($toZone, $item, $insertAt);
        $this->commitHistory();
    }

    protected function findZoneContainingItem(string $itemId): ?string
    {
        foreach (['thank_you_header', 'thank_you', 'header', 'footer', 'form'] as $zone) {
            foreach ($this->collectionForZone($zone) ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (($item['id'] ?? null) === $itemId) {
                    return $zone;
                }

                if ($zone !== 'form' || ! ContentBlockCatalog::isSection($item)) {
                    continue;
                }

                foreach ($item['children'] ?? [] as $child) {
                    if (is_array($child) && ($child['id'] ?? null) === $itemId) {
                        return 'section_'.(string) ($item['id'] ?? '');
                    }
                }
            }
        }

        return null;
    }

    protected function moveItemWithinZone(string $zone, string $itemId, int $position): void
    {
        $collection = $this->collectionForZone($zone);
        if ($collection === null) {
            return;
        }

        $currentIndex = null;
        $item = null;

        foreach ($collection as $index => $entry) {
            if (! is_array($entry) || ($entry['id'] ?? null) !== $itemId) {
                continue;
            }

            $currentIndex = $index;
            $item = $entry;
            break;
        }

        if ($item === null || $currentIndex === null) {
            return;
        }

        $position = max(0, min($position, count($collection) - 1));
        if ($currentIndex === $position) {
            return;
        }

        array_splice($collection, $currentIndex, 1);
        $position = max(0, min($position, count($collection)));
        array_splice($collection, $position, 0, [$item]);

        $this->replaceCollectionForZone($zone, $collection);
        $this->commitHistory();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractItemFromZone(string $itemId, string $zone): ?array
    {
        $collection = $this->collectionForZone($zone);
        if ($collection === null) {
            return null;
        }

        $item = null;
        $remaining = [];

        foreach ($collection as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            if (($entry['id'] ?? null) === $itemId) {
                $item = $entry;

                continue;
            }

            $remaining[] = $entry;
        }

        if ($item === null) {
            return null;
        }

        $this->replaceCollectionForZone($zone, $remaining);

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function insertItemIntoZone(string $zone, array $item, ?int $insertAt): void
    {
        $collection = $this->collectionForZone($zone) ?? [];
        $this->replaceCollectionForZone($zone, $this->insertIntoList($collection, $item, $insertAt));
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    protected function collectionForZone(?string $zone): ?array
    {
        if ($zone === 'thank_you' || $zone === 'thank_you_header') {
            return $zone === 'thank_you_header' ? $this->thankYouHeaderBlocks : $this->thankYouBlocks;
        }

        if ($zone === 'header') {
            return $this->pageChrome['header_blocks'];
        }

        if ($zone === 'footer') {
            return $this->pageChrome['footer_blocks'];
        }

        if (ContentBlockCatalog::isSectionZone($zone)) {
            $sectionId = ContentBlockCatalog::sectionIdFromZone($zone);

            foreach ($this->schema[$this->activeSection]['fields'] ?? [] as $item) {
                if (is_array($item) && ($item['id'] ?? null) === $sectionId && ContentBlockCatalog::isSection($item)) {
                    return array_values($item['children'] ?? []);
                }
            }

            return null;
        }

        if (! isset($this->schema[$this->activeSection])) {
            return null;
        }

        return $this->schema[$this->activeSection]['fields'] ?? [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $collection
     */
    protected function replaceCollectionForZone(string $zone, array $collection): void
    {
        if ($zone === 'thank_you_header') {
            $this->thankYouHeaderBlocks = $collection;

            return;
        }

        if ($zone === 'thank_you') {
            $this->thankYouBlocks = $collection;

            return;
        }

        if ($zone === 'header') {
            $this->updatePageChrome(function (array &$chrome) use ($collection): void {
                $chrome['header_blocks'] = $collection;
            });

            return;
        }

        if ($zone === 'footer') {
            $this->updatePageChrome(function (array &$chrome) use ($collection): void {
                $chrome['footer_blocks'] = $collection;
            });

            return;
        }

        if (ContentBlockCatalog::isSectionZone($zone)) {
            $sectionId = ContentBlockCatalog::sectionIdFromZone($zone);

            if (! isset($this->schema[$this->activeSection]['fields'])) {
                return;
            }

            foreach ($this->schema[$this->activeSection]['fields'] as $index => $item) {
                if (is_array($item) && ($item['id'] ?? null) === $sectionId && ContentBlockCatalog::isSection($item)) {
                    $this->schema[$this->activeSection]['fields'][$index]['children'] = $collection;

                    return;
                }
            }

            return;
        }

        if (isset($this->schema[$this->activeSection])) {
            $this->schema[$this->activeSection]['fields'] = $collection;
        }
    }

    /**
     * @param  callable(array<string, mixed>): void  $mutator
     */
    protected function updatePageChrome(callable $mutator): void
    {
        $settings = $this->settings;
        $stored = is_array($settings['page_chrome'] ?? null) ? $settings['page_chrome'] : [];
        $chrome = [
            'header_blocks' => array_values($stored['header_blocks'] ?? []),
            'footer_blocks' => array_values($stored['footer_blocks'] ?? []),
            'header_placement' => PageChrome::normalizeHeaderPlacement($stored['header_placement'] ?? null),
            'footer_placement' => PageChrome::normalizeFooterPlacement($stored['footer_placement'] ?? null),
        ];
        $mutator($chrome);
        $settings['page_chrome'] = $chrome;
        $this->settings = $settings;
    }

    public function updatedThankYouShowFormName(bool $value): void
    {
        if ($value && $this->thankYouHeaderBlocks === []) {
            $this->thankYouHeaderBlocks = ContentBlockCatalog::defaultThankYouHeaderBlocks($this->name);
        }
    }

    public function updatedThankYouLayout(?string $value): void
    {
        if ($value === null) {
            return;
        }

        if (! FeatureCatalog::proUnlocked() && $value !== ThankYouLayouts::CORE_LAYOUT) {
            ProUpsell::guardThankYouLayout($value, function (string $key, mixed $state): void {
                if ($key === 'layout') {
                    $this->thankYouLayout = (string) $state;
                }
            });

            return;
        }

        $this->thankYouLayoutMeta = ThankYouLayouts::normalizeMeta($value, ThankYouLayouts::defaultMeta($value));
        $this->thankYouShowFormName = $value === ThankYouLayouts::CORE_LAYOUT;
        $this->thankYouBlocks = ContentBlockCatalog::defaultThankYouBlocks(
            $this->thankYouTitle,
            $this->thankYouMessage,
            $value,
        );
        $this->thankYouHeaderBlocks = $value === ThankYouLayouts::CORE_LAYOUT
            ? ContentBlockCatalog::defaultThankYouHeaderBlocks($this->name)
            : [];
        $this->commitHistory();
    }

    public function updateThankYouLayoutMeta(string $key, mixed $value): void
    {
        data_set($this->thankYouLayoutMeta, $key, $value);
        $this->thankYouLayoutMeta = ThankYouLayouts::normalizeMeta($this->thankYouLayout, $this->thankYouLayoutMeta);
        $this->commitHistory();
    }

    public function updateThankYouSocialLink(int $index, string $field, mixed $value): void
    {
        $links = is_array($this->thankYouLayoutMeta['social_links'] ?? null)
            ? $this->thankYouLayoutMeta['social_links']
            : [];

        if (! isset($links[$index]) || ! is_array($links[$index])) {
            return;
        }

        $links[$index][$field] = $value;
        $this->thankYouLayoutMeta['social_links'] = $links;
        $this->thankYouLayoutMeta = ThankYouLayouts::normalizeMeta($this->thankYouLayout, $this->thankYouLayoutMeta);
        $this->commitHistory();
    }

    public function addThankYouSocialLink(): void
    {
        $links = is_array($this->thankYouLayoutMeta['social_links'] ?? null)
            ? $this->thankYouLayoutMeta['social_links']
            : [];
        $links[] = ['platform' => 'facebook', 'url' => ''];
        $this->thankYouLayoutMeta['social_links'] = $links;
        $this->thankYouLayoutMeta = ThankYouLayouts::normalizeMeta($this->thankYouLayout, $this->thankYouLayoutMeta);
        $this->commitHistory();
    }

    public function removeThankYouSocialLink(int $index): void
    {
        $links = is_array($this->thankYouLayoutMeta['social_links'] ?? null)
            ? $this->thankYouLayoutMeta['social_links']
            : [];

        if (! isset($links[$index])) {
            return;
        }

        unset($links[$index]);
        $this->thankYouLayoutMeta['social_links'] = array_values($links);
        $this->thankYouLayoutMeta = ThankYouLayouts::normalizeMeta($this->thankYouLayout, $this->thankYouLayoutMeta);
        $this->commitHistory();
    }

    /**
     * @return array{header_blocks: list<array<string, mixed>>, footer_blocks: list<array<string, mixed>>, header_placement: string, footer_placement: string}
     */
    #[Computed]
    public function pageChrome(): array
    {
        return PageChrome::settings($this->settings);
    }

    public function updateHeaderPlacement(string $value): void
    {
        $this->updatePageChrome(function (array &$chrome) use ($value): void {
            $chrome['header_placement'] = PageChrome::normalizeHeaderPlacement($value);
        });
        $this->commitHistory();
    }

    public function updateFooterPlacement(string $value): void
    {
        $this->updatePageChrome(function (array &$chrome) use ($value): void {
            $chrome['footer_placement'] = PageChrome::normalizeFooterPlacement($value);
        });
        $this->commitHistory();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageChromeBlocksFor(string $zone, string $context = 'all'): array
    {
        $config = $this->pageChrome;
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
        $config = $this->pageChrome;
        $placement = $zone === 'header' ? $config['header_placement'] : $config['footer_placement'];
        $inside = $this->pageChromeBlocksFor($zone, 'inside');

        return PageChrome::zoneBleeds($placement, $inside, $zone);
    }

    public function pageChromeBlockBleeds(array $block, string $zone): bool
    {
        $config = $this->pageChrome;
        $placement = $zone === 'header' ? $config['header_placement'] : $config['footer_placement'];

        return PageChrome::blockBleeds($block, $zone, $placement);
    }

    public function pageChromeBlockOutside(array $block, string $zone): bool
    {
        $config = $this->pageChrome;
        $placement = $zone === 'header' ? $config['header_placement'] : $config['footer_placement'];

        return PageChrome::isOutsidePlacement(PageChrome::resolveBlockPlacement($block, $zone, $placement));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function headerBlocks(): array
    {
        return $this->pageChrome['header_blocks'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function footerBlocks(): array
    {
        return $this->pageChrome['footer_blocks'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function activeFieldItems(): array
    {
        return array_values($this->schema[$this->activeSection]['fields'] ?? []);
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function selectedItem(): ?array
    {
        if ($this->selectedId === null || $this->selectedZone === null) {
            return null;
        }

        foreach ($this->itemsInZone($this->selectedZone) as $item) {
            if (($item['id'] ?? null) === $this->selectedId) {
                return $item;
            }
        }

        return null;
    }

    public function toggleSelectedBoolean(string $property): void
    {
        $item = $this->selectedItem;
        if ($item === null) {
            return;
        }

        $this->updateSelected($property, ! (bool) ($item[$property] ?? false));
    }

    public function toggleSelectedMetaBool(string $metaKey, bool $default = false): void
    {
        $current = (bool) $this->selectedMeta($metaKey, $default);
        $this->updateSelected('meta.'.$metaKey, ! $current);
    }

    public function updateSelectedInputMask(?string $mask): void
    {
        $mask = InputMaskCatalog::normalizeMask($mask);

        if ($mask !== null && ! ProUpsell::guardInputMask($mask)) {
            $this->updateSelected('meta.input_mask', null);

            return;
        }

        $this->updateSelected('meta.input_mask', $mask);

        if ($mask === null) {
            return;
        }

        $item = $this->selectedItem;

        if ($item === null || filled($item['placeholder'] ?? null)) {
            return;
        }

        $this->updateSelected('placeholder', InputMaskCatalog::placeholder($mask));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsInZone(string $zone): array
    {
        if ($zone === 'thank_you' || $zone === 'thank_you_header') {
            return $zone === 'thank_you_header' ? $this->thankYouHeaderBlocks : $this->thankYouBlocks;
        }

        if ($zone === 'header') {
            return $this->headerBlocks;
        }

        if ($zone === 'footer') {
            return $this->footerBlocks;
        }

        if (ContentBlockCatalog::isSectionZone($zone)) {
            $sectionId = ContentBlockCatalog::sectionIdFromZone($zone);

            foreach ($this->activeFieldItems as $item) {
                if (is_array($item) && ($item['id'] ?? null) === $sectionId && ContentBlockCatalog::isSection($item)) {
                    return array_values($item['children'] ?? []);
                }
            }

            return [];
        }

        return $this->activeFieldItems;
    }

    public function updateSelected(string $property, mixed $value): void
    {
        if ($this->selectedId === null || $this->selectedZone === null) {
            return;
        }

        $zone = $this->selectedZone;

        if ($zone === 'thank_you_header') {
            foreach ($this->thankYouHeaderBlocks as $i => $block) {
                if (($block['id'] ?? null) === $this->selectedId) {
                    $this->patchItem($this->thankYouHeaderBlocks[$i], $property, $value);
                    $this->markInspectorDirty();

                    return;
                }
            }

            return;
        }

        if ($zone === 'thank_you') {
            foreach ($this->thankYouBlocks as $i => $block) {
                if (($block['id'] ?? null) === $this->selectedId) {
                    $this->patchItem($this->thankYouBlocks[$i], $property, $value);
                    $this->markInspectorDirty();

                    return;
                }
            }

            return;
        }

        if ($zone === 'header' || $zone === 'footer') {
            $key = $zone === 'header' ? 'header_blocks' : 'footer_blocks';
            $selectedId = $this->selectedId;
            $this->updatePageChrome(function (array &$chrome) use ($key, $selectedId, $property, $value): void {
                foreach ($chrome[$key] ?? [] as $i => $block) {
                    if (($block['id'] ?? null) === $selectedId) {
                        $this->patchItem($chrome[$key][$i], $property, $value);

                        return;
                    }
                }
            });
            $this->markInspectorDirty();

            return;
        }

        if (ContentBlockCatalog::isSectionZone($zone)) {
            $sectionId = ContentBlockCatalog::sectionIdFromZone($zone);

            foreach ($this->schema as $si => $section) {
                foreach ($section['fields'] ?? [] as $fi => $item) {
                    if (! is_array($item) || ($item['id'] ?? null) !== $sectionId || ! ContentBlockCatalog::isSection($item)) {
                        continue;
                    }

                    foreach ($item['children'] ?? [] as $ci => $child) {
                        if (($child['id'] ?? null) !== $this->selectedId) {
                            continue;
                        }

                        $this->patchItem($this->schema[$si]['fields'][$fi]['children'][$ci], $property, $value);
                        $this->markInspectorDirty();

                        return;
                    }
                }
            }

            return;
        }

        foreach ($this->schema as $si => $section) {
            foreach ($section['fields'] ?? [] as $fi => $item) {
                if (($item['id'] ?? null) !== $this->selectedId) {
                    continue;
                }
                $this->patchItem($this->schema[$si]['fields'][$fi], $property, $value);
                $this->markInspectorDirty();

                return;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function patchItem(array &$item, string $property, mixed $value): void
    {
        if (str_starts_with($property, 'meta.')) {
            $metaKey = substr($property, 5);
            $meta = is_array($item['meta'] ?? null) ? $item['meta'] : [];
            $meta[$metaKey] = $this->castInspectorValue($property, $value);
            $item['meta'] = $meta;

            return;
        }

        if ($property === 'options' && is_array($value)) {
            $item['options'] = $value;

            return;
        }

        if ($property === 'validation_rules' && is_array($value)) {
            $item['validation_rules'] = array_values(array_filter($value, fn ($rule): bool => is_string($rule) && $rule !== ''));

            return;
        }

        $item[$property] = $this->castInspectorValue($property, $value);
    }

    protected function castInspectorValue(string $property, mixed $value): mixed
    {
        if ($property === 'column_span') {
            return max(1, min(12, (int) $value));
        }

        if ($property === 'meta.level') {
            return max(1, min(4, (int) $value));
        }

        return $value;
    }

    public function addSelectedOption(): void
    {
        $item = $this->selectedItem;
        if ($item === null) {
            return;
        }

        $options = is_array($item['options'] ?? null) ? $item['options'] : [];
        $options[] = [
            'label' => 'Option '.(count($options) + 1),
            'value' => 'option_'.(count($options) + 1),
            'sort_order' => count($options),
        ];
        $this->updateSelected('options', $options);
    }

    public function removeSelectedOption(int $index): void
    {
        $item = $this->selectedItem;
        if ($item === null) {
            return;
        }

        $options = is_array($item['options'] ?? null) ? $item['options'] : [];
        unset($options[$index]);
        $this->updateSelected('options', array_values($options));
    }

    public function updateSelectedOption(int $index, string $property, mixed $value): void
    {
        $item = $this->selectedItem;
        if ($item === null) {
            return;
        }

        $options = is_array($item['options'] ?? null) ? $item['options'] : [];
        if (! isset($options[$index])) {
            return;
        }

        $options[$index][$property] = $value;
        $this->updateSelected('options', $options);
    }

    public function addSelectedSocialLink(): void
    {
        $links = is_array($this->selectedMeta('links')) ? $this->selectedMeta('links') : [];
        $links[] = ['platform' => 'linkedin', 'url' => ''];
        $this->updateSelected('meta.links', $links);
    }

    public function removeSelectedSocialLink(int $index): void
    {
        $links = is_array($this->selectedMeta('links')) ? $this->selectedMeta('links') : [];
        unset($links[$index]);
        $this->updateSelected('meta.links', array_values($links));
    }

    public function updateSelectedSocialLink(int $index, string $property, mixed $value): void
    {
        $links = is_array($this->selectedMeta('links')) ? $this->selectedMeta('links') : [];
        if (! isset($links[$index])) {
            return;
        }

        $links[$index][$property] = $value;
        $this->updateSelected('meta.links', $links);
    }

    public function addSelectedButtonGroupItem(): void
    {
        $buttons = is_array($this->selectedMeta('buttons')) ? $this->selectedMeta('buttons') : [];
        $buttons[] = ['title' => 'New button', 'text' => 'Open', 'url' => ''];
        $this->updateSelected('meta.buttons', $buttons);
    }

    public function removeSelectedButtonGroupItem(int $index): void
    {
        $buttons = is_array($this->selectedMeta('buttons')) ? $this->selectedMeta('buttons') : [];
        unset($buttons[$index]);
        $this->updateSelected('meta.buttons', array_values($buttons));
    }

    public function updateSelectedButtonGroupItem(int $index, string $property, mixed $value): void
    {
        $buttons = is_array($this->selectedMeta('buttons')) ? $this->selectedMeta('buttons') : [];
        if (! isset($buttons[$index])) {
            return;
        }

        $buttons[$index][$property] = $value;
        $this->updateSelected('meta.buttons', $buttons);
    }

    public function selectedMeta(string $key, mixed $default = null): mixed
    {
        $item = $this->selectedItem;

        return $item ? data_get($item, 'meta.'.$key, $default) : $default;
    }

    public function inspectorChromeZone(): ?string
    {
        if (in_array($this->selectedZone, ['header', 'footer'], true)) {
            return $this->selectedZone;
        }

        if ($this->selectedId === null && in_array($this->insertTarget, ['header', 'footer'], true)) {
            return $this->insertTarget;
        }

        return null;
    }

    public function selectedInspectorTitle(): string
    {
        if ($this->selectedItem === null) {
            return match ($this->inspectorChromeZone()) {
                'header' => 'Form header',
                'footer' => 'Form footer',
                default => 'Inspector',
            };
        }

        $item = $this->selectedItem;

        if (($item['kind'] ?? 'field') === 'content') {
            $type = (string) ($item['type'] ?? 'content');
            $label = ContentBlockCatalog::labels()[$type] ?? ucfirst($type);

            return $label.' · '.$type;
        }

        $label = (string) ($item['label'] ?? $item['name'] ?? 'Field');
        $type = (string) ($item['type'] ?? 'text');

        return $label.' · '.$type;
    }

    /**
     * @return array<int, string>
     */
    public function allFieldNames(): array
    {
        $names = [];

        foreach ($this->schema as $section) {
            $this->collectFieldNames($section['fields'] ?? [], $names);
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  array<int, mixed>  $items
     * @param  array<int, string>  $names
     */
    protected function collectFieldNames(array $items, array &$names): void
    {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (ContentBlockCatalog::isSection($item)) {
                $this->collectFieldNames($item['children'] ?? [], $names);

                continue;
            }

            if (ContentBlockCatalog::isField($item) && filled($item['name'] ?? null)) {
                $names[] = (string) $item['name'];
            }
        }
    }

    public function updatedImageUpload(): void
    {
        if (! $this->imageUpload instanceof TemporaryUploadedFile) {
            return;
        }

        $this->validate([
            'imageUpload' => 'image|max:'.(int) config('form-builder.files.max_size_kb', 5120),
        ]);

        $disk = (string) config('form-builder.files.disk', 'public');
        $directory = 'form-builder/designer';
        $path = $this->imageUpload->store($directory, $disk);
        $url = StorageUrl::fromPublicDiskPath($path);

        $this->updateSelected('meta.image_url', $url);
        $this->imageUpload = null;
    }

    public function removeSelectedImage(): void
    {
        $this->updateSelected('meta.image_url', null);
        $this->imageUpload = null;
    }

    protected function syncInspectorRichState(): void
    {
        $item = $this->selectedItem;
        if ($item === null || ($item['kind'] ?? 'field') !== 'content') {
            $this->inspectorRich = ['content' => ''];

            return;
        }

        $type = (string) ($item['type'] ?? '');
        $key = $type === 'html' ? 'html' : 'text';
        $this->inspectorRich = ['content' => (string) data_get($item, 'meta.'.$key, '')];
        $this->getSchema('inspectorRichEditor')?->fill($this->inspectorRich);
    }

    public function inspectorRichEditor(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('content')
                    ->hiddenLabel()
                    ->live(debounce: 400)
                    ->afterStateUpdated(function (?string $state): void {
                        $item = $this->selectedItem;
                        if ($item === null || ($item['kind'] ?? 'field') !== 'content') {
                            return;
                        }

                        $type = (string) ($item['type'] ?? '');
                        $property = $type === 'html' ? 'meta.html' : 'meta.text';
                        $value = $type === 'html'
                            ? ContentBlockCatalog::sanitizeHtml((string) $state)
                            : (string) $state;
                        $this->updateSelected($property, $value);
                    }),
            ])
            ->statePath('inspectorRich');
    }

    public function inspectorRichEditorField(): ?Htmlable
    {
        $component = $this->getSchema('inspectorRichEditor')?->getComponentByStatePath(
            'inspectorRich.content',
            withHidden: true,
            withAbsoluteStatePath: true,
        );

        return $component instanceof Htmlable ? $component : null;
    }

    public function publicUrl(): string
    {
        return PathResolver::publicUrl($this->formModel());
    }

    public function previewUrl(): string
    {
        return PathResolver::previewUrl($this->formModel());
    }

    public function shareUrl(): string
    {
        return $this->isPublished && $this->formModel()->isPubliclyAvailable()
            ? $this->publicUrl()
            : $this->previewUrl();
    }

    public function shareUrlHint(): string
    {
        return $this->isPublished
            ? 'Share this link with respondents.'
            : 'This link reflects the draft form.';
    }

    public function canOpenPublic(): bool
    {
        return $this->formModel()->isPubliclyAvailable();
    }

    public function formModel(): Form
    {
        return Form::query()->findOrFail($this->formId);
    }

    public function canUndo(): bool
    {
        return $this->historyIndex > 0;
    }

    public function canRedo(): bool
    {
        return $this->historyIndex >= 0 && $this->historyIndex < count($this->historyStack) - 1;
    }

    public function undo(): void
    {
        if (! $this->canUndo()) {
            return;
        }

        $this->flushInspectorHistory();

        $this->historyIndex--;
        $this->restoreHistorySnapshot($this->historyStack[$this->historyIndex]);
        $this->broadcastHistoryState();
    }

    public function redo(): void
    {
        if (! $this->canRedo()) {
            return;
        }

        $this->flushInspectorHistory();

        $this->historyIndex++;
        $this->restoreHistorySnapshot($this->historyStack[$this->historyIndex]);
        $this->broadcastHistoryState();
    }

    protected function broadcastHistoryState(): void
    {
        $this->dispatch(
            'designer-history-state',
            canUndo: $this->canUndo(),
            canRedo: $this->canRedo(),
        );
    }

    protected function seedHistory(): void
    {
        $this->historyStack = [$this->captureHistorySnapshot()];
        $this->historyIndex = 0;
    }

    protected function commitHistory(): void
    {
        if ($this->restoringHistory) {
            return;
        }

        $snapshot = $this->captureHistorySnapshot();

        if ($this->historyIndex >= 0 && isset($this->historyStack[$this->historyIndex])) {
            if (json_encode($this->historyStack[$this->historyIndex]) === json_encode($snapshot)) {
                return;
            }
        }

        $this->historyStack = array_slice($this->historyStack, 0, $this->historyIndex + 1);
        $this->historyStack[] = $snapshot;
        $this->historyIndex = count($this->historyStack) - 1;

        if (count($this->historyStack) > self::HISTORY_LIMIT) {
            $overflow = count($this->historyStack) - self::HISTORY_LIMIT;
            $this->historyStack = array_slice($this->historyStack, $overflow);
            $this->historyIndex = count($this->historyStack) - 1;
        }

        $this->broadcastHistoryState();
    }

    /**
     * @return array<string, mixed>
     */
    protected function captureHistorySnapshot(): array
    {
        return [
            'schema' => $this->schema,
            'settings' => $this->settings,
            'thankYouBlocks' => $this->thankYouBlocks,
            'thankYouHeaderBlocks' => $this->thankYouHeaderBlocks,
            'thankYouInsertTarget' => $this->thankYouInsertTarget,
            'thankYouLayout' => $this->thankYouLayout,
            'thankYouLayoutMeta' => $this->thankYouLayoutMeta,
            'thankYouTitle' => $this->thankYouTitle,
            'thankYouMessage' => $this->thankYouMessage,
            'thankYouShowFormName' => $this->thankYouShowFormName,
            'thankYouShowTimestamp' => $this->thankYouShowTimestamp,
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    protected function restoreHistorySnapshot(array $snapshot): void
    {
        $this->restoringHistory = true;
        $this->schema = $snapshot['schema'];
        $this->settings = $snapshot['settings'];
        $this->thankYouBlocks = $snapshot['thankYouBlocks'];
        $this->thankYouHeaderBlocks = $snapshot['thankYouHeaderBlocks'] ?? [];
        $this->thankYouInsertTarget = $snapshot['thankYouInsertTarget'] ?? 'body';
        $this->thankYouLayout = $snapshot['thankYouLayout'];
        $this->thankYouLayoutMeta = is_array($snapshot['thankYouLayoutMeta'] ?? null)
            ? $snapshot['thankYouLayoutMeta']
            : ThankYouLayouts::defaultMeta((string) $snapshot['thankYouLayout']);
        $this->thankYouTitle = $snapshot['thankYouTitle'];
        $this->thankYouMessage = $snapshot['thankYouMessage'];
        $this->thankYouShowFormName = (bool) ($snapshot['thankYouShowFormName'] ?? true);
        $this->thankYouShowTimestamp = (bool) ($snapshot['thankYouShowTimestamp'] ?? true);
        $this->closeInspector();
        $this->restoringHistory = false;
    }

    public function paletteTargetZone(): string
    {
        if ($this->panel === 'thank_you') {
            return $this->thankYouInsertTarget === 'header' ? 'thank_you_header' : 'thank_you';
        }

        return $this->insertTargetSectionId !== null
            ? 'section_'.$this->insertTargetSectionId
            : (in_array($this->insertTarget, ['header', 'footer'], true)
                ? $this->insertTarget
                : 'form');
    }

    public function render()
    {
        $fieldLabels = FieldCatalog::labels();
        $contentLabels = ContentBlockCatalog::labels();
        return view('form-builder::filament.livewire.form-designer', [
            'fieldLabels' => $fieldLabels,
            'contentLabels' => $contentLabels,
            'layoutLabels' => ContainerTypes::labels(),
            'labelPositionLabels' => LabelPositions::labels(),
            'proUnlocked' => FeatureCatalog::proUnlocked(),
            'thankYouLayoutLabels' => ThankYouLayouts::labels(),
            'fieldPaletteCategories' => PaletteCatalog::filterCategories(
                PaletteCatalog::fieldCategories(),
                $fieldLabels,
                $this->paletteSearch,
            ),
            'contentPaletteCategories' => PaletteCatalog::filterCategories(
                PaletteCatalog::contentCategories(),
                $contentLabels,
                $this->paletteSearch,
            ),
        ]);
    }
}
