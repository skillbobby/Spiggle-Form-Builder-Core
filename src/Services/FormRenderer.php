<?php

namespace Spiggle\FormBuilder\Services;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\ContentBlockCatalog;
use Spiggle\FormBuilder\Support\FieldVisibility;

class FormRenderer
{
    /**
     * Build Filament v5 Schema components from a form definition (admin preview / embed).
     *
     * @return array<int, mixed>
     */
    public function toFilament(Form $form, string $stateKey = 'data'): array
    {
        $containers = $form->schema ?? [];
        $type = ContainerTypes::resolve($form->container_type ?: 'single');

        // Schema keys may be UUIDs / strings from the builder — reindex before
        // using $index in arithmetic or int-typed map callbacks.
        $containers = array_values(array_filter(
            is_array($containers) ? $containers : [],
            fn ($container): bool => is_array($container)
        ));

        if ($type === 'wizard' || $type === 'pages') {
            return [
                Wizard::make(
                    collect($containers)->map(fn (array $container, int $index) => Step::make($container['label'] ?? 'Step '.($index + 1))
                        ->description($container['description'] ?? null)
                        ->schema($this->containerFields($container, $stateKey))
                    )->all()
                ),
            ];
        }

        if ($type === 'tabs') {
            return [
                Tabs::make('form-tabs')
                    ->tabs(
                        collect($containers)->map(fn (array $container, int $index) => Tab::make($container['label'] ?? 'Tab '.($index + 1))
                            ->schema($this->containerFields($container, $stateKey))
                        )->all()
                    ),
            ];
        }

        if ($type === 'accordion') {
            return collect($containers)->map(function (array $container, int $index) use ($stateKey) {
                return Section::make($container['label'] ?? 'Section '.($index + 1))
                    ->description($container['description'] ?? null)
                    ->collapsible()
                    ->collapsed($index !== 0)
                    ->schema($this->containerFields($container, $stateKey));
            })->all() ?: [Grid::make(12)->schema([])];
        }

        $sections = collect($containers)->map(function (array $container, int $index) use ($stateKey) {
            return Section::make($container['label'] ?? 'Section '.($index + 1))
                ->description($container['description'] ?? null)
                ->schema($this->containerFields($container, $stateKey));
        })->all();

        return $sections !== [] ? $sections : [Grid::make(12)->schema([])];
    }

