<?php

namespace Spiggle\FormBuilder\Licensing;

use Spiggle\DynamicFields\Licensing\AddonLicenseRegistry;
use Spiggle\DynamicFields\Licensing\AddonRegistration;
use Spiggle\FormBuilder\Support\InstalledPackageVersions;

final class RegistersAddonLicense
{
    public static function boot(): void
    {
        if (! class_exists(AddonLicenseRegistry::class) || ! app()->bound(AddonLicenseRegistry::class)) {
            return;
        }

        if (class_exists(\Spiggle\FormBuilder\Pro\Licensing\LicenseManager::class)) {
            return;
        }

        app(AddonLicenseRegistry::class)->register(new AddonRegistration(
            id: 'form-builder',
            name: 'Form Builder',
            inactiveDescription: 'Community Edition is active. Install Form Builder Pro to unlock wizard/tabs/pages, XLSX/PDF export, charts, clone, and email notify hooks.',
            purchaseLabel: 'Buy Form Builder Pro',
            licenseManagerClass: CommunityLicenseManager::class,
            permission: (string) config('form-builder.permissions.manage_forms', 'manage_forms'),
            sort: 92,
            packages: InstalledPackageVersions::formBuilderPackages(),
        ));
    }
}
