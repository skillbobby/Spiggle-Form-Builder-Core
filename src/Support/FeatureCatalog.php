<?php

namespace Spiggle\FormBuilder\Support;

use Spiggle\FormBuilder\Contracts\ProUnlock;

class FeatureCatalog
{
    /**
     * Layouts that ship in Core (unlicensed / public package).
     *
     * @var list<string>
     */
    public const CORE_LAYOUTS = [
        'single',
    ];

    /**
     * Layouts that require Pro.
     *
     * @var list<string>
     */
    public const PRO_LAYOUTS = [
        'wizard',
        'tabs',
        'pages',
    ];

    /**
     * Export formats that ship in Core.
     *
     * @var list<string>
     */
    public const CORE_EXPORTS = [
        'csv',
    ];

    /**
     * Export formats that require Pro.
     *
     * @var list<string>
     */
    public const PRO_EXPORTS = [
        'xlsx',
        'pdf',
    ];

    /**
     * Named Pro capabilities (beyond layouts / exports).
     *
     * @var list<string>
     */
    public const PRO_FEATURES = [
        'advanced_layouts',
        'premium_exports',
        'analytics_charts',
        'form_clone',
        'import_custom_fields',
        'email_notify',
        'page_drafts',
    ];

    /**
     * @return list<string>
     */
    public static function coreLayouts(): array
    {
        return self::CORE_LAYOUTS;
    }

    /**
     * @return list<string>
     */
    public static function proLayouts(): array
    {
        return self::PRO_LAYOUTS;
    }

    public static function isProLayout(string $type): bool
    {
        return in_array($type, self::PRO_LAYOUTS, true);
    }

    public static function isCoreLayout(string $type): bool
    {
        return in_array($type, self::CORE_LAYOUTS, true);
    }

    public static function isProExport(string $format): bool
    {
        return in_array(strtolower($format), self::PRO_EXPORTS, true);
    }

    /**
     * Pro features unlock only when the Pro package registers ProUnlock and
     * reports an authorized license (or an explicit testing bypass).
     */
    public static function proUnlocked(): bool
    {
        if (! app()->bound(ProUnlock::class)) {
            return false;
        }

        return app(ProUnlock::class)->unlocked();
    }

    /**
     * Labels for the layout dropdown. Pro layouts stay visible in Core so
     * the upsell can intercept selection.
     *
     * @return array<string, string>
     */
    public static function layoutLabels(): array
    {
        $labels = ContainerTypes::rawLabels();

        if (self::proUnlocked()) {
            return $labels;
        }

        foreach (self::PRO_LAYOUTS as $type) {
            if (isset($labels[$type])) {
                $labels[$type] = $labels[$type].' · PRO';
            }
        }

        return $labels;
    }

    /**
     * Export format options for Filament actions.
     *
     * @return array<string, string>
     */
    public static function exportFormatLabels(): array
    {
        $all = [
            'csv' => 'CSV',
            'xlsx' => 'Excel (XML)',
            'pdf' => 'PDF',
        ];

        if (self::proUnlocked()) {
            return $all;
        }

        return [
            'csv' => 'CSV',
            'xlsx' => 'Excel (XML) · PRO',
            'pdf' => 'PDF · PRO',
        ];
    }

    public static function layoutTitle(string $type): string
    {
        $labels = ContainerTypes::rawLabels();

        return $labels[$type] ?? str_replace('_', ' ', $type);
    }
}
