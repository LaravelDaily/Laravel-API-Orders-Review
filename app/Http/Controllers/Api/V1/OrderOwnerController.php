<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\OwnerFilter;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Response;

class OrderOwnerController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(OwnerFilter $filter)
    {
        return response()->json(new UserCollection(
            User::with('orders')->filter($filter)->paginate()
        ), Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $owner)
    {
        if ($this->include('orders')) {
            return response()->json(new UserResource($owner->load('orders')), Response::HTTP_OK);
        }

        return response()->json(new UserResource($owner), Response::HTTP_OK);
    }
}
