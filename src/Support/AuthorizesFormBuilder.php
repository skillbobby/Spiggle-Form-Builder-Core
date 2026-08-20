<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Facades\Auth;

class AuthorizesFormBuilder
{
    public static function check(string $permissionKey): bool
    {
        $user = Auth::user();

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
