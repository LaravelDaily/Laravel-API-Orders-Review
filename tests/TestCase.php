<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createAuthUserToken(): array
    {
        // Create a user
        $user = \App\Models\User::factory()->create();

        // Generate a Sanctum token for the user
        return [ $user->createToken('auth_token')->plainTextToken, $user, ];
    }

}
