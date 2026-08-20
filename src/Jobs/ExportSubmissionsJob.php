<?php

namespace Spiggle\FormBuilder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\ExportService;

class ExportSubmissionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $format,
        public ?int $formId = null,
        public array $submissionIds = [],
    ) {
        $this->onQueue((string) config('form-builder.exports.queue', 'default'));
    }

    /**
     * @return array{path: string, filename: string, disk: string}
     */
    public function handle(ExportService $exports): array
    {
        $form = $this->formId ? Form::query()->find($this->formId) : null;
        $query = \Spiggle\FormBuilder\Models\FormSubmission::query()->with('form');

        if ($this->submissionIds !== []) {
            $query->whereIn('id', $this->submissionIds);
        }

        return $exports->export($this->format, $form, $query);
    }
}
