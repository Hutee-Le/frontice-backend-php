<?php

namespace App\Providers;

use App\Services\UserService;
use App\Services\AdminService;
use App\Services\FirebaseService;
use Illuminate\Support\ServiceProvider;

class UserProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService();
        });
        $this->app->singleton(AdminService::class, function ($app) {
            return new AdminService();
        });
        $this->app->singleton(FirebaseService::class, function ($app) {
            return new FirebaseService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
