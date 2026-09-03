<?php

namespace Spiggle\FormBuilder\Support;

class FieldVisibility
{
    public const OPERATOR_EQUALS = 'equals';

    public const OPERATOR_NOT_EQUALS = 'not_equals';

    public const OPERATOR_EMPTY = 'empty';

    /**
     * @var list<string>
     */
    public const OPERATORS = [
        self::OPERATOR_EMPTY,
        self::OPERATOR_EQUALS,
        self::OPERATOR_NOT_EQUALS,
    ];

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

        return self::matchesCondition($data[$controlling] ?? null, $field);
    }

    /**
     * Evaluate visibility against a controlling field's current value.
     *
     * @param  array<string, mixed>  $field
     */
    public static function matchesCondition(mixed $actual, array $field): bool
    {
        return match (self::operator($field)) {
            self::OPERATOR_EMPTY => self::isEmptyValue($actual),
            self::OPERATOR_NOT_EQUALS => ! self::valuesMatch($actual, self::expectedValue($field)),
            default => self::valuesMatch($actual, self::expectedValue($field)),
        };
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
    public static function operator(array $field): string
    {
        $visibleWhen = data_get($field, 'meta.visible_when');

        if (is_array($visibleWhen)) {
            return self::normalizeOperator($visibleWhen['operator'] ?? null);
        }

        return self::normalizeOperator(data_get($field, 'meta.visible_when_operator'));
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

    /**
     * Normalize designer meta into flat visible_when_* keys (backward compatible).
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function normalizeMeta(array $meta): array
    {
        $nested = is_array($meta['visible_when'] ?? null) ? $meta['visible_when'] : null;

        $field = $meta['visible_when_field'] ?? ($nested['field'] ?? null);
        $field = is_string($field) ? trim($field) : '';

        if ($field === '') {
            unset(
                $meta['visible_when_field'],
                $meta['visible_when_operator'],
                $meta['visible_when_value'],
                $meta['visible_when'],
            );

            return $meta;
        }

        $operator = self::normalizeOperator(
            $meta['visible_when_operator'] ?? ($nested['operator'] ?? null)
        );

        $value = $meta['visible_when_value'] ?? ($nested['value'] ?? '');
        if (is_bool($value)) {
            $value = self::normalizeScalar($value);
        } else {
            $value = trim((string) $value);
        }

        $meta['visible_when_field'] = $field;
        $meta['visible_when_operator'] = $operator;

        if ($operator === self::OPERATOR_EMPTY) {
            unset($meta['visible_when_value']);
        } else {
            $meta['visible_when_value'] = $value;
        }

        unset($meta['visible_when']);

        return $meta;
    }

    public static function normalizeOperator(mixed $operator): string
    {
        $operator = is_string($operator) ? strtolower(trim($operator)) : '';

        return in_array($operator, self::OPERATORS, true)
            ? $operator
            : self::OPERATOR_EQUALS;
    }

    /**
     * Blank controlling values: null, '', empty array, and false (toggles).
     */
    public static function isEmptyValue(mixed $actual): bool
    {
        if ($actual === null || $actual === '' || $actual === false) {
            return true;
        }

        if (is_array($actual)) {
            return $actual === [];
        }

        if (is_string($actual) && trim($actual) === '') {
            return true;
        }

        return false;
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
