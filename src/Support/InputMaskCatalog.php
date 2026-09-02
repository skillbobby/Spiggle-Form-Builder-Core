<?php

namespace Spiggle\FormBuilder\Support;

class InputMaskCatalog
{
    /**
     * @var list<string>
     */
    public const MASK_TYPES = [
        'phone',
        'date',
        'time',
        'currency',
    ];

    /**
     * @var list<string>
     */
    public const MASKABLE_FIELD_TYPES = [
        'text',
        'phone',
        'number',
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [
            '' => 'None',
            'phone' => 'Phone number',
            'date' => 'Date',
            'time' => 'Time',
            'currency' => 'Currency',
        ];

        if (FeatureCatalog::proUnlocked()) {
            return $labels;
        }

        foreach (self::MASK_TYPES as $mask) {
            if (isset($labels[$mask])) {
                $labels[$mask] = $labels[$mask].' · PRO';
            }
        }

        return $labels;
    }

    public static function isMaskableFieldType(string $type): bool
    {
        return in_array($type, self::MASKABLE_FIELD_TYPES, true);
    }

    public static function normalizeMask(mixed $mask): ?string
    {
        $mask = is_string($mask) ? trim($mask) : '';

        if ($mask === '' || ! in_array($mask, self::MASK_TYPES, true)) {
            return null;
        }

        return $mask;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function normalizeFieldMeta(array $meta, ?bool $proUnlocked = null, ?string $fieldType = null): array
    {
        $proUnlocked ??= FeatureCatalog::proUnlocked();
        $mask = self::normalizeMask($meta['input_mask'] ?? null);

        if ($fieldType !== null && in_array($fieldType, ['date', 'datetime'], true)) {
            unset($meta['input_mask']);

            return $meta;
        }

        if ($mask === null || ! $proUnlocked) {
            unset($meta['input_mask']);

            return $meta;
        }

        $meta['input_mask'] = $mask;

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public static function resolveForField(array $field): ?string
    {
        if (! FeatureCatalog::proUnlocked()) {
            return null;
        }

        $type = (string) ($field['type'] ?? 'text');

        if (! self::isMaskableFieldType($type)) {
            return null;
        }

        return self::normalizeMask($field['meta']['input_mask'] ?? null);
    }

    public static function placeholder(string $mask): string
    {
        return match ($mask) {
            'phone' => '(555) 123-4567',
            'date' => 'MM/DD/YYYY',
            'time' => 'HH:MM',
            'currency' => '$0.00',
            default => '',
        };
    }

    public static function inputMode(string $mask): string
    {
        return match ($mask) {
            'phone' => 'tel',
            'currency' => 'decimal',
            default => 'numeric',
        };
    }

    public static function inputType(string $mask, string $fieldType): string
    {
        if ($mask === 'phone') {
            return 'tel';
        }

        if (in_array($fieldType, ['email', 'url'], true)) {
            return $fieldType;
        }

        return 'text';
    }

    public static function assetUrl(): string
    {
        return PublicFormMaskAssets::url();
    }
}
