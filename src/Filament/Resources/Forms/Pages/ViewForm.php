<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Filament\Widgets\FormStatusChart;
use Spiggle\FormBuilder\Filament\Widgets\FormSubmissionsChart;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\FormRenderer;
use Spiggle\FormBuilder\Support\FeatureCatalog;

class ViewForm extends ViewRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modal(false)
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $this->getRecord()])),
            Action::make('openPublic')
                ->label('Open public form')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->getRecord()->publicUrl())
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        /** @var Form $record */
        $record = $this->getRecord();

        return $schema->components([
            Section::make($record->name)
                ->description($record->description)
                ->columns(2)
                ->schema([
                    TextEntry::make('container_type')->badge(),
                    TextEntry::make('base_path')
                        ->label('Public URL')
                        ->url($record->publicUrl())
                        ->state($record->publicUrl()),
                    TextEntry::make('is_published')->badge(),
                    TextEntry::make('submissions_count')
                        ->state(fn (Form $record): int => $record->submissions()->count()),
                ]),
            Section::make('Layout preview')
                ->schema([
                    TextEntry::make('preview')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Form $record): string => app(FormRenderer::class)->schematicHtml($record->toArray())),
                ]),
        ]);
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
