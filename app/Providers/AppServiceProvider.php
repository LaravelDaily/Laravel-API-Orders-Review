<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Policies\V1\OrderPolicy;
use App\Policies\V1\OwnerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(User::class, OwnerPolicy::class);
    }
}
