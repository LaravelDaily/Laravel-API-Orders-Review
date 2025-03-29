<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Permissions\V1\Abilities;
use App\Traits\V1\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->responseError([
                'email' => ['The provided credentials are incorrect.'],
                'password' => ['The provided credentials are incorrect.'],
            ], 401);
        }

        $user = User::firstWhere('email', $request->email);
        $token = $user?->createToken('authToken', Abilities::getAbilities($user), now()->addHours(8))->plainTextToken;

        return $this->responseSuccess(
            'Authenticated',
            [
                // 'user' => $user,
                'token' => $token,
            ],
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->responseSuccess('Logged out');
    }
}
