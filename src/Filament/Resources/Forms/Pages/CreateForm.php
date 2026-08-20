<?php

namespace Spiggle\FormBuilder\Filament\Resources\Forms\Pages;

use Filament\Resources\Pages\CreateRecord;
use Spiggle\FormBuilder\Filament\Resources\Forms\FormResource;

class CreateForm extends CreateRecord
{
    protected static string $resource = FormResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['schema'])) {
            $data['schema'] = [[
                'label' => 'Details',
                'fields' => [],
            ]];
        }

        return $data;
    }
}
