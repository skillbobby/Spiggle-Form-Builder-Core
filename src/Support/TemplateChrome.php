<?php

namespace Spiggle\FormBuilder\Support;

class TemplateChrome
{
    /**
     * @return array<string, mixed>
     */
    public static function banner(string $asset, string $height = '140px', string $caption = '', ?string $blockPlacement = null): array
    {
        $meta = [
            'image_url' => TemplateChromeAssets::url($asset),
            'alt' => $caption !== '' ? $caption : str_replace('-', ' ', pathinfo($asset, PATHINFO_FILENAME)),
            'height' => $height,
            'caption' => $caption,
        ];

        if ($blockPlacement !== null) {
            $meta['placement'] = $blockPlacement;
        }

        return TemplateBuilder::content('banner', $meta, 12);
    }

    /**
     * @return array<string, mixed>
     */
    public static function footerText(string $text): array
    {
        return TemplateBuilder::content('footer', [
            'text' => $text,
            'alignment' => 'center',
            'muted' => true,
        ], 12);
    }

    /**
     * @param  list<array<string, mixed>>  $headerBlocks
     * @param  list<array<string, mixed>>  $footerBlocks
     * @return array<string, mixed>
     */
    public static function settings(
        array $headerBlocks = [],
        array $footerBlocks = [],
        string $headerPlacement = 'inside',
        string $footerPlacement = 'inside',
    ): array {
        return [
            'page_chrome' => [
                'header_blocks' => $headerBlocks,
                'footer_blocks' => $footerBlocks,
                'header_placement' => $headerPlacement,
                'footer_placement' => $footerPlacement,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function contactShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('contact-header.svg', '128px', 'Questions? We are here to help.')],
            headerPlacement: 'outside_above',
        );
    }

    /** @return array<string, mixed> */
    public static function conferenceShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('conference-header.svg', '152px', 'Annual Summit 2026')],
            footerBlocks: [self::footerText('© 2026 Summit Events · Secure registration powered by Spiggle')],
            headerPlacement: 'inside_bleed',
            footerPlacement: 'inside',
        );
    }

    /** @return array<string, mixed> */
    public static function eventShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('event-header.svg', '144px', 'Reserve your spot')],
            headerPlacement: 'inside_bleed',
        );
    }

    /** @return array<string, mixed> */
    public static function bookingShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('booking-header.svg', '120px', 'Book your appointment')],
            headerPlacement: 'inside',
        );
    }

    /** @return array<string, mixed> */
    public static function hotelShowcase(): array
    {
        return self::settings(
            footerBlocks: [self::banner('hotel-footer.svg', '88px', 'Grand Vista Hotels')],
            footerPlacement: 'outside_below',
        );
    }

    /** @return array<string, mixed> */
    public static function donationShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('donation-header.svg', '136px', 'Every gift makes a difference')],
            footerBlocks: [self::footerText('Tax receipts are emailed automatically. Thank you for your support.')],
            headerPlacement: 'inside_bleed',
        );
    }

    /** @return array<string, mixed> */
    public static function orderShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('order-header.svg', '132px', 'Fast, reliable fulfillment')],
            footerBlocks: [self::footerText('Orders ship within 2 business days · Free returns within 30 days')],
            headerPlacement: 'inside_bleed',
        );
    }

    /** @return array<string, mixed> */
    public static function schoolTabsShowcase(): array
    {
        return self::settings(
            headerBlocks: [self::banner('school-header.svg', '124px', 'Fall enrollment now open')],
            headerPlacement: 'inside',
        );
    }

    /** @return array<string, mixed> */
    public static function feedbackShowcase(): array
    {
        return self::settings(
            footerBlocks: [
                self::banner('feedback-footer.svg', '80px', 'Your voice shapes our roadmap'),
                self::footerText('Responses are confidential and reviewed weekly by our product team.'),
            ],
            footerPlacement: 'inside',
        );
    }
}
