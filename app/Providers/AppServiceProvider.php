<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Request::macro('setMaxSize', function () {
            ini_set('post_max_size', '50M');
            ini_set('upload_max_filesize', '50M');
        });
    }
}
