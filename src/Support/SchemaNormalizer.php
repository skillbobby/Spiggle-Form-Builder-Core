<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Str;

class SchemaNormalizer
{
    /**
     * Normalize a form schema array (containers + fields) for storage.
     *
     * @param  array<int, mixed>  $schema
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(array $schema): array
    {
        $containers = [];
        $usedNames = [];

        foreach (array_values($schema) as $index => $container) {
            if (! is_array($container)) {
                continue;
            }

            $fields = [];
            foreach (array_values($container['fields'] ?? []) as $fieldIndex => $field) {
                if (! is_array($field)) {
                    continue;
                }

                $name = Str::slug((string) ($field['name'] ?? $field['label'] ?? 'field_'.$fieldIndex), '_');
                if ($name === '') {
                    $name = 'field_'.$fieldIndex;
                }

                $base = $name;
                $i = 2;
                while (isset($usedNames[$name])) {
                    $name = $base.'_'.$i;
                    $i++;
                }
                $usedNames[$name] = true;

                $options = [];
                foreach (array_values($field['options'] ?? []) as $optionIndex => $option) {
                    if (! is_array($option)) {
                        continue;
                    }
                    $value = trim((string) ($option['value'] ?? ''));
                    $label = trim((string) ($option['label'] ?? $value));
                    if ($value === '' && $label === '') {
                        continue;
                    }
                    if ($value === '') {
                        $value = Str::slug($label, '_');
                    }
                    $options[] = [
                        'label' => $label !== '' ? $label : $value,
                        'value' => $value,
                        'color' => $option['color'] ?? null,
                        'sort_order' => (int) ($option['sort_order'] ?? $optionIndex),
                    ];
                }

                $columnSpan = (int) ($field['column_span'] ?? 12);
                $columnSpan = max(1, min(12, $columnSpan));

                $fields[] = [
                    'id' => self::uuid($field['id'] ?? null),
                    'name' => $name,
                    'type' => (string) ($field['type'] ?? 'text'),
                    'label' => (string) ($field['label'] ?? Str::headline($name)),
                    'label_position' => $field['label_position'] ?? null,
                    'label_override' => $field['label_override'] ?? null,
                    'required' => (bool) ($field['required'] ?? false),
                    'placeholder' => $field['placeholder'] ?? null,
                    'hint' => $field['hint'] ?? null,
                    'column_span' => $columnSpan,
                    'validation_rules' => array_values(array_filter(
                        is_array($field['validation_rules'] ?? null) ? $field['validation_rules'] : []
                    )),
                    'options' => $options,
                    'meta' => is_array($field['meta'] ?? null) ? $field['meta'] : [],
                ];
            }

            $label = trim((string) ($container['label'] ?? ''));
            if ($label === '') {
                $label = 'Section '.($index + 1);
            }

            $containers[] = [
                'id' => self::uuid($container['id'] ?? null),
                'key' => (string) ($container['key'] ?? Str::slug($label, '_')),
                'label' => $label,
                'description' => $container['description'] ?? null,
                'columns' => max(1, min(12, (int) ($container['columns'] ?? 12))),
                'fields' => $fields,
            ];
        }

        return $containers;
    }

    /**
     * Portable SRD document wrapping a form record.
     *
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public static function document(array $form): array
    {
        return [
            'schema_version' => (string) ($form['schema_version'] ?? config('form-builder.schema_version', '1.0')),
            'form_id' => (string) ($form['uuid'] ?? $form['form_id'] ?? ''),
            'name' => (string) ($form['name'] ?? ''),
            'base_path' => (string) ($form['base_path'] ?? ''),
            'container_type' => (string) ($form['container_type'] ?? 'single'),
            'schema' => self::normalize(is_array($form['schema'] ?? null) ? $form['schema'] : []),
        ];
    }

    /**
     * Flatten fields across containers.
     *
     * @param  array<int, array<string, mixed>>  $schema
     * @return array<int, array<string, mixed>>
     */
    public static function fields(array $schema): array
    {
        $fields = [];
        foreach ($schema as $container) {
            foreach ($container['fields'] ?? [] as $field) {
                if (is_array($field)) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    protected static function uuid(mixed $value): string
    {
        $value = is_string($value) ? trim($value) : '';

        if ($value !== '' && Str::isUuid($value)) {
            return $value;
        }

        return (string) Str::uuid();
    }
}
