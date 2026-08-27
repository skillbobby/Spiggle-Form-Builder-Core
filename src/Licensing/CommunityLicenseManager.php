<?php

namespace Spiggle\FormBuilder\Licensing;

use Spiggle\DynamicFields\Licensing\Contracts\AddonLicenseManager;

/**
 * Community (Core-only) placeholder when Form Builder Pro is not installed.
 */
final class CommunityLicenseManager implements AddonLicenseManager
{
    public function status(): array
    {
        return [
            'enforced' => true,
            'activated' => false,
            'authorized' => false,
            'checkout_url' => $this->checkoutUrl(),
        ];
    }

    public function activate(string $licenseKey): object
    {
        return (object) [
            'ok' => false,
            'message' => 'Install Form Builder Pro to activate a license on this server.',
        ];
    }

    public function deactivate(): object
    {
        return (object) [
            'ok' => true,
            'message' => 'Community Edition — no license to deactivate.',
        ];
    }

    protected function checkoutUrl(): ?string
    {
        $url = trim((string) config('form-builder.upsell.checkout_url', ''));

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($scheme !== 'https' || $host === '') {
            return null;
        }

        $allowedHosts = config('form-builder.upsell.checkout_allowed_hosts', ['lemonsqueezy.com']);

        if (is_array($allowedHosts) && $allowedHosts !== [] && ! $this->hostAllowed($host, $allowedHosts)) {
            return null;
        }

        return $url;
    }

    /**
     * @param  list<string>  $allowedHosts
     */
    protected function hostAllowed(string $host, array $allowedHosts): bool
    {
        foreach ($allowedHosts as $allowed) {
            $allowed = strtolower(ltrim(trim((string) $allowed), '.'));
            if ($allowed === '') {
                continue;
            }

            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }
}
