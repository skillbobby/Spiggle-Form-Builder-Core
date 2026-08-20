<?php

namespace Spiggle\FormBuilder\Support;

class Sanitizer
{
    public static function value(mixed $value, string $type, bool $allowHtml = false): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_map(fn ($item) => self::value($item, $type === 'file' ? 'file' : 'text', $allowHtml), $value);
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        $string = (string) $value;

        if ($allowHtml || ($type === 'textarea' && $allowHtml)) {
            return $string;
        }

        return strip_tags($string);
    }
}
