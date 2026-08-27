<?php

namespace Spiggle\FormBuilder\Support;

use Composer\InstalledVersions;

final class InstalledPackageVersions
{
    public static function pretty(string $packageName): ?string
    {
        if (! class_exists(InstalledVersions::class)) {
            return null;
        }

        try {
            if (! InstalledVersions::isInstalled($packageName)) {
                return null;
            }

            $version = InstalledVersions::getPrettyVersion($packageName);

            return is_string($version) && $version !== '' ? $version : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    public static function formBuilderPackages(): array
    {
        $packages = [
            'spiggle/form-builder-core' => 'Core',
        ];

        if (self::pretty('spiggle/form-builder-pro') !== null) {
            $packages['spiggle/form-builder-pro'] = 'Pro';
        }

        return $packages;
    }

    /**
     * @param  array<string, string>  $packages  composer name => label (e.g. Core, Pro)
     */
    public static function summary(array $packages): string
    {
        $parts = [];

        foreach ($packages as $name => $label) {
            $version = self::pretty($name);
            if ($version === null) {
                continue;
            }
            $parts[] = trim($label.' '.$version);
        }

        return implode(' · ', $parts);
    }
}
