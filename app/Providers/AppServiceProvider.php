<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureApiSessionAuth;
use App\Http\Middleware\ApiAuthMiddleware;

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
        // Mendaftarkan middleware untuk alias
        $router = $this->app['router'];
        
        // Mendaftarkan middleware API auth sebagai pengganti auth standar
        $router->aliasMiddleware('api.auth', EnsureApiSessionAuth::class);
        
        // Mendaftarkan middleware untuk pengecekan role
        $router->aliasMiddleware('role', ApiAuthMiddleware::class);
    }
}
