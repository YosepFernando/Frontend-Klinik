<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureApiSessionAuth;
use App\Http\Middleware\ApiAuthMiddleware;

class ApiAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Mendaftarkan middleware untuk alias
        $router = $this->app['router'];
        
        // Mendaftarkan middleware API auth sebagai pengganti auth standar
        $router->aliasMiddleware('api.auth', EnsureApiSessionAuth::class);
        
        // Mendaftarkan middleware untuk pengecekan role
        $router->macro('apiMiddleware', function ($middleware) use ($router) {
            return $router->middleware('api.auth');
        });
    }
}
