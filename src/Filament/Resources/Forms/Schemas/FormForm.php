<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\LabelPositions;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form settings')
                    ->description('Build fields and content in the visual designer on the edit page.')
                    ->columns(['default' => 1, 'md' => 2])
                    ->columnSpanFull()
                    ->schema(self::settingsSchema()),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected static function settingsSchema(): array
    {
        return [
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
                ->helperText('Served at /'.trim((string) config('form-builder.route_prefix', 'forms'), '/').'/{path}.'),
            Select::make('container_type')
                ->label('Layout')
                ->options(ContainerTypes::labels())
                ->default('single')
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(fn (?string $state, Set $set) => ProUpsell::guardLayout($state, $set))
                ->helperText('Change layout in the visual designer header for a live preview.'),
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
                ->live()
                ->afterStateUpdated(fn (mixed $state, Set $set) => ProUpsell::guardNotifyEmails($state, $set))
                ->columnSpanFull(),
        ];
    }
}
