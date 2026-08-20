<?php

namespace Spiggle\FormBuilder\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\AnalyticsService;

class FormStatusChart extends ChartWidget
{
    protected ?string $heading = 'Status breakdown';

    public ?Model $record = null;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $form = $this->record instanceof Form ? $this->record : null;
        $breakdown = app(AnalyticsService::class)->statusBreakdown($form);
        $labels = array_keys($breakdown);
        $data = array_values($breakdown);

        return [
            'datasets' => [
                [
                    'label' => 'Submissions',
                    'data' => $data,
                    'backgroundColor' => ['#f59e0b', '#22c55e', '#9ca3af', '#ef4444'],
                ],
            ],
            'labels' => $labels !== [] ? $labels : ['none'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
