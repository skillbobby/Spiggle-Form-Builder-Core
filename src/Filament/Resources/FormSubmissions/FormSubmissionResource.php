<?php

namespace Spiggle\FormBuilder\Filament\Resources\FormSubmissions;

use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Pages\ViewFormSubmission;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;
use UnitEnum;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $recordTitleAttribute = 'uuid';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('form-builder.navigation.group', 'Forms');
    }

    public static function getNavigationSort(): ?int
    {
        return (int) config('form-builder.navigation.submissions_sort', 41);
    }

    public static function getNavigationLabel(): string
    {
        return 'Submissions';
    }

    public static function getModelLabel(): string
    {
        return 'Submission';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Submissions';
    }

    public static function table(Table $table): Table
    {
        return FormSubmissionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Submission')
                ->columns(2)
                ->schema([
                    TextEntry::make('form.name')->label('Form'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('uuid')->copyable(),
                ]),
            Section::make('Answers')
                ->schema([
                    TextEntry::make('answers')
                        ->hiddenLabel()
                        ->html()
                        ->state(function (FormSubmission $record): string {
                            $html = '<dl class="space-y-2">';
                            foreach ($record->displayData() as $label => $value) {
                                $display = is_array($value)
                                    ? e(json_encode($value, JSON_UNESCAPED_SLASHES))
                                    : e((string) ($value ?? '—'));
                                $html .= '<div><dt class="font-medium">'.e((string) $label).'</dt><dd>'.$display.'</dd></div>';
                            }

                            return $html.'</dl>';
                        }),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormSubmissions::route('/'),
            'view' => ViewFormSubmission::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return AuthorizesFormBuilder::check('view_submissions')
            || AuthorizesFormBuilder::check('manage_forms');
    }
}
