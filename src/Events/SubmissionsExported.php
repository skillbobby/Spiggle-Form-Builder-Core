<?php

namespace Spiggle\FormBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spiggle\FormBuilder\Models\Form;

class SubmissionsExported
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public ?Form $form,
        public string $format,
        public string $path,
        public array $meta = [],
    ) {}
}
