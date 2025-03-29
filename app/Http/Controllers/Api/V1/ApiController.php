<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\V1\ApiResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    use ApiResponses;
    use AuthorizesRequests;

    protected $policyClass;

    public function include( string $relationships ): bool
    {
        $param = request()->query('include');

        if (is_null($param)) {
            return false;
        }

        $includes = explode(',', strtolower($param));

        return in_array(strtolower($relationships), $includes);
    }

    public function isAble( $ability, $model )
    {
        return Gate::authorize( $ability, [ $model, $this->policyClass, ] );
    }

    public function authIsOwner( $ownerId )
    {
        $owner = User::findOrFail($ownerId);
        $this->authorize('authIsOwner', $owner); //policy
    }

    public function isAbleIsOwner( $ability, $model, $ownerId )
    {
        $this->authIsOwner($ownerId);
        return $this->isAble($ability, $model);
    }


    public function notAuthorized( string $message = 'You are not authorized.' )
    {
        return $this->error( $message, Response::HTTP_UNAUTHORIZED );
    }

    public function notFound( string $message = 'Not Found.' )
    {
        return $this->error( $message, Response::HTTP_NOT_FOUND );
    }

    public function unexpectedError( string $message = 'An unexpected error occurred.' )
    {
        return $this->error( $message, Response::HTTP_INTERNAL_SERVER_ERROR );
    }

    public function dbError( string $message = 'Database error.' )
    {
        return $this->error( $message, Response::HTTP_INTERNAL_SERVER_ERROR );
    }

}
