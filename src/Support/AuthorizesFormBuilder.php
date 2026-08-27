<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class AuthorizesFormBuilder
{
    public static function check(string $permissionKey): bool
    {
        return static::userCan(Auth::user(), $permissionKey);
    }

    public static function userCanManageForms(?Authenticatable $user = null): bool
    {
        return static::userCan($user ?? Auth::user(), 'manage_forms');
    }

    public static function userCanViewSubmissions(?Authenticatable $user = null): bool
    {
        $user ??= Auth::user();

        return static::userCan($user, 'view_submissions')
            || static::userCan($user, 'manage_submissions')
            || static::userCanManageForms($user);
    }

    public static function userCanManageSubmissions(?Authenticatable $user = null): bool
    {
        $user ??= Auth::user();

        return static::userCan($user, 'manage_submissions')
            || static::userCanManageForms($user);
    }

    public static function userCanExportSubmissions(?Authenticatable $user = null): bool
    {
        $user ??= Auth::user();

        return static::userCan($user, 'export_submissions')
            || static::userCanManageSubmissions($user);
    }

    public static function userCan(?Authenticatable $user, string $permissionKey): bool
    {
        if (! $user) {
            return false;
        }

        $permission = config('form-builder.permissions.'.$permissionKey, $permissionKey);

        if (method_exists($user, 'can') && method_exists($user, 'hasRole')) {
            try {
                if ($user->can($permission) || $user->hasRole('super_admin')) {
                    return true;
                }

                if (! class_exists(\Spatie\Permission\Models\Permission::class)) {
                    return true;
                }

                $exists = \Spatie\Permission\Models\Permission::query()
                    ->where('name', $permission)
                    ->exists();

                return ! $exists;
            } catch (\Throwable) {
                return true;
            }
        }

        return true;
    }
}
