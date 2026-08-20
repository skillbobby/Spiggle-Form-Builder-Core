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
                ? RichEditor::make($name)
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

        $visibleField = data_get($field, 'meta.visible_when_field');
        $expected = data_get($field, 'meta.visible_when_value');
        if ($visibleField) {
            $component->visible(fn (callable $get) => (string) $get($stateKey.'.'.$visibleField) === (string) $expected);
        }

        $rules = $field['validation_rules'] ?? [];
        if (is_array($rules) && $rules !== [] && method_exists($component, 'rules')) {
            $component->rules($rules);
        }

        return $component;
    }

    /**
     * HTML schematic used in the builder live preview.
     *
     * @param  array<string, mixed>  $state
     */
    public function schematicHtml(array $state): string
    {
        $name = e((string) ($state['name'] ?? 'Untitled form'));
        $type = e((string) ($state['container_type'] ?? 'single'));
        $schema = is_array($state['schema'] ?? null) ? $state['schema'] : [];

        $html = '<div class="space-y-3 text-sm">';
        $html .= '<p class="font-medium">'.$name.' <span class="opacity-70">('.$type.')</span></p>';

        if ($schema === []) {
            $html .= '<p class="opacity-70">Add a section and fields to see a live layout preview.</p></div>';

            return $html;
        }

        foreach ($schema as $container) {
            $label = e((string) ($container['label'] ?? 'Section'));
            $html .= '<div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 space-y-2">';
            $html .= '<div class="font-medium">'.$label.'</div>';
            $html .= '<div style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:.5rem;">';

            foreach ($container['fields'] ?? [] as $field) {
                if (! is_array($field)) {
                    continue;
                }
                $span = max(1, min(12, (int) ($field['column_span'] ?? 12)));
                $fieldLabel = e((string) ($field['label'] ?? $field['name'] ?? 'Field'));
                $fieldType = e((string) ($field['type'] ?? 'text'));
                $req = ! empty($field['required']) ? ' *' : '';
                $html .= '<div style="grid-column:span '.$span.' / span '.$span.';" class="rounded border border-dashed border-gray-300 dark:border-gray-600 px-2 py-1">';
                $html .= '<span>'.$fieldLabel.$req.'</span> <span class="opacity-60">'.$fieldType.'</span>';
                $html .= '</div>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
    }
}
