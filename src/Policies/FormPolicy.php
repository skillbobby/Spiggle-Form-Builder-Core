<?php

declare(strict_types=1);

namespace Spiggle\FormBuilder\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;

class FormPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function view(AuthUser $authUser, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function update(AuthUser $authUser, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function delete(AuthUser $authUser, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function restore(AuthUser $authUser, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function forceDelete(AuthUser $authUser, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function replicate(AuthUser $authUser, Form $form): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageForms($authUser);
    }
}
