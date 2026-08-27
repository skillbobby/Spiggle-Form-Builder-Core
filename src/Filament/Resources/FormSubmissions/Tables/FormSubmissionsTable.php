<?php

namespace Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\FormSubmissionResource;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Jobs\ExportSubmissionsJob;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Services\AuditLogger;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;
use Spiggle\FormBuilder\Support\FeatureCatalog;

class FormSubmissionsTable
{
    public static function configure(Table $table, ?int $formId = null): Table
    {
        $statuses = config('form-builder.submissions.statuses', [
            'new' => 'New',
            'reviewed' => 'Reviewed',
            'archived' => 'Archived',
            'spam' => 'Spam',
        ]);

        return $table
            ->columns([
                TextColumn::make('form.name')
                    ->label('Form')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: (bool) $formId),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'reviewed' => 'success',
                        'archived' => 'gray',
                        'spam' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('excerpt')
                    ->label('Preview')
                    ->state(fn (FormSubmission $record): string => $record->excerpt())
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                TextColumn::make('uuid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('form_id')
                    ->label('Forms')
                    ->placeholder('All forms')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => Form::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn (): bool => $formId === null),
                SelectFilter::make('status')->options($statuses),
                Filter::make('submitted_between')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from')->native(false),
                        \Filament\Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
                Filter::make('field_contains')
                    ->label('Field contains')
                    ->schema([
                        TextInput::make('key')->label('Field name')->placeholder('email'),
                        TextInput::make('value')->label('Contains'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $key = trim((string) ($data['key'] ?? ''));
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($key === '' || $value === '') {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($key, $value): void {
                            $q->where('data->'.$key, 'like', '%'.$value.'%')
                                ->orWhere('data', 'like', '%'.$value.'%');
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modal(false)
                    ->url(fn (FormSubmission $record): string => FormSubmissionResource::getUrl('view', ['record' => $record])),
                Action::make('markReviewed')
                    ->label('Reviewed')
                    ->icon('heroicon-o-check')
                    ->visible(fn (FormSubmission $record): bool => $record->status === 'new')
                    ->action(fn (FormSubmission $record) => $record->update(['status' => 'reviewed'])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeStatus')
                        ->label('Change status')
                        ->icon('heroicon-o-arrow-path')
                        ->schema([
                            Select::make('status')->options($statuses)->required()->native(false),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (FormSubmission $record) => $record->update(['status' => $data['status']]));
                            app(AuditLogger::class)->log('bulk_status', null, [
                                'ids' => $records->pluck('id')->all(),
                                'status' => $data['status'],
                            ]);
                            Notification::make()->title('Status updated')->success()->send();
                        }),
                    BulkAction::make('archive')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (FormSubmission $record) => $record->archive());
                            app(AuditLogger::class)->log('bulk_archive', null, ['ids' => $records->pluck('id')->all()]);
                        }),
                    BulkAction::make('exportSelected')
                        ->label('Export selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->visible(fn (): bool => AuthorizesFormBuilder::userCanExportSubmissions())
                        ->schema([
                            Select::make('format')
                                ->options(FeatureCatalog::exportFormatLabels())
                                ->default('csv')
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(fn (?string $state, callable $set) => ProUpsell::guardExportFormat($state, $set)),
                        ])
                        ->action(function (Collection $records, array $data) use ($formId): void {
                            $format = strtolower((string) ($data['format'] ?? 'csv'));
                            if (FeatureCatalog::isProExport($format) && ! FeatureCatalog::proUnlocked()) {
                                ProUpsell::notify(strtoupper($format).' export');

                                return;
                            }

                            $result = ExportSubmissionsJob::dispatchSync(
                                $format,
                                $formId,
                                $records->pluck('id')->all()
                            );
                            Notification::make()
                                ->title('Export ready')
                                ->body($result['absolute'] ?? $result['path'] ?? 'Exported')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->after(function (?Collection $records): void {
                            app(AuditLogger::class)->log('bulk_delete', null, [
                                'ids' => $records?->pluck('id')->all() ?? [],
                            ]);
                        }),
                ]),
            ])
            ->emptyStateHeading('No submissions yet')
            ->emptyStateDescription('Publish the form and share its public URL to start collecting responses.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
