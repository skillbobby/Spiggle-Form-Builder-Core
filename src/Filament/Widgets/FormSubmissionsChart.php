<?php

namespace Spiggle\FormBuilder\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\AnalyticsService;

class FormSubmissionsChart extends ChartWidget
{
    protected ?string $heading = 'Submissions over time';

    protected ?string $description = 'Last 14 days';

    public ?Model $record = null;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $form = $this->record instanceof Form ? $this->record : null;
        $series = app(AnalyticsService::class)->submissionsByDay($form, 14);

        return [
            'datasets' => [
                [
                    'label' => 'Submissions',
                    'data' => $series['data'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#fde68a',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
