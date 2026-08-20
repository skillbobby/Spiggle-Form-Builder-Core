<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Submissions';

    public function table(Table $table): Table
    {
        return FormSubmissionsTable::configure($table, $this->getOwnerRecord()->getKey());
    }
}
