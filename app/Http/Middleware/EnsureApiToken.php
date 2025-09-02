<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class EnsureApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip untuk route yang tidak memerlukan API token
        $excludedRoutes = [
            'login',
            'register',
            'logout',
            'password.*',
        ];
        
        $currentRoute = $request->route() ? $request->route()->getName() : '';
        
        foreach ($excludedRoutes as $excluded) {
            if (fnmatch($excluded, $currentRoute)) {
                return $next($request);
            }
        }
        
        // Jika user sudah login tapi tidak ada API token, redirect ke login
        if (Auth::check() && !Session::has('api_token')) {
            \Log::warning('User logged in but no API token found', [
                'user_id' => Auth::id(),
                'route' => $currentRoute
            ]);
            
            Auth::logout();
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }
        
        return $next($request);
    }
}
