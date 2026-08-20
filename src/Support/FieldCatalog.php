<?php

namespace Spiggle\FormBuilder\Support;

class FieldCatalog
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        if (class_exists(\Spiggle\DynamicFields\Support\FieldTypes::class)) {
            /** @var array<string, string> $labels */
            $labels = \Spiggle\DynamicFields\Support\FieldTypes::labels();

            if ($labels !== []) {
                return $labels;
            }
        }

        return config('form-builder.field_types', []);
    }

    public static function requiresOptions(string $type): bool
    {
        if (class_exists(\Spiggle\DynamicFields\Support\FieldTypes::class)) {
            return \Spiggle\DynamicFields\Support\FieldTypes::requiresOptions($type);
        }

        return in_array($type, ['select', 'radio', 'multi_select', 'tags'], true);
    }

    public static function storesArray(string $type): bool
    {
        if (class_exists(\Spiggle\DynamicFields\Support\FieldTypes::class)
            && method_exists(\Spiggle\DynamicFields\Support\FieldTypes::class, 'storesArray')) {
            return \Spiggle\DynamicFields\Support\FieldTypes::storesArray($type);
        }

        return in_array($type, ['multi_select', 'tags', 'file'], true);
    }

    public static function isBoolean(string $type): bool
    {
        if (class_exists(\Spiggle\DynamicFields\Support\FieldTypes::class)
            && method_exists(\Spiggle\DynamicFields\Support\FieldTypes::class, 'isBoolean')) {
            return \Spiggle\DynamicFields\Support\FieldTypes::isBoolean($type);
        }

        return in_array($type, ['boolean', 'toggle'], true);
    }

    public static function isFile(string $type): bool
    {
        if (class_exists(\Spiggle\DynamicFields\Support\FieldTypes::class)
            && method_exists(\Spiggle\DynamicFields\Support\FieldTypes::class, 'isFile')) {
            return \Spiggle\DynamicFields\Support\FieldTypes::isFile($type);
        }

        return $type === 'file';
    }

    public static function defaultRules(string $type): array
    {
        return match ($type) {
            'email' => ['email'],
            'url' => ['url'],
            'number' => ['numeric'],
            'date' => ['date'],
            'datetime' => ['date'],
            'boolean', 'toggle' => ['boolean'],
            'multi_select', 'tags', 'file' => ['array'],
            default => ['string'],
        };
    }
}
