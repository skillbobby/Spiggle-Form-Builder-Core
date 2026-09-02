<?php

namespace Spiggle\FormBuilder\Support;

class PageChrome
{
    /** @var list<string> */
    public const HEADER_PLACEMENTS = ['inside', 'inside_bleed', 'outside_above'];

    /** @var list<string> */
    public const FOOTER_PLACEMENTS = ['inside', 'inside_bleed', 'outside_below'];

    /** @var list<string> */
    public const BLOCK_PLACEMENTS = ['default', 'full_width', 'outside'];

    public static function normalizeHeaderPlacement(?string $value): string
    {
        return in_array($value, self::HEADER_PLACEMENTS, true) ? $value : 'inside';
    }

    public static function normalizeFooterPlacement(?string $value): string
    {
        return in_array($value, self::FOOTER_PLACEMENTS, true) ? $value : 'inside';
    }

    public static function normalizeBlockPlacement(?string $value): string
    {
        return in_array($value, self::BLOCK_PLACEMENTS, true) ? $value : 'default';
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{header_blocks: list<array<string, mixed>>, footer_blocks: list<array<string, mixed>>, header_placement: string, footer_placement: string}
     */
    public static function settings(array $settings): array
    {
        $chrome = is_array($settings['page_chrome'] ?? null) ? $settings['page_chrome'] : [];

        return [
            'header_blocks' => array_values(array_filter($chrome['header_blocks'] ?? [], fn ($b): bool => is_array($b))),
            'footer_blocks' => array_values(array_filter($chrome['footer_blocks'] ?? [], fn ($b): bool => is_array($b))),
            'header_placement' => self::normalizeHeaderPlacement($chrome['header_placement'] ?? null),
            'footer_placement' => self::normalizeFooterPlacement($chrome['footer_placement'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public static function resolveBlockPlacement(array $block, string $zone, string $zonePlacement): string
    {
        $override = self::normalizeBlockPlacement($block['meta']['placement'] ?? null);

        if ($override === 'outside') {
            return $zone === 'header' ? 'outside_above' : 'outside_below';
        }

        if ($override === 'full_width') {
            return 'inside_bleed';
        }

        return $zonePlacement;
    }

    public static function isOutsidePlacement(string $placement): bool
    {
        return in_array($placement, ['outside_above', 'outside_below'], true);
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return array{inside: list<array<string, mixed>>, outside: list<array<string, mixed>>}
     */
    public static function partitionBlocks(array $blocks, string $zone, string $zonePlacement): array
    {
        $inside = [];
        $outside = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $resolved = self::resolveBlockPlacement($block, $zone, $zonePlacement);

            if (self::isOutsidePlacement($resolved)) {
                $outside[] = $block;
            } else {
                $inside[] = $block;
            }
        }

        return ['inside' => $inside, 'outside' => $outside];
    }

    /**
     * @param  list<array<string, mixed>>  $insideBlocks
     */
    public static function zoneBleeds(string $zonePlacement, array $insideBlocks, string $zone): bool
    {
        if ($zonePlacement === 'inside_bleed') {
            return true;
        }

        foreach ($insideBlocks as $block) {
            if (self::resolveBlockPlacement($block, $zone, $zonePlacement) === 'inside_bleed') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public static function blockBleeds(array $block, string $zone, string $zonePlacement): bool
    {
        return self::resolveBlockPlacement($block, $zone, $zonePlacement) === 'inside_bleed';
    }

    /**
     * @return array<string, string>
     */
    public static function headerPlacementLabels(): array
    {
        return [
            'inside' => 'Inside (padded)',
            'inside_bleed' => 'Inside (full width)',
            'outside_above' => 'Above form card',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function footerPlacementLabels(): array
    {
        return [
            'inside' => 'Inside (padded)',
            'inside_bleed' => 'Inside (full width)',
            'outside_below' => 'Below form card',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function blockPlacementLabels(string $zone): array
    {
        return [
            'default' => 'Use zone default',
            'full_width' => 'Full width (edge to edge)',
            'outside' => $zone === 'header' ? 'Above form card' : 'Below form card',
        ];
    }
}
