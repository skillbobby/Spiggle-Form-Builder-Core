<?php

namespace Spiggle\FormBuilder\Support;

class TemplateBuilder
{
    /**
     * @param  array<int, array<string, mixed>>  $schema
     * @return array<string, mixed>
     */
    public static function make(
        string $slug,
        string $name,
        string $description,
        string $category,
        array $schema,
        string $containerType = 'single',
        string $tier = 'core',
        string $icon = 'heroicon-o-document-text',
        string $successMessage = 'Thanks — your response has been recorded.',
        ?array $settings = null,
    ): array {
        $definition = [
            'name' => $name,
            'slug' => $slug,
            'base_path' => $slug,
            'description' => $description,
            'container_type' => $containerType,
            'is_published' => false,
            'is_active' => true,
            'success_message' => $successMessage,
            'schema' => $schema,
        ];

        if ($settings !== null) {
            $definition['settings'] = $settings;
        }

        return [
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'icon' => $icon,
            'tier' => $tier,
            'definition' => $definition,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function field(string $name, string $type, string $label, array $overrides = []): array
    {
        return array_merge([
            'name' => $name,
            'type' => $type,
            'label' => $label,
            'column_span' => 12,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function content(string $type, array $meta = [], int $columnSpan = 12): array
    {
        return [
            'kind' => 'content',
            'type' => $type,
            'column_span' => $columnSpan,
            'meta' => $meta,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    public static function page(string $label, array $fields, ?string $description = null): array
    {
        return array_filter([
            'label' => $label,
            'description' => $description,
            'fields' => $fields,
        ], fn ($value): bool => $value !== null);
    }

    public static function proLayout(string $containerType): string
    {
        return $containerType;
    }

    public static function tierForLayout(string $containerType): string
    {
        return $containerType === 'single' ? 'core' : 'pro';
    }
}
