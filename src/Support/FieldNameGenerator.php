<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Str;

class FieldNameGenerator
{
    /**
     * Generate a unique internal field name from a label or base slug.
     *
     * @param  list<string>  $existingNames
     */
    public static function unique(string $base, array $existingNames): string
    {
        $slug = Str::slug($base, '_');
        if ($slug === '') {
            $slug = 'field';
        }

        $existing = array_fill_keys(array_map('strval', $existingNames), true);

        if (! isset($existing[$slug])) {
            return $slug;
        }

        for ($i = 2; $i <= 99; $i++) {
            $candidate = $slug.'_'.$i;
            if (! isset($existing[$candidate])) {
                return $candidate;
            }
        }

        do {
            $candidate = $slug.'_'.Str::lower(Str::random(4));
        } while (isset($existing[$candidate]));

        return $candidate;
    }
}
