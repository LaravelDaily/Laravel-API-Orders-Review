<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\UserFilter;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\User;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserFilter $filter)
    {
        return new UserCollection(
            User::with('orders')->filter($filter)->paginate()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if ($this->include('orders')) {
            $user->load('orders');
        }

        return new UserResource($user);
    }
}
