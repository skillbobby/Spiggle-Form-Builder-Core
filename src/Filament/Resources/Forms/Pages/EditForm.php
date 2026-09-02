<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\FormDocumentService;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected string $view = 'form-builder::filament.resources.forms.pages.edit-form';

    public bool $designerCanUndo = false;

    public bool $designerCanRedo = false;

    public ?string $designerName = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->designerName = (string) $this->getRecord()->name;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function getTitle(): string | Htmlable
    {
        return $this->designerName ?: (string) $this->getRecord()->name;
    }

    #[On('designer-history-state')]
    public function onDesignerHistoryState(bool $canUndo, bool $canRedo): void
    {
        $this->designerCanUndo = $canUndo;
        $this->designerCanRedo = $canRedo;
    }

    #[On('designer-name-updated')]
    public function onDesignerNameUpdated(string $name): void
    {
        $this->designerName = $name;
    }

    #[On('designer-saved')]
    public function onDesignerSaved(string $name): void
    {
        $this->designerName = $name;
        $this->getRecord()->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('undo')
                ->label('Undo')
                ->icon('heroicon-o-arrow-uturn-left')
                ->action(fn (): mixed => $this->dispatch('designer-undo'))
                ->disabled(fn (): bool => ! $this->designerCanUndo)
                ->extraAttributes(['title' => 'Undo (Ctrl+Z)']),
            Action::make('redo')
                ->label('Redo')
                ->icon('heroicon-o-arrow-uturn-right')
                ->action(fn (): mixed => $this->dispatch('designer-redo'))
                ->disabled(fn (): bool => ! $this->designerCanRedo)
                ->extraAttributes(['title' => 'Redo (Ctrl+Shift+Z)']),
            ViewAction::make()
                ->modal(false)
                ->url(fn (): string => static::getResource()::getUrl('view', ['record' => $this->getRecord()])),
            Action::make('exportJson')
                ->label('Export JSON')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): mixed {
                    /** @var Form $form */
                    $form = $this->getRecord();

                    return response()->streamDownload(
                        function () use ($form): void {
                            echo app(FormDocumentService::class)->encode($form);
                        },
                        Str::slug($form->slug ?: $form->name).'.json',
                        ['Content-Type' => 'application/json'],
                    );
                }),
            DeleteAction::make(),
        ];
    }
}
