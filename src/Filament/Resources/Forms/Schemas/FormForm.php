<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Services\FormRenderer;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\LabelPositions;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('form-builder')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema(self::settingsSchema()),
                        Tab::make('Builder')
                            ->icon('heroicon-o-squares-plus')
                            ->schema(self::builderSchema()),
                        Tab::make('Preview')
                            ->icon('heroicon-o-eye')
                            ->schema(self::previewSchema()),
                    ]),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected static function settingsSchema(): array
    {
        return [
            Section::make('Form')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                            if (blank($get('base_path'))) {
                                $set('base_path', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->alphaDash()
                        ->maxLength(120)
                        ->helperText('Internal identifier.'),
                    TextInput::make('base_path')
                        ->label('Public path')
                        ->required()
                        ->maxLength(160)
                        ->helperText('Served at /'.trim((string) config('form-builder.route_prefix', 'forms'), '/').'/{path}. Conflicts get a short hash suffix.'),
                    Select::make('container_type')
                        ->label('Layout')
                        ->options(ContainerTypes::labels())
                        ->default('single')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (?string $state, Set $set) => ProUpsell::guardLayout($state, $set))
                        ->helperText(FeatureCatalog::proUnlocked()
                            ? 'Wizard and pages validate one section at a time. Tabs and accordion include Back / Next. Accordion keeps every section heading visible.'
                            : 'Without an active Pro license, wizard/tabs/pages/accordion still save but the public form renders as a single page.'),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('settings.label_position')
                        ->label('Default label position')
                        ->options(LabelPositions::labels())
                        ->default('above')
                        ->native(false),
                    Toggle::make('is_published')
                        ->label('Published')
                        ->helperText('Unpublished forms are hidden from the public URL.')
                        ->default(false)
                        ->inline(false),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                    Textarea::make('success_message')
                        ->rows(2)
                        ->default('Thanks — your response has been recorded.')
                        ->columnSpanFull(),
                    TextInput::make('redirect_url')
                        ->label('Redirect after submit')
                        ->url()
                        ->maxLength(255),
                    TagsInput::make('notify_emails')
                        ->label(FeatureCatalog::proUnlocked() ? 'Notify emails' : 'Notify emails · PRO')
                        ->placeholder('ops@example.com')
                        ->helperText(FeatureCatalog::proUnlocked()
                            ? 'Addresses stored on the form for your FormSubmitted listeners.'
                            : 'Email notify hooks require Form Builder Pro.')
                        ->live()
                        ->afterStateUpdated(fn (mixed $state, Set $set) => ProUpsell::guardNotifyEmails($state, $set))
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected static function builderSchema(): array
    {
        return [
            Section::make('Sections & fields')
                ->description('Drag to reorder. Collapsed rows show the section or field label. On mobile, items stack full width.')
                ->schema([
                    Repeater::make('schema')
                        ->label('Sections')
                        ->addActionLabel('Add section')
                        ->minItems(1)
                        ->defaultItems(1)
                        ->collapsed()
                        ->collapsible()
                        ->cloneable()
                        ->reorderable()
                        ->reorderableWithDragAndDrop()
                        ->itemLabel(function (array $state): ?string {
                            $label = trim((string) ($state['label'] ?? ''));

                            return $label !== '' ? $label : 'Untitled section';
                        })
                        ->addable(fn (Get $get): bool => $get('container_type') !== 'single')
                        ->deletable(fn (Get $get): bool => $get('container_type') !== 'single')
                        ->maxItems(fn (Get $get): int => $get('container_type') === 'single' ? 1 : 50)
                        ->schema([
                            TextInput::make('label')
                                ->label('Section title')
                                ->required()
                                ->live(onBlur: true)
                                ->maxLength(255)
                                ->placeholder('Your details'),
                            Textarea::make('description')
                                ->rows(2)
                                ->maxLength(500),
                            Repeater::make('fields')
                                ->label('Fields')
                                ->addActionLabel('Add field')
                                ->collapsed()
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(1)
                                ->itemLabel(function (array $state): ?string {
                                    $label = trim((string) ($state['label'] ?? ''));
                                    $type = trim((string) ($state['type'] ?? ''));
                                    if ($label !== '') {
                                        return $type !== '' ? $label.' · '.$type : $label;
                                    }
                                    $name = trim((string) ($state['name'] ?? ''));

                                    return $name !== '' ? $name : 'Untitled field';
                                })
                                ->schema(self::fieldSchema())
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected static function fieldSchema(): array
    {
        return [
            TextInput::make('label')
                ->required()
                ->live(onBlur: true)
                ->maxLength(255)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                    if (blank($get('name'))) {
                        $set('name', Str::slug((string) $state, '_'));
                    }
                }),
            TextInput::make('name')
                ->label('Internal name')
                ->required()
                ->alphaDash()
                ->maxLength(100)
                ->helperText('Used as the submission key.'),
            Select::make('type')
                ->options(FieldCatalog::labels())
                ->required()
                ->native(false)
                ->live()
                ->default('text'),
            Select::make('column_span')
                ->label('Width (12-col grid)')
                ->options(self::spanOptions())
                ->default(12)
                ->native(false)
                ->helperText('Stacks to full width on mobile.'),
            Toggle::make('required')
                ->inline(false)
                ->default(false),
            Select::make('label_position')
                ->options(['' => 'Use form default'] + LabelPositions::labels())
                ->native(false),
            TextInput::make('label_override')
                ->maxLength(255)
                ->helperText('Runtime label without changing the stored schema label.'),
            TextInput::make('placeholder')->maxLength(255),
            TextInput::make('hint')->maxLength(255),
            TagsInput::make('validation_rules')
                ->placeholder('max:255')
                ->helperText('Extra Laravel rules (e.g. max:255, regex:/^[A-Z]/).')
                ->columnSpanFull(),
            TextInput::make('meta.visible_when_field')
                ->label('Visible when field')
                ->helperText('Internal name of another field on this form.'),
            TextInput::make('meta.visible_when_value')
                ->label('Visible when value'),
            Toggle::make('meta.use_editor')
                ->label('Use rich text editor')
                ->visible(fn (Get $get): bool => $get('type') === 'textarea')
                ->inline(false),
            TextInput::make('meta.rows')
                ->numeric()
                ->minValue(2)
                ->default(4)
                ->visible(fn (Get $get): bool => $get('type') === 'textarea' && ! $get('meta.use_editor')),
            Toggle::make('meta.multiple')
                ->label('Allow multiple files')
                ->visible(fn (Get $get): bool => $get('type') === 'file')
                ->inline(false),
            Repeater::make('options')
                ->visible(fn (Get $get): bool => FieldCatalog::requiresOptions((string) $get('type')))
                ->addActionLabel('Add option')
                ->collapsed()
                ->collapsible()
                ->reorderable()
                ->defaultItems(0)
                ->itemLabel(function (array $state): ?string {
                    $label = trim((string) ($state['label'] ?? ''));
                    if ($label !== '') {
                        return $label;
                    }
                    $value = trim((string) ($state['value'] ?? ''));

                    return $value !== '' ? $value : null;
                })
                ->schema([
                    TextInput::make('label')->required()->live(onBlur: true)->maxLength(255),
                    TextInput::make('value')->required()->live(onBlur: true)->maxLength(255),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected static function previewSchema(): array
    {
        return [
            Section::make('Live layout preview')
                ->description('Updates as you edit Settings and Builder. Open the public URL after publishing for the full interactive form.')
                ->schema([
                    Html::make(function (Get $get): HtmlString {
                        return new HtmlString(app(FormRenderer::class)->schematicHtml([
                            'name' => $get('name'),
                            'container_type' => $get('container_type'),
                            'schema' => $get('schema') ?? [],
                        ]));
                    })->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int|string, string>
     */
    protected static function spanOptions(): array
    {
        $options = [];
        foreach (range(1, 12) as $span) {
            $options[$span] = $span.' / 12'.($span === 12 ? ' (full)' : ($span === 6 ? ' (half)' : ''));
        }

        return $options;
    }
}
