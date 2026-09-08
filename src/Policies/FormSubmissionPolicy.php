<?php

declare(strict_types=1);

namespace Spiggle\FormBuilder\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\AuthorizesFormBuilder;

/**
 * Same Filament Gate path as {@see FormPolicy}: action visibility uses this
 * policy, not Resource::can*() alone. Align with form-builder.permissions.*.
 */
class FormSubmissionPolicy
{
    public function viewAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanViewSubmissions($user);
    }

    public function view(?Authenticatable $user, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanViewSubmissions($user);
    }

    public function create(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function update(?Authenticatable $user, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function delete(?Authenticatable $user, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function deleteAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function restore(?Authenticatable $user, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function forceDelete(?Authenticatable $user, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function forceDeleteAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function restoreAny(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function replicate(?Authenticatable $user, FormSubmission $formSubmission): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }

    public function reorder(?Authenticatable $user): bool
    {
        return AuthorizesFormBuilder::userCanManageSubmissions($user);
    }
}
