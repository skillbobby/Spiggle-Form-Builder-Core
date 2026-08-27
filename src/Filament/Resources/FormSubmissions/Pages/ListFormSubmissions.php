<?php

namespace Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\FormSubmissionResource;
use Spiggle\FormBuilder\Filament\Support\ProUpsell;
use Spiggle\FormBuilder\Filament\Widgets\FormStatusChart;
use Spiggle\FormBuilder\Filament\Widgets\FormSubmissionsChart;
use Spiggle\FormBuilder\Jobs\ExportSubmissionsJob;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;
use Spiggle\FormBuilder\Support\FeatureCatalog;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportAll')
                ->label('Export')
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
                ->action(function (array $data): void {
                    $format = strtolower((string) ($data['format'] ?? 'csv'));
                    if (FeatureCatalog::isProExport($format) && ! FeatureCatalog::proUnlocked()) {
                        ProUpsell::notify(strtoupper($format).' export');

                        return;
                    }

                    $result = ExportSubmissionsJob::dispatchSync($format);
                    Notification::make()
                        ->title('Export ready')
                        ->body($result['absolute'] ?? $result['path'])
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'new' => Tab::make('New')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new')),
            'reviewed' => Tab::make('Reviewed')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'reviewed')),
            'archived' => Tab::make('Archived')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        if (! FeatureCatalog::proUnlocked()) {
            return [];
        }

        return [
            FormSubmissionsChart::class,
            FormStatusChart::class,
        ];
    }
}
