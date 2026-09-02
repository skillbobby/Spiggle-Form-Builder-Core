<?php

namespace Spiggle\FormBuilder\Support;

class LayoutPreview
{
    /**
     * Colorful mini wireframe SVG for a form layout type.
     */
    public static function svg(string $type): string
    {
        return match ($type) {
            'accordion' => self::accordion(),
            'tabs' => self::tabs(),
            'wizard' => self::wizard(),
            'pages' => self::pages(),
            default => self::single(),
        };
    }

    protected static function single(): string
    {
        return <<<'SVG'
<svg class="cfs-layout-svg" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect width="120" height="80" rx="8" fill="#fdf2f8"/>
  <rect x="14" y="12" width="52" height="6" rx="3" fill="#f9a8d4"/>
  <rect x="14" y="24" width="92" height="8" rx="4" fill="#fbcfe8"/>
  <rect x="14" y="38" width="92" height="8" rx="4" fill="#fbcfe8"/>
  <rect x="14" y="52" width="64" height="8" rx="4" fill="#fbcfe8"/>
  <rect x="14" y="66" width="36" height="10" rx="5" fill="#ec4899"/>
</svg>
SVG;
    }

    protected static function accordion(): string
    {
        return <<<'SVG'
<svg class="cfs-layout-svg" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect width="120" height="80" rx="8" fill="#eff6ff"/>
  <rect x="10" y="10" width="100" height="18" rx="4" fill="#dbeafe" stroke="#93c5fd" stroke-width="1"/>
  <path d="M98 19l4 4 4-4" stroke="#3b82f6" stroke-width="1.5" stroke-linecap="round"/>
  <rect x="14" y="15" width="40" height="4" rx="2" fill="#60a5fa"/>
  <rect x="10" y="32" width="100" height="18" rx="4" fill="#dbeafe" stroke="#93c5fd" stroke-width="1"/>
  <path d="M98 41l4 4 4-4" stroke="#3b82f6" stroke-width="1.5" stroke-linecap="round"/>
  <rect x="14" y="37" width="36" height="4" rx="2" fill="#60a5fa"/>
  <rect x="10" y="54" width="100" height="18" rx="4" fill="#bfdbfe"/>
  <rect x="14" y="60" width="72" height="4" rx="2" fill="#3b82f6"/>
  <rect x="14" y="68" width="48" height="3" rx="1.5" fill="#93c5fd"/>
</svg>
SVG;
    }

    protected static function tabs(): string
    {
        return <<<'SVG'
<svg class="cfs-layout-svg" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect width="120" height="80" rx="8" fill="#ecfdf5"/>
  <rect x="10" y="12" width="28" height="12" rx="4" fill="#6ee7b7"/>
  <rect x="40" y="12" width="28" height="12" rx="4" fill="#d1fae5"/>
  <rect x="70" y="12" width="28" height="12" rx="4" fill="#d1fae5"/>
  <rect x="10" y="28" width="100" height="44" rx="4" fill="#fff" stroke="#a7f3d0" stroke-width="1"/>
  <rect x="18" y="36" width="48" height="5" rx="2.5" fill="#34d399"/>
  <rect x="18" y="46" width="84" height="6" rx="3" fill="#d1fae5"/>
  <rect x="18" y="56" width="84" height="6" rx="3" fill="#d1fae5"/>
</svg>
SVG;
    }

    protected static function wizard(): string
    {
        return <<<'SVG'
<svg class="cfs-layout-svg" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect width="120" height="80" rx="8" fill="#f5f3ff"/>
  <circle cx="24" cy="18" r="8" fill="#8b5cf6"/>
  <text x="24" y="21" text-anchor="middle" fill="#fff" font-size="8" font-weight="700">1</text>
  <rect x="36" y="17" width="48" height="2" rx="1" fill="#c4b5fd"/>
  <circle cx="92" cy="18" r="8" fill="#ddd6fe" stroke="#a78bfa" stroke-width="1.5"/>
  <text x="92" y="21" text-anchor="middle" fill="#7c3aed" font-size="8" font-weight="700">2</text>
  <rect x="14" y="34" width="92" height="6" rx="3" fill="#c4b5fd"/>
  <rect x="14" y="46" width="92" height="8" rx="4" fill="#ede9fe"/>
  <rect x="14" y="58" width="92" height="8" rx="4" fill="#ede9fe"/>
  <rect x="68" y="68" width="38" height="8" rx="4" fill="#8b5cf6"/>
</svg>
SVG;
    }

    protected static function pages(): string
    {
        return <<<'SVG'
<svg class="cfs-layout-svg" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <rect width="120" height="80" rx="8" fill="#fff7ed"/>
  <rect x="14" y="14" width="92" height="5" rx="2.5" fill="#fdba74"/>
  <rect x="14" y="24" width="92" height="8" rx="4" fill="#fed7aa"/>
  <rect x="14" y="36" width="92" height="8" rx="4" fill="#fed7aa"/>
  <rect x="14" y="48" width="60" height="8" rx="4" fill="#fed7aa"/>
  <circle cx="48" cy="68" r="4" fill="#f97316"/>
  <circle cx="60" cy="68" r="4" fill="#fdba74"/>
  <circle cx="72" cy="68" r="4" fill="#fdba74"/>
</svg>
SVG;
    }
}
