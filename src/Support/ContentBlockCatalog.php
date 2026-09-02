<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Str;

class ContentBlockCatalog
{
  /**
   * @var list<string>
   */
  public const CORE_BLOCKS = [
    'heading',
    'paragraph',
    'divider',
    'spacer',
    'banner',
    'image',
    'video',
    'footer',
    'button',
    'section',
  ];

  /**
   * @var list<string>
   */
  public const PRO_BLOCKS = [
    'social_links',
    'button_group',
    'html',
  ];

  /**
   * @return array<string, string>
   */
  public static function labels(): array
  {
    $labels = [
      'heading' => 'Heading',
      'paragraph' => 'Paragraph',
      'divider' => 'Divider',
      'spacer' => 'Spacer',
      'banner' => 'Banner',
      'image' => 'Image',
      'video' => 'Video',
      'footer' => 'Footer text',
      'button' => 'Link button',
      'section' => 'Section container',
      'social_links' => 'Social links',
      'button_group' => 'Button group',
      'html' => 'Custom HTML',
    ];

    if (! FeatureCatalog::proUnlocked()) {
      foreach (self::PRO_BLOCKS as $type) {
        if (isset($labels[$type])) {
          $labels[$type] = $labels[$type].' · PRO';
        }
      }
    }

    return $labels;
  }

  /**
   * @return list<string>
   */
  public static function coreBlocks(): array
  {
    return self::CORE_BLOCKS;
  }

  /**
   * @return list<string>
   */
  public static function proBlocks(): array
  {
    return self::PRO_BLOCKS;
  }

  public static function isProBlock(string $type): bool
  {
    return in_array($type, self::PRO_BLOCKS, true);
  }

  public static function isContent(array $item): bool
  {
    return ($item['kind'] ?? 'field') === 'content';
  }

  public static function isField(array $item): bool
  {
    return ! self::isContent($item);
  }

  public static function isSection(array $item): bool
  {
    return self::isContent($item) && (string) ($item['type'] ?? '') === 'section';
  }

  public static function isSectionZone(string $zone): bool
  {
    return str_starts_with($zone, 'section_');
  }

  public static function sectionIdFromZone(string $zone): ?string
  {
    return self::isSectionZone($zone) ? substr($zone, 8) : null;
  }

  /**
   * @return array<string, mixed>
   */
  public static function sectionStyles(array $meta): array
  {
    $shadow = (string) ($meta['shadow'] ?? 'sm');

    $boxShadow = match ($shadow) {
      'md' => '0 10px 24px rgb(15 23 42 / 0.08)',
      'lg' => '0 16px 36px rgb(15 23 42 / 0.12)',
      'none' => 'none',
      default => '0 1px 3px rgb(15 23 42 / 0.08), 0 1px 2px rgb(15 23 42 / 0.04)',
    };

    return [
      'background' => (string) ($meta['background'] ?? '#ffffff'),
      'border' => sprintf(
        '%s solid %s',
        (string) ($meta['border_width'] ?? '1px'),
        (string) ($meta['border_color'] ?? '#e5e7eb'),
      ),
      'border-radius' => (string) ($meta['border_radius'] ?? '12px'),
      'padding' => (string) ($meta['padding'] ?? '1.25rem'),
      'box-shadow' => $boxShadow,
    ];
  }

