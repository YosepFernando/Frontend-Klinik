<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureApiSessionAuth
{
    /**
     * Handle an incoming request - verifies user is authenticated via API session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Periksa apakah user sudah terautentikasi via session API
        if (!Session::has('authenticated') || !Session::get('authenticated') || !Session::has('api_token')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tidak terautentikasi'], 401);
            }
            
            // Log untuk debugging
            \Log::warning('Akses dashboard ditolak - tidak terautentikasi', [
                'authenticated' => Session::has('authenticated') ? 'ada' : 'tidak ada',
                'api_token' => Session::has('api_token') ? 'ada' : 'tidak ada',
                'request_path' => $request->path(),
            ]);
            
            return redirect()->route('login')->with('error', 'Silahkan login terlebih dahulu.');
        }
        
        // Log untuk debugging
        \Log::info('Akses dashboard diterima - terautentikasi', [
            'user_id' => Session::get('user_id'),
            'role' => Session::get('user_role'),
            'path' => $request->path(),
        ]);
        
        return $next($request);
    }
}
