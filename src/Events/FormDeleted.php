<?php

namespace Spiggle\FormBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spiggle\FormBuilder\Models\Form;

class FormDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Form $form) {}
}
