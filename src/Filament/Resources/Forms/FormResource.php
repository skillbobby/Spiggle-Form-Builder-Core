<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spiggle\FormBuilder\Filament\Resources\Forms\Pages\ChooseFormStart;
use Spiggle\FormBuilder\Filament\Resources\Forms\Pages\EditForm;
use Spiggle\FormBuilder\Filament\Resources\Forms\Pages\ListForms;
use Spiggle\FormBuilder\Filament\Resources\Forms\Pages\ViewForm;
use Spiggle\FormBuilder\Filament\Resources\Forms\RelationManagers\SubmissionsRelationManager;
use Spiggle\FormBuilder\Filament\Resources\Forms\Schemas\FormForm;
use Spiggle\FormBuilder\Filament\Resources\Forms\Tables\FormsTable;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;
use UnitEnum;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('form-builder.navigation.group', 'Forms');
    }

    public static function getNavigationSort(): ?int
    {
        return (int) config('form-builder.navigation.forms_sort', 40);
    }

    public static function getNavigationLabel(): string
    {
        return 'Forms';
    }

    public static function getModelLabel(): string
    {
        return 'Form';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Forms';
    }

    public static function form(Schema $schema): Schema
    {
        return FormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'create' => ChooseFormStart::route('/create'),
            'view' => ViewForm::route('/{record}'),
            'edit' => EditForm::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return static::userCanManageForms();
    }

    public static function canViewAny(): bool
    {
        return static::userCanManageForms();
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userCanManageForms();
    }

    public static function canCreate(): bool
    {
        return static::userCanManageForms();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userCanManageForms();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userCanManageForms();
    }

    protected static function userCanManageForms(): bool
    {
        return AuthorizesFormBuilder::userCanManageForms();
    }
}
