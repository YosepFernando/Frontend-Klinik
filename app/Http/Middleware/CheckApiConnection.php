<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ApiService;
use Illuminate\Support\Facades\View;

class CheckApiConnection
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip untuk route yang tidak memerlukan API
        $excludedRoutes = [
            'login',
            'logout',
            'register',
            'password/*',
            'api-status'
        ];

        foreach ($excludedRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Test koneksi API
        if (!$this->apiService->testConnection()) {
            // Jika request AJAX, return JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server API tidak dapat diakses. Pastikan server API berjalan di http://localhost:8002'
                ], 503);
            }

            // Jika bukan AJAX, share error ke semua view
            View::share('api_error', 'Server API tidak dapat diakses. Pastikan server API berjalan di http://localhost:8002');
        }

        return $next($request);
    }
}
