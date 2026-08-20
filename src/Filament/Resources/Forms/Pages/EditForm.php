<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Models\Form;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('openPublic')
                ->label('Open public form')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->getRecord()->publicUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->is_published && $this->getRecord()->is_active),
            Action::make('exportJson')
                ->label('Export JSON')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): void {
                    /** @var Form $form */
                    $form = $this->getRecord();
                    $path = 'form-builder-exports/'.$form->slug.'.json';
                    Storage::disk('local')->put($path, json_encode($form->document(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                    Notification::make()
                        ->title('Exported')
                        ->body(storage_path('app/'.$path))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
