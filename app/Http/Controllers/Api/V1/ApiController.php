<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\V1\ApiResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

class ApiController extends Controller
{
    use ApiResponses;
    use AuthorizesRequests;

    protected $policyClass;

    public function include(string $relationships): bool
    {
        $param = request()->query('include');

        if (is_null($param)) {
            return false;
        }

        $includes = explode(',', strtolower($param));

        return in_array(strtolower($relationships), $includes);
    }

    public function isAble($ability, $model)
    {
        return Gate::authorize($ability, [$model, $this->policyClass]);
    }

    public function authIsOwner($ownerId)
    {
        $owner = User::findOrFail($ownerId);
        $this->authorize('authIsOwner', $owner); // policy
    }

    public function isAbleIsOwner($ability, $model, $ownerId)
    {
        $this->authIsOwner($ownerId);

        return $this->isAble($ability, $model);
    }
}
