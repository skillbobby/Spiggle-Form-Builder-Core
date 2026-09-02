<?php

declare(strict_types=1);

namespace Spiggle\FormBuilder\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spiggle\FormBuilder\Models\FormSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormSubmissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FormSubmission');
    }

    public function view(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return $authUser->can('View:FormSubmission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FormSubmission');
    }

    public function update(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return $authUser->can('Update:FormSubmission');
    }

    public function delete(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return $authUser->can('Delete:FormSubmission');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FormSubmission');
    }

    public function restore(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return $authUser->can('Restore:FormSubmission');
    }

    public function forceDelete(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return $authUser->can('ForceDelete:FormSubmission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FormSubmission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FormSubmission');
    }

    public function replicate(AuthUser $authUser, FormSubmission $formSubmission): bool
    {
        return $authUser->can('Replicate:FormSubmission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FormSubmission');
    }

}