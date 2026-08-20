<?php

namespace Spiggle\FormBuilder\Filament\Support;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Spiggle\FormBuilder\Support\FeatureCatalog;

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
            $page = 'Spiggle\\FormBuilder\\Pro\\Filament\\Pages\\ManageAddonLicense';
            if (class_exists($page)) {
                try {
                    return $page::getUrl();
                } catch (\Throwable) {
                    return '';
                }
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
}
