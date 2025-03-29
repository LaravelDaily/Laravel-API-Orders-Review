<?php

namespace App\Policies\V1;

use App\Models\User;

class OwnerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, User $owner): bool
    {
        return $this->authIsOwner($authUser, $owner);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $authUser, User $owner): bool
    {
        return $this->authIsOwner($authUser, $owner);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, User $owner): bool
    {
        return $this->authIsOwner($authUser, $owner);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, User $owner): bool
    {
        return $this->authIsOwner($authUser, $owner);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $authUser, User $owner): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $authUser, User $owner): bool
    {
        return false;
    }

    /**
     * Determine whether the user is the Owner of the resources.
     */
    public function authIsOwner(User $authUser, User $owner): bool
    {
        return $authUser->id === $owner->id;
    }
}
