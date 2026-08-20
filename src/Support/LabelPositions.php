<?php

namespace Spiggle\FormBuilder\Support;

class LabelPositions
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return config('form-builder.label_positions', [
            'above' => 'Above',
            'inline' => 'Inline',
            'below' => 'Below',
            'inside' => 'Inside (placeholder / floating)',
        ]);
    }
}
