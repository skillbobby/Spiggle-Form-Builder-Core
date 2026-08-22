<?php

namespace Spiggle\FormBuilder\Support;

class ContainerTypes
{
    /**
     * Raw labels from config (no PRO badges).
     *
     * @return array<string, string>
     */
    public static function rawLabels(): array
    {
        return config('form-builder.container_types', [
            'single' => 'Single page',
            'wizard' => 'Wizard',
            'tabs' => 'Tabs',
            'pages' => 'Pages',
            'accordion' => 'Accordion',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return FeatureCatalog::layoutLabels();
    }

    public static function isStepped(string $type): bool
    {
        return in_array($type, ['wizard', 'pages'], true);
    }

    /**
     * Layouts that use Back / Next between sections.
     */
    public static function usesStepNav(string $type): bool
    {
        return in_array($type, ['wizard', 'pages', 'tabs', 'accordion'], true);
    }

    /**
     * Normalize a layout to a Core-safe value when Pro is locked.
     */
    public static function resolve(string $type): string
    {
        if (FeatureCatalog::proUnlocked() || FeatureCatalog::isCoreLayout($type)) {
            return $type;
        }

        return 'single';
    }
}
