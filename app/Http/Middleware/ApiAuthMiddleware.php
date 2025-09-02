<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ApiAuthMiddleware
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
        \Log::info('ApiAuthMiddleware: Checking authentication', [
            'url' => $request->url(),
            'has_authenticated_session' => Session::has('authenticated'),
            'authenticated_value' => Session::get('authenticated'),
            'has_api_token' => Session::has('api_token'),
            'session_id' => session()->getId()
        ]);
        
        // Check if user is authenticated via API session
        if (!Session::has('authenticated') || !Session::get('authenticated')) {
            \Log::warning('ApiAuthMiddleware: User not authenticated via API session', [
                'url' => $request->url(),
                'session_data' => session()->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            return redirect()->guest(route('login'))->with('error', 'Sesi API tidak valid. Silakan login kembali.');
        }
        
        // Check if API token exists
        if (!Session::has('api_token')) {
            \Log::warning('ApiAuthMiddleware: API token missing', [
                'url' => $request->url(),
                'session_data' => session()->all()
            ]);
            
            Session::flush();
            return redirect()->route('login')->with('error', 'Token API hilang. Silakan login kembali.');
        }
        
        \Log::info('ApiAuthMiddleware: Authentication successful', [
            'url' => $request->url(),
            'user_id' => Session::get('user_id'),
            'user_role' => Session::get('user_role')
        ]);
        
        return $next($request);
    }
}
