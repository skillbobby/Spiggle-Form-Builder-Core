<?php

declare(strict_types=1);

namespace Spiggle\FormBuilder\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;

class FormSubmissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanViewSubmissions($authUser);
    }

    public function view(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanViewSubmissions($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function update(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function delete(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function restore(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function forceDelete(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function replicate(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($authUser);
    }
}
