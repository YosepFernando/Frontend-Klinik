<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class EnsureSessionIsValid
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
        // Skip for AJAX requests that check session status
        if ($request->is('check-session') || $request->is('csrf-token')) {
            return $next($request);
        }

        // Skip for login/logout routes
        $excludedRoutes = [
            'login',
            'logout',
            'register',
            'password.*',
        ];
        
        $currentRoute = $request->route() ? $request->route()->getName() : '';
        
        foreach ($excludedRoutes as $excluded) {
            if (fnmatch($excluded, $currentRoute)) {
                return $next($request);
            }
        }

        // Check if this is a form submission that requires authentication
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $hasValidAuth = Session::has('authenticated') && 
                          Session::has('api_token') && 
                          Session::get('authenticated') === true;

            if (!$hasValidAuth) {
                Log::warning('EnsureSessionIsValid - Invalid session on form submission', [
                    'route' => $currentRoute,
                    'method' => $request->method(),
                    'has_authenticated' => Session::has('authenticated'),
                    'authenticated_value' => Session::get('authenticated'),
                    'has_api_token' => Session::has('api_token'),
                    'user_id' => Session::get('user_id'),
                    'session_id' => Session::getId(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // For AJAX requests, return JSON error
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'Session expired',
                        'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                        'redirect' => route('login')
                    ], 401);
                }

                // For form submissions, redirect to login with error
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.');
            }

            // Refresh session lifetime on valid form submissions
            $request->session()->migrate(true);
            
            Log::info('EnsureSessionIsValid - Session validated and refreshed', [
                'route' => $currentRoute,
                'method' => $request->method(),
                'user_id' => Session::get('user_id'),
                'session_id' => Session::getId()
            ]);
        }

        return $next($request);
    }
}
