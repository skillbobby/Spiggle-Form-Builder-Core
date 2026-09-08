<?php

declare(strict_types=1);

namespace Spiggle\FormBuilder\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;

/**
 * Filament Create/Edit/Delete actions authorize via Gate → this policy
 * ({@see \Filament\Resources\Pages\Page::getDefaultActionAuthorizationResponse}),
 * not via Resource::canCreate()/canEdit() overrides. Keep ability names aligned
 * with config form-builder.permissions.manage_forms (Shield custom permission).
 */
class FormPolicy
{
    public function viewAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function view(?Authenticatable $user, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function create(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function update(?Authenticatable $user, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function delete(?Authenticatable $user, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function deleteAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function restore(?Authenticatable $user, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function forceDelete(?Authenticatable $user, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function forceDeleteAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function restoreAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function replicate(?Authenticatable $user, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }

    public function reorder(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($user);
    }
}
