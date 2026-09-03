<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Str;

class SchemaNormalizer
{
    /**
     * Normalize a form schema array (containers + fields/content blocks) for storage.
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

            $items = [];
            foreach (array_values($container['fields'] ?? []) as $fieldIndex => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $kind = (string) ($item['kind'] ?? 'field');

                if ($kind === 'content') {
                    $items[] = self::normalizeContentBlock($item);
                    continue;
                }

                $items[] = self::normalizeFieldItem($item, $fieldIndex, $usedNames);
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
                'fields' => $items,
            ];
        }

        return $containers;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function normalizeContentBlock(array $item): array
    {
        $type = (string) ($item['type'] ?? 'paragraph');
        $defaults = ContentBlockCatalog::defaults($type);
        $meta = is_array($item['meta'] ?? null) ? $item['meta'] : [];
        $mergedMeta = array_merge($defaults['meta'] ?? [], $meta);

        if ($type === 'html' && isset($mergedMeta['html'])) {
            $mergedMeta['html'] = ContentBlockCatalog::sanitizeHtml((string) $mergedMeta['html']);
        }

        if (in_array($type, ['banner', 'image'], true) && array_key_exists('placement', $mergedMeta)) {
            $mergedMeta['placement'] = PageChrome::normalizeBlockPlacement(
                is_string($mergedMeta['placement']) ? $mergedMeta['placement'] : null,
            );
        }

        $columnSpan = max(1, min(12, (int) ($item['column_span'] ?? $defaults['column_span'] ?? 12)));

        $normalized = [
            'id' => self::uuid($item['id'] ?? null),
            'kind' => 'content',
            'type' => $type,
            'column_span' => $columnSpan,
            'meta' => $mergedMeta,
        ];

        if ($type === 'section') {
            $children = [];
            $childNames = [];
            foreach (array_values($item['children'] ?? []) as $childIndex => $child) {
                if (! is_array($child)) {
                    continue;
                }

                if (ContentBlockCatalog::isContent($child)) {
                    $children[] = self::normalizeContentBlock($child);
                    continue;
                }

                $children[] = self::normalizeFieldItem($child, $childIndex, $childNames);
            }
            $normalized['children'] = $children;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, bool>  $usedNames
     * @return array<string, mixed>
     */
    public static function normalizeFieldItem(array $item, int $fieldIndex, array &$usedNames): array
    {
        $name = Str::slug((string) ($item['name'] ?? $item['label'] ?? 'field_'.$fieldIndex), '_');
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
        foreach (array_values($item['options'] ?? []) as $optionIndex => $option) {
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

        $columnSpan = max(1, min(12, (int) ($item['column_span'] ?? 12)));

        return [
            'id' => self::uuid($item['id'] ?? null),
            'kind' => 'field',
            'name' => $name,
            'type' => (string) ($item['type'] ?? 'text'),
            'label' => (string) ($item['label'] ?? Str::headline($name)),
            'label_position' => $item['label_position'] ?? null,
            'label_override' => $item['label_override'] ?? null,
            'required' => (bool) ($item['required'] ?? false),
            'placeholder' => $item['placeholder'] ?? null,
            'hint' => $item['hint'] ?? null,
            'column_span' => $columnSpan,
            'validation_rules' => array_values(array_filter(
                is_array($item['validation_rules'] ?? null) ? $item['validation_rules'] : []
            )),
            'options' => $options,
            'meta' => FieldVisibility::normalizeMeta(
                InputMaskCatalog::normalizeFieldMeta(
                    is_array($item['meta'] ?? null) ? $item['meta'] : [],
                    fieldType: (string) ($item['type'] ?? 'text'),
                )
            ),
        ];
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
     * Flatten input fields (kind=field) across containers.
     *
     * @param  array<int, array<string, mixed>>  $schema
     * @return array<int, array<string, mixed>>
     */
    public static function fields(array $schema): array
    {
        $fields = [];
        foreach ($schema as $container) {
            foreach ($container['fields'] ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (ContentBlockCatalog::isField($item)) {
                    $fields[] = $item;
                    continue;
                }

                if (ContentBlockCatalog::isSection($item)) {
                    foreach ($item['children'] ?? [] as $child) {
                        if (is_array($child) && ContentBlockCatalog::isField($child)) {
                            $fields[] = $child;
                        }
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Flatten content blocks across containers.
     *
     * @param  array<int, array<string, mixed>>  $schema
     * @return array<int, array<string, mixed>>
     */
    public static function contentBlocks(array $schema): array
    {
        $blocks = [];
        foreach ($schema as $container) {
            foreach ($container['fields'] ?? [] as $item) {
                if (is_array($item) && ContentBlockCatalog::isContent($item)) {
                    $blocks[] = $item;
                }
            }
        }

        return $blocks;
    }

    /**
     * Normalize page chrome content blocks in settings.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public static function normalizePageChrome(array $settings): array
    {
        $chrome = data_get($settings, 'page_chrome', []);
        if (! is_array($chrome)) {
            return $settings;
        }

        $normalizedChrome = PageChrome::settings(['page_chrome' => $chrome]);

        $settings['page_chrome'] = [
            'header_blocks' => array_map(
                fn (array $block): array => self::normalizeContentBlock($block),
                $normalizedChrome['header_blocks'],
            ),
            'footer_blocks' => array_map(
                fn (array $block): array => self::normalizeContentBlock($block),
                $normalizedChrome['footer_blocks'],
            ),
            'header_placement' => $normalizedChrome['header_placement'],
            'footer_placement' => $normalizedChrome['footer_placement'],
        ];

        return $settings;
    }

    /**
     * Default thank-you settings merged with stored values.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public static function thankYouSettings(array $settings, array $form = []): array
    {
        $stored = data_get($settings, 'thank_you', []);
        if (! is_array($stored)) {
            $stored = [];
        }

        $layout = ThankYouLayouts::resolve((string) ($stored['layout'] ?? ThankYouLayouts::CORE_LAYOUT));

        $layoutMeta = is_array($stored['layout_meta'] ?? null) ? $stored['layout_meta'] : [];
        $layoutMeta = ThankYouLayouts::normalizeMeta($layout, $layoutMeta);

        return array_merge([
            'layout' => $layout,
            'title' => 'Thank you!',
            'message' => (string) ($form['success_message'] ?? 'Your response has been received and saved.'),
            'show_form_name' => true,
            'show_timestamp' => true,
            'submit_another_label' => 'Submit Another Response',
            'layout_meta' => $layoutMeta,
            'blocks' => [],
            'header_blocks' => [],
            'redirect_url' => $form['redirect_url'] ?? null,
            'auto_redirect' => false,
            'redirect_delay_seconds' => 3,
        ], $stored, [
            'layout' => $layout,
            'layout_meta' => $layoutMeta,
        ]);
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