  /**
   * @return array<string, mixed>
   */
  public static function defaults(string $type): array
  {
    $meta = match ($type) {
      'heading' => ['text' => 'Heading', 'level' => 2, 'alignment' => 'left'],
      'paragraph' => ['text' => 'Add your intro text here.', 'alignment' => 'left'],
      'divider' => ['style' => 'solid'],
      'spacer' => ['height' => '24px'],
      'banner' => ['image_url' => null, 'alt' => '', 'height' => '160px', 'background' => '#ecfdf5', 'caption' => ''],
      'image' => ['image_url' => null, 'alt' => '', 'alignment' => 'center', 'max_height' => '320px'],
      'video' => ['url' => '', 'provider' => 'youtube', 'aspect_ratio' => '16/9'],
      'footer' => ['text' => '© '.date('Y').' Your company', 'alignment' => 'center', 'muted' => true],
      'button' => ['text' => 'Learn more', 'url' => '', 'style' => 'primary'],
      'social_links' => ['links' => [
        ['platform' => 'linkedin', 'url' => ''],
        ['platform' => 'twitter', 'url' => ''],
      ]],
      'button_group' => ['buttons' => [
        ['title' => 'Connect with us', 'text' => 'Follow', 'url' => ''],
        ['title' => 'Visit our website', 'text' => 'Open site', 'url' => ''],
      ]],
      'html' => ['html' => '<p>Custom content</p>'],
      'section' => [
        'title' => 'Section',
        'show_title' => true,
        'show_divider' => true,
        'border_width' => '1px',
        'border_color' => '#e5e7eb',
        'border_radius' => '12px',
        'background' => '#ffffff',
        'shadow' => 'sm',
        'padding' => '1.25rem',
      ],
      default => [],
    };

    $block = [
      'kind' => 'content',
      'type' => $type,
      'column_span' => 12,
      'meta' => $meta,
    ];

    if ($type === 'section') {
      $block['children'] = [];
    }

    return $block;
  }

  /**
   * Default thank-you content: centered heading + message paragraph.
   *
   * @return list<array<string, mixed>>
   */
  public static function defaultThankYouBlocks(string $title, string $message, ?string $layout = null): array
  {
    if ($layout === 'review_card') {
      return [
        array_merge(self::defaults('heading'), [
          'id' => (string) Str::uuid(),
          'meta' => [
            'text' => 'Thank You',
            'level' => 2,
            'alignment' => 'center',
          ],
        ]),
        array_merge(self::defaults('heading'), [
          'id' => (string) Str::uuid(),
          'meta' => [
            'text' => 'for your purchase!',
            'level' => 3,
            'alignment' => 'center',
          ],
        ]),
        array_merge(self::defaults('paragraph'), [
          'id' => (string) Str::uuid(),
          'meta' => [
            'text' => "We'd love to hear your feedback. Click below to let us know about your experience purchasing tickets from us.",
            'alignment' => 'center',
          ],
        ]),
      ];
    }

    if ($layout === 'campaign') {
      return [
        array_merge(self::defaults('heading'), [
          'id' => (string) Str::uuid(),
          'meta' => [
            'text' => 'Thank you!',
            'level' => 2,
            'alignment' => 'center',
          ],
        ]),
        array_merge(self::defaults('paragraph'), [
          'id' => (string) Str::uuid(),
          'meta' => [
            'text' => "We've sent your free report to your inbox so it's easy to access. You can find more information on our website and social pages.",
            'alignment' => 'center',
          ],
        ]),
      ];
    }

    return [
      array_merge(self::defaults('heading'), [
        'id' => (string) Str::uuid(),
        'meta' => [
          'text' => $title !== '' ? $title : 'Thank you!',
          'level' => 2,
          'alignment' => 'center',
        ],
      ]),
      array_merge(self::defaults('paragraph'), [
        'id' => (string) Str::uuid(),
        'meta' => [
          'text' => $message !== '' ? $message : 'Your response has been received and saved.',
          'alignment' => 'center',
        ],
      ]),
    ];
  }

  /**
   * Default thank-you header content shown under the success icon.
   *
   * @return list<array<string, mixed>>
   */
  public static function defaultThankYouHeaderBlocks(string $formName): array
  {
    return [
      array_merge(self::defaults('paragraph'), [
        'id' => (string) Str::uuid(),
        'meta' => [
          'text' => $formName !== '' ? $formName : 'Form name',
          'alignment' => 'center',
        ],
      ]),
    ];
  }

  public static function sanitizeHtml(string $html): string
  {
    return strip_tags($html, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><blockquote><span><div>');
  }
}
