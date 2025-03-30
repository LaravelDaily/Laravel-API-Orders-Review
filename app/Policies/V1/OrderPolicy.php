<?php

namespace App\Policies\V1;

use App\Models\Order;
use App\Models\User;
use App\Permissions\V1\Abilities;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authUser): bool
    {
        // TODO: implement isAdmin logic later
        return $authUser->id === 1;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, Order $order): bool
    {
        return $this->orderOwner($authUser, $order) || $this->viewAny($authUser);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $authUser): bool
    {
        return $this->requestOwner($authUser);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, Order $order): bool
    {
        if ($authUser->tokenCan(Abilities::Update_All_Orders)) {
            return true;
        } elseif ($authUser->tokenCan(Abilities::Update_Own_Order)) {
            return $this->orderOwner($authUser, $order) && $this->requestOwner($authUser);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, Order $order): bool
    {
        return $this->orderOwner($authUser, $order);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $authUser, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $authUser, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user is the Owner of the resources.
     */
    public function isOwner(User $authUser, User $owner): bool
    {
        return $authUser->id === $owner->id;
    }

    /**
     * Determine whether the user is the Owner of the resources.
     */
    public function orderOwner(User $authUser, Order $order): bool
    {
        return $authUser->id === $order->user_id;
    }

    public function requestOwner(User $authUser): bool
    {
        return request()->input('data.attributes.user_id') === $authUser->id;
    }
}
