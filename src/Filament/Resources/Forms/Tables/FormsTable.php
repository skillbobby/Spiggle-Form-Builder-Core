<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\FeatureCatalog;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Form $record): string => '/'.trim((string) config('form-builder.route_prefix', 'forms'), '/').'/'.$record->base_path),
                TextColumn::make('container_type')
                    ->label('Layout')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ContainerTypes::labels()[$state] ?? $state),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Entries')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('container_type')
                    ->options(ContainerTypes::labels()),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modal(false)
                    ->url(fn (Form $record): string => FormResource::getUrl('edit', ['record' => $record])),
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Form $record): string => $record->publicUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Form $record): bool => $record->is_published && $record->is_active),
                Action::make('clone')
                    ->label(FeatureCatalog::proUnlocked() ? 'Clone' : 'Clone · PRO')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation(fn (): bool => FeatureCatalog::proUnlocked())
                    ->action(function (Form $record): void {
                        if (! FeatureCatalog::proUnlocked()) {
                            ProUpsell::notify('Form clone');

                            return;
                        }

                        $clone = $record->cloneForm();
                        Notification::make()
                            ->title('Form cloned')
                            ->body($clone->name)
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No forms yet')
            ->emptyStateDescription('Create a form, then share its public URL. Drag-and-drop fields in the builder.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
