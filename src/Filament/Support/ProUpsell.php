<?php

namespace Spiggle\FormBuilder\Filament\Support;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\ThankYouLayouts;

class ProUpsell
{
    public static function checkoutUrl(): string
    {
        $url = trim((string) (
            config('form-builder.licensing.checkout_url')
            ?: config('form-builder.upsell.checkout_url', '')
        ));

        $url = self::sanitizeCheckoutUrl($url);
        $domain = (string) config('app.url');

        if ($url === null) {
            if (class_exists(\Spiggle\DynamicFields\Licensing\AddonLicenseRegistry::class)) {
                return \Spiggle\DynamicFields\Licensing\AddonLicenseRegistry::licensePageUrl();
            }

            return '';
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'checkout[custom][domain]='.urlencode($domain);
    }

    /**
     * HTTPS-only checkout URLs; host must match allowlist when configured.
     */
    public static function sanitizeCheckoutUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https' || $host === '') {
            return null;
        }

        $allowed = config('form-builder.licensing.checkout_allowed_hosts')
            ?? config('form-builder.upsell.checkout_allowed_hosts', ['lemonsqueezy.com']);

        if (is_array($allowed) && $allowed !== []) {
            $ok = false;
            foreach ($allowed as $suffix) {
                $suffix = strtolower(ltrim(trim((string) $suffix), '.'));
                if ($suffix !== '' && ($host === $suffix || str_ends_with($host, '.'.$suffix))) {
                    $ok = true;
                    break;
                }
            }
            if (! $ok) {
                return null;
            }
        }

        return $url;
    }

    public static function notify(string $feature): void
    {
        $checkout = self::checkoutUrl();

        $notification = Notification::make()
            ->warning()
            ->persistent()
            ->title('Pro Feature Required')
            ->body($feature.' requires a Form Builder Pro license.');

        if ($checkout !== '') {
            $notification->actions([
                Action::make('buy')
                    ->button()
                    ->label('Buy Pro License')
                    ->url($checkout)
                    ->openUrlInNewTab(),
            ]);
        }

        $notification->send();
    }

    public static function guardLayout(?string $state, callable $set): void
    {
        if ($state === null || $state === '' || FeatureCatalog::proUnlocked()) {
            return;
        }

        if (! FeatureCatalog::isProLayout($state)) {
            return;
        }

        self::notify(FeatureCatalog::layoutTitle($state).' layout');
        $set('container_type', 'single');
    }

    public static function guardExportFormat(?string $state, callable $set): void
    {
        if ($state === null || $state === '' || FeatureCatalog::proUnlocked()) {
            return;
        }

        if (! FeatureCatalog::isProExport($state)) {
            return;
        }

        self::notify(strtoupper($state).' export');
        $set('format', 'csv');
    }

    public static function guardNotifyEmails(mixed $state, callable $set): void
    {
        if (FeatureCatalog::proUnlocked()) {
            return;
        }

        if ($state === null || $state === [] || $state === '') {
            return;
        }

        self::notify('Email notifications');
        $set('notify_emails', []);
    }

    public static function guardContentBlock(?string $type): bool
    {
        if ($type === null || $type === '' || FeatureCatalog::proUnlocked()) {
            return true;
        }

        if (! \Spiggle\FormBuilder\Support\ContentBlockCatalog::isProBlock($type)) {
            return true;
        }

        self::notify(ucfirst(str_replace('_', ' ', $type)).' content block');

        return false;
    }

    public static function guardThankYouLayout(?string $layout, callable $set): void
    {
        if ($layout === null || $layout === '' || $layout === ThankYouLayouts::CORE_LAYOUT || FeatureCatalog::proUnlocked()) {
            return;
        }

        if (! ThankYouLayouts::isProLayout($layout)) {
            return;
        }

        $labels = ThankYouLayouts::labels();

        self::notify(($labels[$layout] ?? 'Pro thank-you').' layout');
        $set('layout', ThankYouLayouts::CORE_LAYOUT);
    }

    public static function guardThankYouCustomization(): bool
    {
        if (FeatureCatalog::proUnlocked()) {
            return true;
        }

        self::notify('Thank-you page customization');

        return false;
    }

    public static function guardInputMask(?string $mask): bool
    {
        if ($mask === null || $mask === '' || FeatureCatalog::proUnlocked()) {
            return true;
        }

        if (! in_array($mask, \Spiggle\FormBuilder\Support\InputMaskCatalog::MASK_TYPES, true)) {
            return true;
        }

        self::notify('Input masking');

        return false;
    }
}
