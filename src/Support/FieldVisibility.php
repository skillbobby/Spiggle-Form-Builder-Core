<?php

namespace Spiggle\FormBuilder\Support;

class FieldVisibility
{
    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $data
     */
    public static function isVisible(array $field, array $data): bool
    {
        $controlling = self::controllingFieldName($field);

        if ($controlling === null) {
            return true;
        }

        return self::valuesMatch($data[$controlling] ?? null, self::expectedValue($field));
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public static function controllingFieldName(array $field): ?string
    {
        $visibleWhen = data_get($field, 'meta.visible_when');

        if (is_array($visibleWhen)) {
            $name = $visibleWhen['field'] ?? null;

            return is_string($name) && $name !== '' ? $name : null;
        }

        $name = data_get($field, 'meta.visible_when_field');

        return is_string($name) && $name !== '' ? $name : null;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public static function expectedValue(array $field): string
    {
        $visibleWhen = data_get($field, 'meta.visible_when');

        if (is_array($visibleWhen)) {
            return self::normalizeExpected($visibleWhen['value'] ?? true);
        }

        return self::normalizeExpected(data_get($field, 'meta.visible_when_value', ''));
    }

    public static function valuesMatch(mixed $actual, string $expected): bool
    {
        $expected = self::normalizeExpected($expected);

        if (is_array($actual)) {
            if ($actual === []) {
                return $expected === '';
            }

            foreach ($actual as $item) {
                if (self::normalizeScalar($item) === $expected) {
                    return true;
                }
            }

            return false;
        }

        if (is_bool($actual)) {
            return self::normalizeScalar($actual) === self::normalizeBooleanExpected($expected);
        }

        return self::normalizeScalar($actual) === $expected;
    }

    protected static function normalizeExpected(mixed $value): string
    {
        if (is_bool($value)) {
            return self::normalizeScalar($value);
        }

        return trim((string) $value);
    }

    protected static function normalizeScalar(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (is_array($value)) {
            return $value === [] ? '' : implode(',', array_map(
                fn ($item) => self::normalizeScalar($item),
                array_values($value)
            ));
        }

        return trim((string) $value);
    }

    protected static function normalizeBooleanExpected(string $expected): string
    {
        return match (strtolower(trim($expected))) {
            '1', 'true', 'yes', 'on' => '1',
            '0', 'false', 'no', 'off', '' => '',
            default => $expected,
        };
    }
}
