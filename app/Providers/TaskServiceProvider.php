<?php

namespace App\Providers;

use App\Services\ChallengeSevice;
use App\Services\TaskService;
use App\Services\VnpayService;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(TaskService::class, function ($app) {
            return new TaskService();
        });
        $this->app->singleton(VnpayService::class, function ($app) {
            return new VnpayService();
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
