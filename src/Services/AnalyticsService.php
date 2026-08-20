<?php

namespace Spiggle\FormBuilder\Services;

use Illuminate\Support\Carbon;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;

class AnalyticsService
{
    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function submissionsByDay(?Form $form = null, int $days = 14): array
    {
        $from = Carbon::now()->subDays($days - 1)->startOfDay();
        $query = FormSubmission::query()->where('created_at', '>=', $from);
        if ($form) {
            $query->where('form_id', $form->id);
        }

        $counts = $query
            ->selectRaw('date(created_at) as day, count(*) as aggregate')
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $labels = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $labels[] = $day;
            $data[] = (int) ($counts[$day] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array<string, int>
     */
    public function statusBreakdown(?Form $form = null): array
    {
        $query = FormSubmission::query();
        if ($form) {
            $query->where('form_id', $form->id);
        }

        return $query
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($v) => (int) $v)
            ->all();
    }
}
