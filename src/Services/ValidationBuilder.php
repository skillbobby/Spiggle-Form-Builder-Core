<?php

namespace Spiggle\FormBuilder\Services;

use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\ContentBlockCatalog;
use Spiggle\FormBuilder\Support\FieldCatalog;

class ValidationBuilder
{
    /**
     * Laravel rules for the entire form or a single container index.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(Form $form, ?int $containerIndex = null): array
    {
        $containers = $form->schema ?? [];
        $rules = [];

        foreach ($containers as $index => $container) {
            if ($containerIndex !== null && $index !== $containerIndex) {
                continue;
            }

            foreach ($container['fields'] ?? [] as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $this->appendFieldRules($field, $rules);
            }
        }

        return $this->applyHooks($form, $rules);
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, array<int, mixed>>  $rules
     */
    protected function appendFieldRules(array $field, array &$rules): void
    {
        if (ContentBlockCatalog::isSection($field)) {
            foreach ($field['children'] ?? [] as $child) {
                if (is_array($child)) {
                    $this->appendFieldRules($child, $rules);
                }
            }

            return;
        }

        if (ContentBlockCatalog::isContent($field) || blank($field['name'] ?? null)) {
            return;
        }

        $name = (string) $field['name'];
        $type = (string) ($field['type'] ?? 'text');
        $fieldRules = [];

        if (! empty($field['required'])) {
            $fieldRules[] = 'required';
        } else {
            $fieldRules[] = 'nullable';
        }

        $fieldRules = array_merge($fieldRules, FieldCatalog::defaultRules($type));

        $extra = $field['validation_rules'] ?? [];
        if (is_array($extra)) {
            foreach ($extra as $rule) {
                if (is_string($rule) && $rule !== '' && ! in_array($rule, $fieldRules, true)) {
                    $fieldRules[] = $rule;
                }
            }
        }

        $rules['data.'.$name] = array_values(array_unique($fieldRules));
    }

    /**
     * @param  array<string, array<int, mixed>>  $rules
     * @return array<string, array<int, mixed>>
     */
    protected function applyHooks(Form $form, array $rules): array
    {
        $hooks = config('form-builder.validation_hooks', []);

        if (is_callable($hooks)) {
            $result = $hooks($form, $rules);

            return is_array($result) ? $result : $rules;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(Form $form, ?int $containerIndex = null): array
    {
        $attributes = [];

        foreach ($form->schema ?? [] as $index => $container) {
            if ($containerIndex !== null && $index !== $containerIndex) {
                continue;
            }

            foreach ($container['fields'] ?? [] as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $this->appendFieldAttributes($field, $attributes);
            }
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, string>  $attributes
     */
    protected function appendFieldAttributes(array $field, array &$attributes): void
    {
        if (ContentBlockCatalog::isSection($field)) {
            foreach ($field['children'] ?? [] as $child) {
                if (is_array($child)) {
                    $this->appendFieldAttributes($child, $attributes);
                }
            }

            return;
        }

        if (ContentBlockCatalog::isContent($field)) {
            return;
        }

        $name = $field['name'] ?? null;
        if (! $name) {
            return;
        }

        $attributes['data.'.$name] = (string) ($field['label_override'] ?: $field['label'] ?? $name);
    }
}
