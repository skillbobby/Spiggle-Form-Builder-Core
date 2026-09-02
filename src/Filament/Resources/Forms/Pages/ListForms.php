<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Services\FormDocumentService;

class ListForms extends ListRecords
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importJson')
                ->label('Import JSON')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import form from JSON')
                ->modalDescription('Upload a portable JSON export from this builder or the form-builder:export command.')
                ->schema([
                    FileUpload::make('file')
                        ->label('JSON file')
                        ->acceptedFileTypes(['application/json', 'text/json', 'text/plain'])
                        ->required()
                        ->maxSize(5120),
                ])
                ->action(function (array $data, FormDocumentService $documents): void {
                    $upload = $data['file'] ?? null;

                    if ($upload instanceof TemporaryUploadedFile) {
                        $json = $upload->get();
                    } elseif (is_string($upload) && $upload !== '') {
                        $json = Storage::disk('local')->get($upload);
                    } else {
                        throw new InvalidArgumentException('Choose a JSON file to import.');
                    }

                    if (! is_string($json) || trim($json) === '') {
                        throw new InvalidArgumentException('The uploaded file is empty.');
                    }

                    $imported = $documents->importDocuments($documents->decode($json));
                    $count = count($imported);
                    $first = $imported[0];

                    Notification::make()
                        ->title($count === 1 ? 'Form imported' : "{$count} forms imported")
                        ->body($count === 1 ? $first->name : collect($imported)->pluck('name')->implode(', '))
                        ->success()
                        ->send();

                    if ($count === 1) {
                        $this->redirect(FormResource::getUrl('edit', ['record' => $first]));

                        return;
                    }

                    $this->redirect(static::getUrl());
                }),
            CreateAction::make()
                ->modal(false)
                ->url(fn (): string => static::getResource()::getUrl('create')),
        ];
    }
}