    /**
     * @param  array<string, mixed>  $container
     * @return array<int, mixed>
     */
    public function containerFields(array $container, string $stateKey = 'data'): array
    {
        $components = [];

        foreach ($container['fields'] ?? [] as $field) {
            if (! is_array($field)) {
                continue;
            }

            if (ContentBlockCatalog::isContent($field)) {
                if (ContentBlockCatalog::isSection($field)) {
                    $components[] = $this->makeSectionContainer($field, $stateKey);
                } else {
                    $components[] = $this->makeContentPlaceholder($field);
                }

                continue;
            }

            $component = $this->makeField($field, $stateKey);
            if ($component) {
                $components[] = $component;
            }
        }

        return [Grid::make(12)->schema($components)];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public function makeField(array $field, string $stateKey = 'data'): mixed
    {
        $name = $stateKey.'.'.($field['name'] ?? 'field');
        $type = (string) ($field['type'] ?? 'text');
        $options = collect($field['options'] ?? [])->pluck('label', 'value')->all();

        $component = match ($type) {
            'textarea' => ! empty($field['meta']['use_editor'])
                ? $this->makeRichEditor($field, $name)
                : Textarea::make($name)->rows((int) data_get($field, 'meta.rows', 4)),
            'select' => Select::make($name)->options($options)->native(false),
            'multi_select' => Select::make($name)->options($options)->multiple()->native(false)->searchable(),
            'tags' => TagsInput::make($name)->suggestions(array_values($options)),
            'radio' => Radio::make($name)->options($options),
            'date' => DatePicker::make($name)->native(false),
            'datetime' => DateTimePicker::make($name)->native(false),
            'boolean', 'toggle' => Toggle::make($name),
            'number' => TextInput::make($name)->numeric(),
            'email' => TextInput::make($name)->email(),
            'phone' => TextInput::make($name)->tel(),
            'url' => TextInput::make($name)->url(),
            'file' => FileUpload::make($name)
                ->disk((string) config('form-builder.files.disk', 'public'))
                ->directory((string) config('form-builder.files.directory', 'form-submissions'))
                ->multiple((bool) data_get($field, 'meta.multiple', false)),
            default => TextInput::make($name),
        };

        $label = (string) ($field['label_override'] ?: $field['label'] ?? $field['name'] ?? 'Field');
        $component->label($label)->required((bool) ($field['required'] ?? false));

        if (! empty($field['placeholder']) && method_exists($component, 'placeholder')) {
            $component->placeholder((string) $field['placeholder']);
        }
        if (! empty($field['hint']) && method_exists($component, 'helperText')) {
            $component->helperText((string) $field['hint']);
        }

        $span = max(1, min(12, (int) ($field['column_span'] ?? 12)));
        $component->columnSpan(['default' => 12, 'md' => $span]);

        $visibleField = FieldVisibility::controllingFieldName($field);
        if ($visibleField) {
            $component->visible(fn (callable $get) => FieldVisibility::matchesCondition(
                $get($stateKey.'.'.$visibleField),
                $field
            ));
        }

        $rules = $field['validation_rules'] ?? [];
        if (is_array($rules) && $rules !== [] && method_exists($component, 'rules')) {
            $component->rules($rules);
        }

        return $component;
    }

    /**
     * Filament RichEditor used by the admin mapper and the public Livewire form.
     *
     * @param  array<string, mixed>  $field
     */
    public function makeRichEditor(array $field, string $name, bool $hideLabel = false, ?string $htmlId = null): RichEditor
    {
        $editor = RichEditor::make($name)
            ->fileAttachments(false);

        if ($hideLabel) {
            $editor->hiddenLabel();
        }

        if ($htmlId) {
            $editor->extraAttributes(['id' => $htmlId]);
        }

        if (! empty($field['placeholder'])) {
            $editor->placeholder((string) $field['placeholder']);
        }

        return $editor;
    }

    /**
     * HTML schematic used in the builder live preview.
     *
     * Inline styles only: Filament v5 does not ship Tailwind utilities, and
     * TextEntry::html() sanitizes layout tags down to plain text.
     *
     * @param  array<string, mixed>  $state
     */
    public function schematicHtml(array $state): string
    {
        $name = e((string) ($state['name'] ?? 'Untitled form'));
        $type = (string) ($state['container_type'] ?? 'single');
        $schema = is_array($state['schema'] ?? null) ? $state['schema'] : [];

        $shell = 'border:1px solid color-mix(in srgb, currentColor 18%, transparent);border-radius:12px;padding:16px;background:color-mix(in srgb, currentColor 4%, transparent);font-size:14px;line-height:1.4';

        if ($schema === []) {
            return '<div data-layout-preview="empty" style="'.$shell.';opacity:.7">Add a section and fields to see a live layout preview.</div>';
        }

        $html = '<div data-layout-preview="'.e($type).'" style="'.$shell.'">';
        $html .= '<p style="margin:0 0 14px;font-weight:600">'.$name.'</p>';
        $html .= $this->schematicChromeHtml($type, $schema);

        foreach ($schema as $index => $container) {
            if (! is_array($container)) {
                continue;
            }
            $label = e((string) ($container['label'] ?? 'Section'));
            $html .= '<div data-preview-section="'.$index.'" style="margin-top:'.($index === 0 ? '0' : '16px').'">';
            if ($type !== 'single' || count($schema) > 1) {
                $html .= '<div style="font-size:12px;opacity:.65;margin-bottom:8px">'.$label.'</div>';
            }
            $html .= $this->schematicFieldsHtml($container);
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param  array<int, mixed>  $schema
     */
    protected function schematicChromeHtml(string $type, array $schema): string
    {
        $schema = array_values(array_filter($schema, fn ($container): bool => is_array($container)));

        if ($type === 'tabs') {
            $html = '<div data-preview-tabs style="display:flex;flex-wrap:wrap;gap:0;border-bottom:1px solid color-mix(in srgb, currentColor 18%, transparent);margin-bottom:14px">';
            foreach ($schema as $index => $container) {
                $label = e((string) ($container['label'] ?? 'Tab '.($index + 1)));
                $active = $index === 0
                    ? 'font-weight:600;border-bottom:2px solid #f59e0b;opacity:1'
                    : 'opacity:.55;border-bottom:2px solid transparent';
                $html .= '<div style="padding:8px 14px;'.$active.'">'.$label.'</div>';
            }

            return $html.'</div>';
        }

        if ($type === 'wizard' || $type === 'pages') {
            $html = '<div data-preview-steps style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px">';
            foreach ($schema as $index => $container) {
                $label = e((string) ($container['label'] ?? 'Step '.($index + 1)));
                $html .= '<div style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid color-mix(in srgb, currentColor 18%, transparent);font-size:13px">';
                $html .= '<span style="width:22px;height:22px;border-radius:999px;background:#f59e0b;color:#111;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:600">'.($index + 1).'</span>';
                $html .= $label.'</div>';
            }

            return $html.'</div>';
        }

        if ($type === 'accordion') {
            $html = '<div data-preview-accordion style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px">';
            foreach ($schema as $index => $container) {
                $label = e((string) ($container['label'] ?? 'Section '.($index + 1)));
                $html .= '<div style="border:1px solid color-mix(in srgb, currentColor 18%, transparent);border-radius:8px;padding:8px 12px;font-weight:600;display:flex;justify-content:space-between;gap:8px">';
                $html .= '<span>'.$label.'</span><span style="opacity:.5">'.($index === 0 ? '▾' : '▸').'</span></div>';
            }

            return $html.'</div>';
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $container
     */
    protected function schematicFieldsHtml(array $container): string
    {
        $html = '<div style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:8px">';

        foreach ($container['fields'] ?? [] as $field) {
            if (! is_array($field)) {
                continue;
            }

            if (ContentBlockCatalog::isContent($field)) {
                $html .= $this->schematicContentHtml($field);
                continue;
            }

            $span = max(1, min(12, (int) ($field['column_span'] ?? 12)));
            $fieldLabel = e((string) ($field['label'] ?? $field['name'] ?? 'Field'));
            $fieldType = e((string) ($field['type'] ?? 'text'));
            $req = ! empty($field['required']) ? ' *' : '';
            $html .= '<div style="grid-column:span '.$span.' / span '.$span.';min-height:56px;border:1px solid color-mix(in srgb, currentColor 22%, transparent);border-radius:8px;padding:8px 10px;background:color-mix(in srgb, currentColor 3%, transparent)">';
            $html .= '<div style="font-size:12px;font-weight:600">'.$fieldLabel.$req.'</div>';
            $html .= '<div style="margin-top:8px;height:8px;border-radius:4px;background:color-mix(in srgb, currentColor 12%, transparent)"></div>';
            $html .= '<div style="margin-top:6px;font-size:11px;opacity:.55">'.$fieldType.'</div>';
            $html .= '</div>';
        }

        return $html.'</div>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function schematicContentHtml(array $block): string
    {
        if (ContentBlockCatalog::isSection($block)) {
            $span = max(1, min(12, (int) ($block['column_span'] ?? 12)));
            $title = e((string) data_get($block, 'meta.title', 'Section'));
            $html = '<div style="grid-column:span '.$span.' / span '.$span.';border:1px solid color-mix(in srgb, currentColor 22%, transparent);border-radius:12px;padding:12px;background:color-mix(in srgb, currentColor 3%, transparent)">';
            $html .= '<div style="font-size:12px;font-weight:700;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid color-mix(in srgb, currentColor 18%, transparent)">'.$title.'</div>';
            $html .= '<div style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:8px">';

            foreach ($block['children'] ?? [] as $child) {
                if (! is_array($child)) {
                    continue;
                }

                if (ContentBlockCatalog::isContent($child)) {
                    $html .= $this->schematicContentHtml($child);
                    continue;
                }

                $childSpan = max(1, min(12, (int) ($child['column_span'] ?? 12)));
                $childLabel = e((string) ($child['label'] ?? $child['name'] ?? 'Field'));
                $html .= '<div style="grid-column:span '.$childSpan.' / span '.$childSpan.';min-height:48px;border:1px solid color-mix(in srgb, currentColor 22%, transparent);border-radius:8px;padding:8px 10px">';
                $html .= '<div style="font-size:12px;font-weight:600">'.$childLabel.'</div>';
                $html .= '</div>';
            }

            return $html.'</div></div>';
        }

        $span = max(1, min(12, (int) ($block['column_span'] ?? 12)));
        $type = e((string) ($block['type'] ?? 'content'));
        $label = match ($block['type'] ?? '') {
            'heading' => e((string) data_get($block, 'meta.text', 'Heading')),
            'banner' => 'Banner',
            'footer' => 'Footer',
            default => ucfirst(str_replace('_', ' ', (string) ($block['type'] ?? 'content'))),
        };

        $html = '<div style="grid-column:span '.$span.' / span '.$span.';min-height:40px;border:1px dashed color-mix(in srgb, currentColor 22%, transparent);border-radius:8px;padding:8px 10px;background:color-mix(in srgb, currentColor 3%, transparent)">';
        $html .= '<div style="font-size:12px;font-weight:600">'.$label.'</div>';
        $html .= '<div style="margin-top:6px;font-size:11px;opacity:.55">'.$type.'</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function makeSectionContainer(array $block, string $stateKey = 'data'): mixed
    {
        $meta = is_array($block['meta'] ?? null) ? $block['meta'] : [];
        $styles = ContentBlockCatalog::sectionStyles($meta);
        $style = collect($styles)->map(fn ($value, $key) => $key.': '.$value)->implode('; ');
        $childComponents = [];

        foreach ($block['children'] ?? [] as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (ContentBlockCatalog::isContent($child)) {
                if (ContentBlockCatalog::isSection($child)) {
                    $childComponents[] = $this->makeSectionContainer($child, $stateKey);
                } else {
                    $childComponents[] = $this->makeContentPlaceholder($child);
                }

                continue;
            }

            $component = $this->makeField($child, $stateKey);
            if ($component) {
                $childComponents[] = $component;
            }
        }

        $title = (string) ($meta['title'] ?? 'Section');
        $span = max(1, min(12, (int) ($block['column_span'] ?? 12)));

        $section = Section::make($title)
            ->schema([Grid::make(12)->schema($childComponents)])
            ->extraAttributes(['style' => $style]);

        if (empty($meta['show_title'])) {
            $section->heading('');
        }

        return $section->columnSpan(['default' => 12, 'md' => $span]);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function makeContentPlaceholder(array $block): mixed
    {
        $span = max(1, min(12, (int) ($block['column_span'] ?? 12)));
        $label = ucfirst(str_replace('_', ' ', (string) ($block['type'] ?? 'content')));

        return \Filament\Schemas\Components\Html::make(new \Illuminate\Support\HtmlString(
            '<div style="padding:.5rem;border:1px dashed #d1d5db;border-radius:8px;font-size:.85rem">'.$label.'</div>'
        ))->columnSpan(['default' => 12, 'md' => $span]);
    }
}
