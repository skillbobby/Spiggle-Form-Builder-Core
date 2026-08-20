<?php

namespace Spiggle\FormBuilder\Filament\Resources\FormSubmissions\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Spiggle\FormBuilder\Filament\Resources\FormSubmissions\FormSubmissionResource;
use Spiggle\FormBuilder\Models\FormSubmission;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markReviewed')
                ->label('Mark reviewed')
                ->visible(fn (): bool => $this->getRecord()->status === 'new')
                ->action(fn () => $this->getRecord()->update(['status' => 'reviewed'])),
            Action::make('archive')
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->status !== 'archived')
                ->action(fn () => $this->getRecord()->archive()),
        ];
    }
}
