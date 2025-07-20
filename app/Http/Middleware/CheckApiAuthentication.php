<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user has valid API authentication
        if (!session('api_token') || !session('authenticated')) {
            Log::warning('CheckApiAuthentication - Missing API authentication', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'has_token' => session('api_token') ? 'yes' : 'no',
                'authenticated' => session('authenticated') ? 'yes' : 'no',
                'user_id' => session('user_id'),
                'user_role' => session('user_role'),
                'session_id' => session()->getId(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            // Clear any invalid session data
            session()->forget(['api_token', 'authenticated', 'user_id', 'user_role', 'pegawai_id']);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi autentikasi telah berakhir. Silakan login kembali.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.');
        }
        
        // Additional check: verify API token is still valid by making a quick test call
        try {
            $apiService = app(\App\Services\ApiService::class);
            $testResponse = $apiService->withToken(session('api_token'))->get('auth/profile');
            
            if (isset($testResponse['status']) && $testResponse['status'] === 'error') {
                if (isset($testResponse['message']) && 
                    (strpos($testResponse['message'], 'Unauthenticated') !== false || 
                     strpos($testResponse['message'], 'Unauthorized') !== false ||
                     strpos($testResponse['message'], 'Token') !== false)) {
                    
                    Log::warning('CheckApiAuthentication - API token validation failed', [
                        'url' => $request->fullUrl(),
                        'user_id' => session('user_id'),
                        'api_response' => $testResponse
                    ]);
                    
                    // Clear invalid session
                    session()->forget(['api_token', 'authenticated', 'user_id', 'user_role', 'pegawai_id']);
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Token autentikasi tidak valid. Silakan login kembali.',
                            'redirect' => route('login')
                        ], 401);
                    }
                    
                    return redirect()->route('login')
                        ->with('error', 'Token autentikasi tidak valid. Silakan login kembali.');
                }
            }
        } catch (\Exception $e) {
            Log::error('CheckApiAuthentication - Token validation error: ' . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'user_id' => session('user_id')
            ]);
            // Don't block request on API connection issues, just log the error
        }
        
        return $next($request);
    }
}
