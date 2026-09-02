<?php

namespace Spiggle\FormBuilder\Support;

class ThankYouLayouts
{
  public const CORE_LAYOUT = 'card';

  /**
   * @var list<string>
   */
  public const PRO_LAYOUTS = [
    'review_card',
    'campaign',
  ];

  /**
   * @return array<string, string>
   */
  public static function labels(): array
  {
    $labels = [
      self::CORE_LAYOUT => 'Card',
      'review_card' => 'Review Card',
      'campaign' => 'Connect',
    ];

    if (! FeatureCatalog::proUnlocked()) {
      foreach (self::PRO_LAYOUTS as $layout) {
        if (isset($labels[$layout])) {
          $labels[$layout] = $labels[$layout].' · PRO';
        }
      }
    }

    return $labels;
  }

  public static function isProLayout(string $layout): bool
  {
    return in_array($layout, self::PRO_LAYOUTS, true);
  }

  public static function isValidLayout(string $layout): bool
  {
    return $layout === self::CORE_LAYOUT || in_array($layout, self::PRO_LAYOUTS, true);
  }

  /**
   * Resolve stored layout for rendering (falls back to card when Pro is locked).
   */
  public static function resolve(string $layout): string
  {
    if (! self::isValidLayout($layout)) {
      return self::CORE_LAYOUT;
    }

    if ($layout !== self::CORE_LAYOUT && ! FeatureCatalog::proUnlocked()) {
      return self::CORE_LAYOUT;
    }

    return $layout;
  }

  /**
   * @return array<string, mixed>
   */
  public static function defaultMeta(string $layout): array
  {
    return match ($layout) {
      'review_card' => [
        'page_background' => '#3b82f6',
        'header_band_color' => '#a5b4fc',
        'hero_icon_type' => 'emoji',
        'hero_icon' => '✉️',
        'hero_image_url' => '',
        'cta_label' => 'LEAVE US A REVIEW',
        'cta_url' => '',
        'cta_color' => '#2563eb',
      ],
      'campaign' => [
        'page_background' => '#f8fafc',
        'accent_colors' => ['#f5b800', '#10b981'],
        'success_icon' => '✓',
        'success_icon_color' => '#10b981',
        'connect_card_title' => 'Connect With Us',
        'social_links' => [
          ['platform' => 'facebook', 'url' => ''],
          ['platform' => 'linkedin', 'url' => ''],
          ['platform' => 'pinterest', 'url' => ''],
          ['platform' => 'twitter', 'url' => ''],
        ],
        'website_card_title' => 'Visit Our Website',
        'website_button_label' => 'Visit Website',
        'website_button_url' => '',
        'website_button_color' => '#10b981',
      ],
      default => [],
    };
  }

  /**
   * @param  array<string, mixed>  $meta
   * @return array<string, mixed>
   */
  public static function normalizeMeta(string $layout, array $meta): array
  {
    $defaults = self::defaultMeta($layout);
    $merged = array_merge($defaults, $meta);

    if ($layout === 'campaign' && isset($meta['social_links']) && is_array($meta['social_links'])) {
      $merged['social_links'] = array_values(array_map(function (mixed $link): array {
        if (! is_array($link)) {
          return ['platform' => 'link', 'url' => ''];
        }

        return [
          'platform' => (string) ($link['platform'] ?? 'link'),
          'url' => (string) ($link['url'] ?? ''),
        ];
      }, $meta['social_links']));
    }

    if ($layout === 'campaign' && isset($meta['accent_colors']) && is_array($meta['accent_colors'])) {
      $merged['accent_colors'] = [
        (string) ($meta['accent_colors'][0] ?? $defaults['accent_colors'][0] ?? '#f5b800'),
        (string) ($meta['accent_colors'][1] ?? $defaults['accent_colors'][1] ?? '#10b981'),
      ];
    }

    return $merged;
  }

  /**
   * Blade component name for a resolved layout.
   */
  public static function component(string $layout): string
  {
    return match (self::resolve($layout)) {
      'review_card' => 'form-builder::components.thank-you.review-card',
      'campaign' => 'form-builder::components.thank-you.campaign',
      default => 'form-builder::components.thank-you.card',
    };
  }
}
