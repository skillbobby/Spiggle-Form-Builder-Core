<?php

namespace Spiggle\FormBuilder\Support;

class OptionColor
{
    /**
     * Filament / Dynamic Fields named colors mapped to hex for the public form.
     *
     * @var array<string, string>
     */
    public const NAMED = [
        'primary' => '#f59e0b',
        'success' => '#10b981',
        'warning' => '#f59e0b',
        'danger' => '#dc2626',
        'info' => '#3b82f6',
        'gray' => '#6b7280',
        'grey' => '#6b7280',
        'blue' => '#3b82f6',
        'indigo' => '#6366f1',
        'purple' => '#a855f7',
        'pink' => '#ec4899',
        'rose' => '#f43f5e',
        'red' => '#ef4444',
        'orange' => '#f97316',
        'amber' => '#f59e0b',
        'yellow' => '#eab308',
        'lime' => '#84cc16',
        'green' => '#22c55e',
        'emerald' => '#10b981',
        'teal' => '#14b8a6',
        'cyan' => '#06b6d4',
        'sky' => '#0ea5e9',
        'violet' => '#8b5cf6',
        'fuchsia' => '#d946ef',
        'slate' => '#64748b',
    ];

    /**
     * Safe CSS color (hex) or null if the stored value cannot be painted.
     */
    public static function css(mixed $color): ?string
    {
        if (! is_string($color)) {
            return null;
        }

        $color = trim($color);
        if ($color === '') {
            return null;
        }

        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color) === 1) {
            return strtolower($color);
        }

        return self::NAMED[strtolower($color)] ?? null;
    }

    /**
     * @param  list<array<string, mixed>>  $options
     */
    public static function forValue(array $options, string $value): ?string
    {
        foreach ($options as $option) {
            if (! is_array($option)) {
                continue;
            }

            $optionValue = (string) ($option['value'] ?? '');
            $optionLabel = (string) ($option['label'] ?? '');
            if ($optionValue !== $value && $optionLabel !== $value) {
                continue;
            }

            return self::css($option['color'] ?? null);
        }

        return null;
    }
}
